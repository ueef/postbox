<?php
declare(strict_types=1);

namespace Ueef\Postbox\Interfaces {

    interface DriverInterface
    {
        public function wait(bool $nonBlocking = false): void;
        public function listen(string $queue, callable $callback): void;
        public function send(string $queue, string $message): void;
        public function request(string $queue, string $message): string;
    }
}

