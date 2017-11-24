<?php

namespace Ueef\Postbox {

    use ArrayObject;
    use Ueef\Postbox\Exceptions\Exception;
    use Ueef\Postbox\Exceptions\HandlerException;
    use Ueef\Postbox\Interfaces\DriverInterface;
    use Ueef\Postbox\Interfaces\PostmanInterface;
    use Ueef\Postbox\Interfaces\EnvelopeInterface;
    use Ueef\Postbox\Interfaces\RequestInterface;
    use Ueef\Assignable\Traits\AssignableTrait;
    use Ueef\Assignable\Interfaces\AssignableInterface;
    use Ueef\Postbox\Interfaces\TracerInterface;

    class Postman implements AssignableInterface, PostmanInterface
    {
        use AssignableTrait;

        /** @var TracerInterface */
        private $tracer = null;

        /** @var DriverInterface */
        private $driver;

        /** @var EnvelopeInterface */
        private $envelope;


        public function send(array $route, array $data)
        {
            $request = $this->makeRequest($route, $data);

            if ($this->tracer) {
                $this->tracer->spanStart($this->tracer::TYPE_SENDING, $request);
            }

            $this->driver->send($request->getQueue(), $this->envelope->makeRequest($request));

            if ($this->tracer) {
                $this->tracer->spanFinish();
            }
        }

        public function request(array $route, array $data): array
        {
            $request = $this->makeRequest($route, $data);

            if ($this->tracer) {
                $this->tracer->spanStart($this->tracer::TYPE_REQUESTING, $request);
            }

            $response = $this->envelope->parseResponse(
                $this->driver->request($request->getQueue(), $this->envelope->makeRequest($request))
            );

            if ($this->tracer) {
                $this->tracer->spanFinish($response);
            }

            if (Exception::NONE !== $response->getErrorCode()) {
                throw new HandlerException($response->getErrorMessage(), $response->getErrorCode());
            }

            return $response->getData();
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