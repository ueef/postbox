<?php

namespace Ueef\Postbox\Tracers {

    use Zipkin\Kind;
    use Zipkin\Span;
    use Zipkin\Tracing;
    use Zipkin\TraceContext;
    use Zipkin\Propagation\Map;
    use Ueef\Assignable\Traits\AssignableTrait;
    use Ueef\Postbox\Interfaces\TracerInterface;
    use Ueef\Postbox\Interfaces\RequestInterface;
    use Ueef\Postbox\Interfaces\ResponseInterface;
    use Ueef\Assignable\Interfaces\AssignableInterface;

    class ZipkinTracer implements TracerInterface, AssignableInterface
    {
        use AssignableTrait;

        /** @var Span */
        private $span;

        /** @var integer */
        private $span_type;

        /** @var Tracing */
        private $zipkin;

        /** @var TraceContext */
        private $context;


        public function spanStart(int $type, RequestInterface &$request, ?array $context = null)
        {
            $this->span_type = $type;

            if ($context) {
                $this->context = $this->zipkin->getPropagation()->getExtractor(new Map())($context);
            }

            $tracer = $this->zipkin->getTracer();

            if ($this->context) {
                $this->span = $tracer->newChild($this->context);
            } else {
                $this->span = $tracer->newTrace();
            }

            $this->span->setName($this->getSpanName($request));

            if (self::TYPE_SENDING == $type || self::TYPE_REQUESTING == $type) {
                $this->span->setKind(Kind\CLIENT);
            }

            if (self::TYPE_HANDLING == $type) {
                $this->span->setKind(Kind\SERVER);
            }

            $requestContext = [];
            $this->zipkin->getPropagation()->getInjector(new Map())($this->span->getContext(), $requestContext);
            $request->setContext($requestContext);

            $this->span->start();
        }

        public function spanFinish(?ResponseInterface $response = null)
        {
            $this->span->finish();
            $this->span->flush();

            $this->span = null;
            $this->span_type = null;
        }

        private function getSpanName(RequestInterface $request)
        {
            return implode(':', $request->getRoute());
        }
    }
}