<?php

namespace Ueef\Postbox\Interfaces {

    interface TracerInterface
    {
        public function startTracing();
        public function stopTracing();

        public function setId(int $id);
        public function setTraceName(string $name);
        public function setTraceMessage(string $message);

        public function getTraceIdOrGenerate(): string;
        public function setTraceId(string $traceId);

        public function saveTrace();
    }
}

