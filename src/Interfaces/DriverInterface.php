<?php
declare(strict_types=1);

namespace Ueef\Postbox\Interfaces;

interface DriverInterface
{
    public function wait(bool $nonBlocking = false, float $timeout = 0): void;
    public function send(string $queue, string $exchange, string $message): void;
    public function bind(string $queue, string $exchange);
    public function consume(string $queue, callable $callback): void;
}