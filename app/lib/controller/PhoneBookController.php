<?php

namespace app\lib\controller;


use app\lib\service\phoneBook\iPhoneEntry;
use app\lib\service\phoneBook\iSearchParams;
use app\lib\service\phoneBook\MapperTrait;
use app\lib\service\phoneBook\PhoneBookService;
use app\lib\service\phoneBook\PhoneEntryValidator;
use app\lib\service\phoneBook\ValidationException;
use Exception;
use League\Route\Http\Exception\BadRequestException;
use League\Route\Http\Exception\NotFoundException;
use malkusch\lock\exception\ExecutionOutsideLockException;
use malkusch\lock\exception\LockAcquireException;
use malkusch\lock\exception\LockReleaseException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

/**
 * Class PhoneBookController
 * @package app\lib\controller
 */
class PhoneBookController extends Controller
{
    use MapperTrait;

    private PhoneBookService $phoneBookService;

    private PhoneEntryValidator $phoneEntryValidator;


    public function __construct(
        PhoneBookService $phoneBookService,
        PhoneEntryValidator $validator
    ) {
        $this->phoneBookService = $phoneBookService;
        $this->phoneEntryValidator = $validator;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws BadRequestException
     * @throws ExecutionOutsideLockException
     * @throws LockAcquireException
     * @throws LockReleaseException
     * @throws Exception
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            $this->phoneEntryValidator->validateFields($data);
            $entry = $this->phoneBookService->addEntry($data);
        } catch (ValidationException $e) {
            throw new BadRequestException($e->getMessage());
        }

        return $this->respond($this->mapEntryToArray($entry), Controller::CODE_CREATED);
    }

    /**
     * @param ServerRequestInterface $request
     * @param $args
     * @return ResponseInterface
     * @throws BadRequestException
     * @throws NotFoundException
     * @throws ExecutionOutsideLockException
     * @throws LockAcquireException
     * @throws LockReleaseException
     * @throws Exception
     */
    public function update(ServerRequestInterface $request, $args): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            $this->phoneEntryValidator->validateFields($data);
            $isUpdated = $this->phoneBookService->updateEntry($args['id'], $data);

            if (!$isUpdated) {
                throw new NotFoundException('Entry not found');
            }

        } catch (ValidationException $e) {
            throw new BadRequestException($e->getMessage());
        }

        $entry = $this->phoneBookService->getById($args['id']);

        return $this->respond($this->mapEntryToArray($entry));
    }


    /**
     * @param ServerRequestInterface $request
     * @param array|null $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @noinspection PhpUnusedParameterInspection
     */
    public function getOne(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $entry = $this->phoneBookService->getById($args['id']);

        return $this->respond($this->mapEntryToArray($entry));
    }

    /**
     * @param ServerRequestInterface $request
     * @param array|null $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @noinspection PhpUnusedParameterInspection
     */
    public function delete(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $isRemoved = $this->phoneBookService->removeEntry($args['id']);

        if (!$isRemoved) {
            throw new NotFoundException('Entry not found');
        }

        return $this->respond([], self::CODE_NO_DATA);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function getAll(ServerRequestInterface $request): ResponseInterface
    {
        $entries = $this->phoneBookService->getBySearchParams(
            new class($request) implements iSearchParams {

                private ServerRequestInterface $request;

                public function __construct(ServerRequestInterface $request)
                {
                    $this->request = $request;
                }

                /**
                 * @inheritDoc
                 */
                public function byFirstOrLastName(): ?string
                {
                    return $this->request->getQueryParams()['byFirstOrLastName'] ?? null;
                }

                /**
                 * @inheritDoc
                 */
                public function byPhone(): ?string
                {
                    return $this->request->getQueryParams()['byPhone'] ?? null;
                }
            }
        );

        return $this->respond(
            array_map(
                fn(iPhoneEntry $e) => $this->mapEntryToArray($e),
                $entries
            )
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws ReflectionException
     * @noinspection PhpUnusedParameterInspection
     */
    public function getAllParams(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respond($this->phoneBookService->getSearchableParams());
    }
}