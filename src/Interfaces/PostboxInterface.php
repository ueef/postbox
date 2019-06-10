<?php
declare(strict_types=1);

namespace Ueef\Postbox\Interfaces;

interface PostboxInterface
{
    public function wait();
    public function send(string $queue, string $exchange, array $message): void;
    public function bind(string $queue, string $exchange);
    public function consume(string $queue, callable $handler);
}