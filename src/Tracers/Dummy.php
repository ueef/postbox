<?php

namespace Ueef\Postbox\Tracers {

    use Ueef\Postbox\Interfaces\TracerInterface;

    class Dummy implements TracerInterface
    {
        public function getTraceId(): string
        {
            return '';
        }

        public function setTraceId(string $traceId)
        {

        }

        public function getSpanId(): string
        {
            return '';
        }

        public function setSpanId(string $spanId)
        {

        }

        public function getSpanName(): string
        {
            return '';
        }

        public function setSpanName(string $spanName)
        {

        }

        public function getParentSpanId(): string
        {
            return '';
        }

        public function setParentSpanId(string $parentSpanId)
        {

        }

        public function spanBegin(string $spanName)
        {

        }

        public function spanEnd()
        {

        }

        public function log(int $type, string $data)
        {

        }
    }
}

