<?php

namespace app\lib\controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Class HelloController
 * @package app\lib\controller
 */
class HelloController extends Controller
{
    private LoggerInterface $logger;

    /**
     * HelloController constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function getHello(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->info('hello from logs');
        $data = ['hello from ctrl'];

        return $this->respond($data);
    }
}