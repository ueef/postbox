<?php

namespace Ueef\Postbox\Interfaces {

    interface EncoderInterface
    {
        public function encode(array $message): string;
        public function decode(string $message): array;
    }
}

