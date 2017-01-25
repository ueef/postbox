<?php

namespace Ueef\Postbox\Interfaces {

    interface PostboxInterface
    {
        public function wait(string $from, callable $handler);
    }
}