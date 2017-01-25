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


        public function send(array $address, array $data)
        {
            $request = new Request([
                'address' => $address,
                'data' => $data,
            ]);

            $this->driver->send($request->getService(), $this->envelope->makeRequest($request));
        }

        public function request(array $address, array $data): ResponseInterface
        {
            $request = new Request([
                'address' => $address,
                'data' => $data,
            ]);

            return $this->envelope->parseResponse($this->driver->request($request->getService(), $this->envelope->makeRequest($request)));
        }
    }
}