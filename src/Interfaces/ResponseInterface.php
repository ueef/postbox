<?php

namespace Ueef\Postbox\Interfaces {

    interface ResponseInterface
    {
        public function __toString(): string;
        public function getData(): array;
        public function getRoute(): array;
        public function getErrorCode(): int;
        public function getErrorMessage(): string;
    }
}

