<?php
declare(strict_types=1);

namespace Ueef\Postbox\Drivers;

use Ueef\Postbox\Interfaces\DriverInterface;

class NullDriver implements DriverInterface
{
    public function wait(): void {}
    public function send(string $queue, string $message): void {}
    public function consume(string $queue, callable $callback): void {}
}
