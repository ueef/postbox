<?php

namespace Ueef\Postbox {

    use Ueef\Postbox\Interfaces\ContextContainerInterface;
    use Zipkin\TraceContext;

    class ContextContainer implements ContextContainerInterface
    {
        /**
         * @var TraceContext
         */
        private $context;

        public function getContext(): TraceContext
        {
            return $this->context;
        }

        /**
         * @param TraceContext $context
         */
        public function setContext(TraceContext $context)
        {
            $this->context = $context;
        }
    }
}

