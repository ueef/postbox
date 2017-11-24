<?php

namespace Ueef\Postbox\Tracers {

    use ArrayObject;
    use Zipkin\Kind;
    use Zipkin\Span;
    use Zipkin\Tracing;
    use Zipkin\TraceContext;
    use Zipkin\Propagation\Map;
    use Ueef\Postbox\Interfaces\TracerInterface;
    use Ueef\Postbox\Interfaces\RequestInterface;
    use Ueef\Postbox\Interfaces\ResponseInterface;

    class ZipkinTracer implements TracerInterface
    {
        /** @var Span */
        private $span;

        /** @var Span */
        private $root_span;

        /** @var integer */
        private $span_type;

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
            $this->span_type = $type;

            if (self::TYPE_HANDLING == $type) {
                $context = $request->getContext();

                if ($context) {
                    $this->context = $this->zipkin->getPropagation()->getExtractor(new Map())($context);
                }
            }

            $tracer = $this->zipkin->getTracer();

            if ($this->context) {
                if (self::TYPE_HANDLING == $type) {
                    $this->span = $tracer->joinSpan($this->context);
                } else {
                    $this->span = $tracer->newChild($this->context);
                }
            } else {
                $this->root_span = $tracer->newTrace();
                $this->root_span->setName('root-' . $this->getSpanName($request));

                $this->span = $tracer->newChild($this->root_span->getContext());
            }

            $this->span->setName($this->getSpanName($request));

            if (self::TYPE_REQUESTING == $type) {
                $this->span->setKind(Kind\CLIENT);
            }

            if (self::TYPE_SENDING == $type) {
                $this->span->setKind(Kind\PRODUCER);
            }

            if (self::TYPE_HANDLING == $type) {
                $this->span->setKind(Kind\SERVER);
            }

            $requestContext = new ArrayObject();
            $this->zipkin->getPropagation()->getInjector(new Map())($this->span->getContext(), $requestContext);
            $request->setContext($requestContext->getArrayCopy());

            if ($this->root_span) {
                $this->root_span->start();
            }
            $this->span->start();
        }

        public function spanFinish(?ResponseInterface $response = null)
        {
            $this->span->finish();
            $this->span->flush();

            if ($this->root_span) {
                $this->root_span->finish();
                $this->root_span->flush();
                $this->root_span = null;
            }

            if (self::TYPE_HANDLING == $this->span_type) {
                $this->context = null;
            }

            $this->span = null;
            $this->span_type = null;
        }

        private function getSpanName(RequestInterface $request)
        {
            return implode(':', $request->getRoute());
        }
    }
}