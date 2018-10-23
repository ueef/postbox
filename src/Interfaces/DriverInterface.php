<?php

namespace Ueef\Postbox\Interfaces {

    interface DriverInterface
    {
        public function wait();
        public function send(string $to, string $message);
        public function consume(string $from, callable $callback);
        public function request(string $to, string $message): string;
    }
}

