<?php

namespace Ueef\Postbox\Tracers {

    use ArrayObject;
    use Zipkin\Kind;
    use Zipkin\Span;
    use Zipkin\Tracing;
    use Zipkin\Propagation\TraceContext;
    use Zipkin\Propagation\Map;
    use Ueef\Postbox\Interfaces\TracerInterface;
    use Ueef\Postbox\Interfaces\RequestInterface;
    use Ueef\Postbox\Interfaces\ResponseInterface;

    class ZipkinTracer implements TracerInterface
    {
        /** @var Span[] */
        private $spans;

        /** @var Span */
        private $root_span;

        /** @var Tracing */
        private $zipkin;

        /** @var TraceContext */
        private $context;

        public function __construct(Tracing $zipkin)
        {
            $this->zipkin = $zipkin;
        }

        public function spanStart(int $type, RequestInterface &$request)
        {
            if (self::TYPE_HANDLING == $type) {
                $context = $request->getContext();

                if ($context) {
                    $this->context = $this->zipkin->getPropagation()->getExtractor(new Map())($context);
                }
            }

            $tracer = $this->zipkin->getTracer();

            if ($this->context) {
                if (self::TYPE_HANDLING == $type) {
                    $span = $tracer->joinSpan($this->context);
                } else {
                    $span = $tracer->newChild($this->context);
                }
            } else {
                $this->root_span = $tracer->newTrace();
                $this->root_span->setName('root-' . $this->getSpanName($request));
                $this->root_span->start();

                $span = $tracer->newChild($this->root_span->getContext());
            }

            $span->setName($this->getSpanName($request));

            if (self::TYPE_REQUESTING == $type) {
                $span->setKind(Kind\CLIENT);
            }

            if (self::TYPE_SENDING == $type) {
                $span->setKind(Kind\PRODUCER);
            }

            if (self::TYPE_HANDLING == $type) {
                $span->setKind(Kind\SERVER);
            }

            $requestContext = new ArrayObject();
            $this->zipkin->getPropagation()->getInjector(new Map())($span->getContext(), $requestContext);
            $request->setContext($requestContext->getArrayCopy());

            $span->start();
            $this->spans[] = $span;
        }

        public function spanFinish(?ResponseInterface $response = null)
        {
            $span = array_pop($this->spans);
            $span->finish();
            $span->flush();

            if ($this->root_span) {
                $this->root_span->finish();
                $this->root_span->flush();
            }
        }

        private function getSpanName(RequestInterface $request)
        {
            return implode(':', $request->getRoute());
        }
    }
}