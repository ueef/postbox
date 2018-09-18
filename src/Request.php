<?php
declare(strict_types=1);

namespace Ueef\Postbox {

    use Ueef\Assignable\Traits\AssignableTrait;
    use Ueef\Postbox\Interfaces\RequestInterface;

    class Request implements RequestInterface
    {
        /** @var array */
        private $data = [];

        /** @var array */
        private $route = [];

        /** @var array */
        private $context = [];


        public function __construct(array $data = [], array $route = [])
        {
            $this->data = $data;
            $this->route = array_values($route);
        }

        public function pack(): array
        {
            return [
                'data' => $this->data,
                'route' => $this->route,
                'context' => $this->context,
            ];
        }

        public function assign(array $parameters): void
        {
            foreach ($parameters as $key => $value) {
                switch ($key) {
                    case 'data':
                    case 'context':
                        if (is_array($value)) {
                            $this->{$key} = $value;
                        }
                        break;
                    case 'route':
                        $this->{$key} = array_values($value);
                        break;
                    case 'queue':
                        $this->{$key} = (string) $value;
                        break;
                }
            }
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
            return $this->route[0];
        }

        public function setContext(array $context): void
        {
            $this->context = $context;
        }

        public function getContext(): array
        {
            return $this->context;
        }
    }
}

