<?php

namespace Ueef\Postbox {

    use Ueef\Assignable\Traits\AssignableTrait;
    use Ueef\Postbox\Interfaces\RequestInterface;

    class Request implements RequestInterface
    {
        use AssignableTrait;

        /** @var array */
        private $data;

        /** @var array */
        private $route;

        /** @var string */
        private $queue;

        /** @var array */
        private $context;


        public function __toString(): string
        {
            return implode('.', $this->getRoute()) . PHP_EOL . json_encode($this->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        }

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

        public function setContext(array $context)
        {
            $this->context = $context;
        }

        public function getContext(): ?array
        {
            return $this->context;
        }
    }
}

