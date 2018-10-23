<?php

namespace Ueef\Postbox\Interfaces {

    interface PostboxInterface
    {
        public function wait();
        public function consume(string $from, callable $handler);
    }
}