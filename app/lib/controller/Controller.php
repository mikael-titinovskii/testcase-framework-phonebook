<?php

namespace app\lib\controller;


use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;

abstract class Controller
{
    public const CODE_OK = 200;
    public const CODE_CREATED = 201;
    public const CODE_NO_DATA = 204;

    /**
     * @param array $data
     * @param int $code
     * @return ResponseInterface
     */
    protected function respond(array $data, int $code = self::CODE_OK): ResponseInterface
    {
        $response = new Response;
        $response->getBody()->write(\GuzzleHttp\json_encode($data));

        return $response
            ->withAddedHeader('content-type', 'application/json')
            ->withStatus($code);
    }
}