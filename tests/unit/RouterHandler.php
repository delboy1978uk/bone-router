<?php declare(strict_types=1);

namespace BoneTest;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response();
    }

    public function json(ServerRequestInterface $request): ResponseInterface
    {
        return new Response\JsonResponse(['pong' => '2020-04-06']);
    }

    public function explode(ServerRequestInterface $request): ResponseInterface
    {
        throw new \Exception();
    }

    public function deny(ServerRequestInterface $request): ResponseInterface
    {
        throw new \Exception('now way Jose', 403);
    }
}