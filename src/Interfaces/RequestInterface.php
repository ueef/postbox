<?php

namespace Ueef\Postbox\Interfaces {

    use Ueef\Assignable\Interfaces\AssignableInterface;

    interface RequestInterface extends AssignableInterface
    {
        public function __toString(): string;
        public function getData(): array;
        public function getRoute(): array;
        public function getQueue(): string;
        public function getContext(): array;
    }
}

