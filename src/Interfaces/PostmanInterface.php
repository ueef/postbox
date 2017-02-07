<?php

namespace Ueef\Postbox\Interfaces {

    interface PostmanInterface
    {
        public function send(array $route, array $parameters);
        public function request(array $route, array $parameters): array;
    }
}