<?php
declare(strict_types=1);

namespace Ueef\Postbox\Interfaces;

interface PostboxInterface
{
    public function wait(bool $nonBlocking = false): void;
    public function listen(string $queue, HandlerInterface $handler): void;
    public function send(array $route, array $parameters): void;
    public function request(array $route, array $parameters): array;
}