<?php

namespace Ueef\Postbox\Interfaces {

    use Zipkin\TraceContext;

    interface ContextContainerInterface
    {
        public function getContext(): TraceContext;
        public function setContext(TraceContext $context);
    }
}

