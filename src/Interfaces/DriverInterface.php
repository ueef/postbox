<?php
declare(strict_types=1);

namespace Ueef\Postbox\Interfaces {

    interface DriverInterface
    {
        public function wait(bool $nonBlocking = false): void;
        public function listen(string $queue, callable $callback): void;
        public function send(string $queue, string $message, int $delayedTo = 0): void;
        public function request(string $queue, string $message): string;
    }
}

