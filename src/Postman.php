<?php

namespace Ueef\Postbox {

    use Ueef\Postbox\Interfaces\DriverInterface;
    use Ueef\Postbox\Interfaces\PostmanInterface;
    use Ueef\Postbox\Interfaces\EnvelopeInterface;
    use Ueef\Postbox\Interfaces\ResponseInterface;
    use Ueef\Assignable\Traits\AssignableTrait;
    use Ueef\Assignable\Interfaces\AssignableInterface;

    class Postman implements AssignableInterface, PostmanInterface
    {
        use AssignableTrait;

        /**
         * @var DriverInterface
         */
        private $driver;

        /**
         * @var EnvelopeInterface
         */
        private $envelope;


        public function send(array $route, array $data)
        {
            $request = new Request([
                'route' => $route,
                'data' => $data,
            ]);

            $this->driver->send($request->getService(), $this->envelope->makeRequest($request));
        }

        public function request(array $route, array $data): ResponseInterface
        {
            $request = new Request([
                'route' => $route,
                'data' => $data,
            ]);

            return $this->envelope->parseResponse($this->driver->request($request->getService(), $this->envelope->makeRequest($request)));
        }
    }
}