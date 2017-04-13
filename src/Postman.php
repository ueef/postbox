<?php

namespace Ueef\Postbox {

    use Ueef\Postbox\Exceptions\Exception;
    use Ueef\Postbox\Exceptions\HandlerException;
    use Ueef\Postbox\Interfaces\DriverInterface;
    use Ueef\Postbox\Interfaces\PostmanInterface;
    use Ueef\Postbox\Interfaces\EnvelopeInterface;
    use Ueef\Postbox\Interfaces\RequestInterface;
    use Ueef\Postbox\Interfaces\ResponseInterface;
    use Ueef\Assignable\Traits\AssignableTrait;
    use Ueef\Assignable\Interfaces\AssignableInterface;
    use Ueef\Postbox\Interfaces\TracerInterface;

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

        /**
         * @var TracerInterface
         */
        private $tracer;

        public function send(array $route, array $data)
        {
            $this->tracer->spanBegin(implode('.', $route));
            $request = $this->makeRequest($route, $data);
            $encodedRequest = $this->envelope->makeRequest($request);

            $this->tracer->log(TracerInterface::EVENT_SEND, $encodedRequest);
            $this->driver->send($request->getQueue(), $encodedRequest);
            $this->tracer->spanEnd();
        }

        public function request(array $route, array $data): array
        {
            $this->tracer->spanBegin(implode('.', $route));
            $request = $this->makeRequest($route, $data);
            $encodedRequest = $this->envelope->makeRequest($request);

            $this->tracer->log(TracerInterface::EVENT_SEND, $encodedRequest);
            $encodedResponse = $this->driver->request($request->getQueue(), $encodedRequest);
            $this->tracer->log(TracerInterface::EVENT_RECEIVE, $encodedResponse);
            $this->tracer->spanEnd();

            $response = $this->envelope->parseResponse($encodedResponse);

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
                'traceId' => $this->tracer->getTraceId(),
                'spanId' => $this->tracer->getSpanId(),
                'spanName' => $this->tracer->getSpanName(),
                'parentSpanId' => $this->tracer->getParentSpanId()
            ]);
        }
    }
}