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
        private $address;

        /**
         * @var string
         */
        private $service;


        public function getData(): array
        {
            return $this->data;
        }

        public function getAddress(): array
        {
            return $this->address;
        }

        public function getService(): string
        {
            return $this->service;
        }
    }
}

