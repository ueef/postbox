<?php

namespace Ueef\Postbox\Interfaces {

    interface PostmanInterface
    {
        public function send(array $address, array $parameters);
        public function request(array $address, array $parameters): ResponseInterface;
    }
}