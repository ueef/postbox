<?php

namespace Ueef\Postbox {

    use Ueef\Assignable\Interfaces\AssignableInterface;
    use Ueef\Assignable\Traits\AssignableTrait;
    use Ueef\Postbox\Interfaces\RequestInterface;

    class Request implements AssignableInterface, RequestInterface
    {
        use AssignableTrait;

        /**
         * @var array
         */
        private $data;

        /**
         * @var array
         */
        private $route;

        /**
         * @var string
         */
        private $queue;

        /**
         * @var string
         */
        private $traceId;

        /**
         * @var string
         */
        private $spanId;

        /**
         * @var string
         */
        private $parentSpanId;

        public function getData(): array
        {
            return $this->data;
        }

        public function getRoute(): array
        {
            return $this->route;
        }

        public function getQueue(): string
        {
            return $this->queue;
        }

        public function getTraceId(): string
        {
            return $this->traceId;
        }

        public function getSpanId(): string
        {
            return $this->spanId;
        }

        public function getParentSpanId(): string
        {
            return $this->parentSpanId;
        }
    }
}

