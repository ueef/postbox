<?php

namespace Ueef\Postbox\Interfaces {

    use Ueef\Assignable\Interfaces\AssignableInterface;

    interface ResponseInterface extends AssignableInterface
    {
        public function __toString(): string;
        public function getData(): array;
        public function getRoute(): array;
        public function getErrorCode(): int;
        public function getErrorMessage(): string;
    }
}

