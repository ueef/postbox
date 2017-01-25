<?php

namespace Ueef\Postbox\Interfaces {

    interface EnvelopeInterface
    {
        public function makeRequest(RequestInterface $request): string;
        public function parseRequest(string $message): RequestInterface;

        public function makeResponse(ResponseInterface $response): string;
        public function parseResponse(string $message): ResponseInterface;
    }
}

