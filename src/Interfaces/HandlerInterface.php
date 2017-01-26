<?php

namespace Ueef\Postbox\Interfaces {

    interface HandlerInterface
    {
        public function __invoke(RequestInterface $request): array;
    }
}

