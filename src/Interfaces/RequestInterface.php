<?php

namespace Ueef\Postbox\Interfaces {

    interface RequestInterface
    {
        public function getData(): array;
        public function getAddress(): array;
        public function getService(): string;
    }
}

