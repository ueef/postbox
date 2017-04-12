<?php

namespace Ueef\Postbox\Interfaces {

    interface RequestInterface
    {
        public function getData(): array;
        public function getRoute(): array;
        public function getQueue(): string;
        public function getTraceId(): string;
        public function getSpanId(): string;
        public function getParentSpanId(): string;
    }
}

