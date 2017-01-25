<?php

namespace Ueef\Postbox\Interfaces {

    interface DriverInterface
    {
        public function wait(string $from, callable $callback);
        public function send(string $to, string $message);
        public function request(string $to, string $message): string;
    }
}

