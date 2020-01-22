<?php

use malkusch\lock\mutex\FlockMutex;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nette\Caching\Storages\FileStorage;
use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\Conventions\DiscoveredConventions;
use Nette\Database\Structure;
use Psr\Container\ContainerInterface;

// container config
return [
    'app.is_production' => getenv('IS_PRODUCTION'),
    'app.mutex_timeout' => 10, //s

    'db.host' => getenv('MYSQL_HOST'),
    'db.name' => getenv('MYSQL_DATABASE'),
    'db.port' => getenv('MYSQL_PORT'),
    'db.user' => getenv('MYSQL_USER'),
    'db.password' => getenv('MYSQL_PASSWORD'),

    FlockMutex::class => Di\factory(
        static function (ContainerInterface $c) {
            return new FlockMutex(
                fopen(RUNTIME_DIR.'/lock', 'ab+'), // rb?
                $c->get('app.mutex_timeout')
            );
        }
    ),
    Connection::class => Di\factory(
        static function (ContainerInterface $c) {
            return new Nette\Database\Connection(
                sprintf('mysql:host=%s;dbname=%s;port=%s', $c->get('db.host'), $c->get('db.name'), $c->get('db.port')),
                $c->get('db.user'),
                $c->get('db.password')
            );
        }
    ),

    Context::class => Di\factory(
        static function (ContainerInterface $c) {
            $storage = new FileStorage(RUNTIME_DIR.'/orm');
            $structure = new Structure($c->get(Connection::class), $storage);
            $conventions = new DiscoveredConventions($structure);

            return new Context($c->get(Connection::class), $structure, $conventions, $storage);
        }
    ),

    Psr\Log\LoggerInterface::class => DI\factory(
        static function (ContainerInterface $c) {
            $logger = new Logger('app');
            $output = "[%datetime%] %channel%.%level_name%: %message%\n";
            $formatter = new LineFormatter($output);

            $handler = new StreamHandler(RUNTIME_DIR.'/applog.log', Logger::DEBUG);
            $handler->setFormatter($formatter);

            if ($c->get('app.is_production')) {
                $handler = new StreamHandler('php://stdout', Logger::DEBUG);
                $handler->setFormatter($formatter);
            }

            $logger->pushHandler($handler);

            return $logger;
        }
    ),
];