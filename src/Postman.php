<?php

namespace Ueef\Postbox {

    use ArrayObject;
    use Ueef\Postbox\Exceptions\Exception;
    use Ueef\Postbox\Exceptions\HandlerException;
    use Ueef\Postbox\Interfaces\DriverInterface;
    use Ueef\Postbox\Interfaces\PostmanInterface;
    use Ueef\Postbox\Interfaces\EnvelopeInterface;
    use Ueef\Postbox\Interfaces\RequestInterface;
    use Ueef\Postbox\Interfaces\ResponseInterface;
    use Ueef\Assignable\Traits\AssignableTrait;
    use Ueef\Assignable\Interfaces\AssignableInterface;
    use const Zipkin\Kind\CLIENT;
    use const Zipkin\Kind\PRODUCER;
    use Zipkin\Propagation\Map;
    use function Zipkin\Timestamp\now;
    use Zipkin\TraceContext;
    use Zipkin\Tracing;

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
         * @var TraceContext
         */
        private $incomingContext;

        /**
         * @var Tracing
         */
        private $tracing;

        public function send(array $route, array $data)
        {
            $request = $this->makeRequest($route, $data);

            $tracer = $this->tracing->getTracer();
            $span = $tracer->newChild($this->incomingContext);
            $span->setKind(PRODUCER);
            $span->setName(implode(':', $request->getRoute()));
            $context = new ArrayObject();
            $injector = $this->tracing->getPropagation()->getInjector(new Map());
            $injector($span->getContext(), $context);

            $request->assign(['contest' => (array)$context]);
            $encodedRequest = $this->envelope->makeRequest($request);

            $span->start(now());
            $this->driver->send($request->getQueue(), $encodedRequest);
            $tracer->flush();
        }

        public function request(array $route, array $data): array
        {
            $request = $this->makeRequest($route, $data);

            $tracer = $this->tracing->getTracer();
            $span = $tracer->newChild($this->incomingContext);
            $span->setKind(CLIENT);
            $span->setName(implode(':', $request->getRoute()));
            $context = new ArrayObject();
            $injector = $this->tracing->getPropagation()->getInjector(new Map());
            $injector($span->getContext(), $context);

            $request->assign(['contest' => (array)$context]);
            $encodedRequest = $this->envelope->makeRequest($request);

            $span->start(now());
            $encodedResponse = $this->driver->request($request->getQueue(), $encodedRequest);
            $span->finish(now());
            $tracer->flush();

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
            ]);
        }
    }
}