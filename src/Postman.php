<?php

namespace Ueef\Postbox {

    use Ueef\Postbox\Interfaces\DriverInterface;
    use Ueef\Postbox\Interfaces\PostmanInterface;
    use Ueef\Postbox\Interfaces\EnvelopeInterface;
    use Ueef\Postbox\Interfaces\RequestInterface;
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
            $request = $this->makeRequest($route, $data);

            $this->driver->send($request->getQueue(), $this->envelope->makeRequest($request));
        }

        public function request(array $route, array $data): ResponseInterface
        {
            $request = $this->makeRequest($route, $data);

            return $this->envelope->parseResponse($this->driver->request($request->getQueue(), $this->envelope->makeRequest($request)));
        }

        private function makeRequest(array $route, array $data): RequestInterface
        {
            return new Request([
                'route' => $route,
                'queue' => reset($route),
                'data' => $data,
            ]);
        }
    }
}