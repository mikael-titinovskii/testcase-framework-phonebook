<?php

namespace app;


use Cake\Cache\Cache;
use DateTime;
use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Dotenv\Dotenv;
use Exception;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Http\Exception as HttpException;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Psr\Log\LoggerInterface;
use Throwable;

class App
{
    private const FATAL_ERROR_PAYLOAD = [
        'status_code' => 500,
        'reason_phrase' => 'Internal error, try again later or contact support',
    ];
    /**
     * @var Container
     */
    private Container $container;


    public function run(): void
    {
        try {
            $this->initEnv();
            $this->initContainer();
            $this->initCache();
            $this->initRouter();
        } catch (Throwable $t) {
            // if we are here, that means either
            // - configuration failed, container is inaccessible
            // - the router-level try/catch failed to take action on an error
            // in these scenarios - it is considered an emergency

            // bubble error in dev env
            if (!getenv('IS_PRODUCTION')) {
                /** @noinspection PhpUnhandledExceptionInspection */
                throw $t;
            }

            // try to write something after message is shown
            register_shutdown_function(
                static function () use ($t) {
                    fwrite(
                        fopen('php://stderr', 'wb'),
                        sprintf(
                            "[%s] %s.%s: %s %s\n", // todo cleanup
                            (new DateTime())->format('Y-m-d H:i:s'),
                            'app',
                            'EMERGENCY',
                            $t->getMessage(),
                            $t->getTraceAsString()
                        )
                    );
                }
            );

            // stop app
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json');
            echo \GuzzleHttp\json_encode(self::FATAL_ERROR_PAYLOAD);
            // fpm process needs to die this way
            fastcgi_finish_request();
            // plan b
            exit(1);
        }
    }

    private function initCache(): void
    {
        // todo find a better caching lib
        Cache::setConfig(
            'default',
            [
                'className' => 'File',
                'duration' => '+7 hours', // todo move to container cfg
                'path' => RUNTIME_DIR.'/cache',
                'prefix' => 'cake_short_',
            ]
        );
    }

    private function initEnv(): void
    {
        $dotenv = Dotenv::createImmutable(PROJECT_DIR);
        $dotenv->load();
        $dotenv->required('IS_PRODUCTION')->isBoolean();
    }

    /**
     * @throws Exception
     */
    private function initContainer(): void
    {
        $builder = new ContainerBuilder;
        $builder->useAnnotations(false);
        $builder->addDefinitions(APP_DIR.'/config.php');
        $builder->enableCompilation(RUNTIME_DIR.'/container/');
        $builder->writeProxiesToFile(true, RUNTIME_DIR.'/container/');
        $container = $builder->build();
        $this->container = $container;
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @noinspection PhpRedundantCatchClauseInspection
     */
    private function initRouter(): void
    {
        $request = ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );

        $strategy = new ApplicationStrategy;
        $strategy->setContainer($this->container);
        $router = (new Router);
        $router->setStrategy($strategy);

        // todo composer remove league/route
        $router->middleware(new BodyParamsMiddleware());

        try {
            /** @var RouteResolver $resolver */
            $resolver = $this->container->make(RouteResolver::class, compact('router'));
            $resolver->resolve();
            $response = $router->dispatch($request);

        // todo these t/c scenarios can prob. be extracted to a middleware on it's own
        } catch (HttpException $e) {
            // handle http exceptions thrown by controllers/services
            // these are safe, and part of the flow
            $response = $e->buildJsonResponse(new Response);
        } catch (Throwable $t) {
            /** @var LoggerInterface $l */
            $l = $this->container->get(LoggerInterface::class);
            $l->critical($t->getMessage(), $t->getTrace());
            $response = (new Response)->withStatus(500);
            $response = $response->withAddedHeader('content-type', 'application/json');
            $response->getBody()->write(\GuzzleHttp\json_encode(self::FATAL_ERROR_PAYLOAD));
        }

        (new SapiEmitter())->emit($response);
    }
}