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
        private $service;


        public function getData(): array
        {
            return $this->data;
        }

        public function getRoute(): array
        {
            return $this->route;
        }

        public function getService(): string
        {
            return $this->service;
        }
    }
}

