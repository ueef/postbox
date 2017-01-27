<?php

namespace Ueef\Postbox {

    use Ueef\Postbox\Exceptions\Exception;
    use Ueef\Postbox\Interfaces\ResponseInterface;
    use Ueef\Assignable\Traits\AssignableTrait;
    use Ueef\Assignable\Interfaces\AssignableInterface;

    class Response implements AssignableInterface, ResponseInterface
    {
        use AssignableTrait;

        /**
         * @var array
         */
        private $data = [];

        /**
         * @var array
         */
        private $route = [];

        /**
         * @var integer
         */
        private $error_code = Exception::NONE;

        /**
         * @var string
         */
        private $error_message = '';


        public function getData(): array
        {
            return $this->data;
        }

        public function getRoute(): array
        {
            return $this->route;
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