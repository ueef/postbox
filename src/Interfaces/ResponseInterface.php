<?php
declare(strict_types=1);

namespace Ueef\Postbox\Interfaces {

    use Ueef\Packable\Interfaces\PackableInterface;

    interface ResponseInterface extends PackableInterface
    {
        public function getData(): array;
        public function getRequest(): RequestInterface;
        public function getErrorCode(): string;
        public function getErrorMessage(): string;
    }
}

