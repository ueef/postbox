<?php

namespace Ueef\Postbox {

    use Ueef\Postbox\Interfaces\DriverInterface;
    use Ueef\Postbox\Interfaces\PostboxInterface;
    use Ueef\Postbox\Interfaces\EnvelopeInterface;
    use Ueef\Postbox\Interfaces\ResponseInterface;
    use Ueef\Assignable\Traits\AssignableTrait;
    use Ueef\Assignable\Interfaces\AssignableInterface;

    class Postbox implements AssignableInterface, PostboxInterface
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


        public function wait(string $from, callable $handler)
        {
            $this->driver->wait($from, function (string $response) use ($handler) {
                call_user_func($handler, $this->envelope->parseResponse($response));
            });
        }

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