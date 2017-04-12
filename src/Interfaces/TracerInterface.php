<?php

namespace Ueef\Postbox\Interfaces {

    interface TracerInterface
    {
        const EVENT_SEND = 1;
        const EVENT_RECEIVE = 2;
        const EVENT_START = 3;
        const EVENT_COMPLETE = 4;

        public function createNewSpanId(): string;

        public function getTraceId(): string;
        public function setTraceId(string $traceId);

        public function setSpanId(string $spanId);
        public function getSpanId(): string;

        public function setParentSpanId(string $spanId);

        public function setSpanName(string $spanName);

        public function log(int $type, string $data);
    }
}

