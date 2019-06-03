<?php
declare(strict_types=1);

namespace Ueef\Postbox\Interfaces;

interface DriverInterface
{
    public function wait(): void;
    public function send(string $queue, string $message): void;
    public function consume(string $queue, callable $callback): void;
}