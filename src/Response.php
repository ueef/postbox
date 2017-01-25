<?php

namespace Ueef\Postbox {

    use Ueef\Assignable\Interfaces\AssignableInterface;
    use Ueef\Assignable\Traits\AssignableTrait;
    use Ueef\Postbox\Interfaces\ResponseInterface;

    class Response implements AssignableInterface, ResponseInterface
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
         * @var integer
         */
        private $error_code;

        /**
         * @var string
         */
        private $error_message;


        public function getData(): array
        {
            return $this->data;
        }

        public function getAddress(): array
        {
            return $this->address;
        }

        public function getErrorCode(): int
        {
            return $this->error_code;
        }

        public function getErrorMessage(): string
        {
            return $this->error_message;
        }
    }
}