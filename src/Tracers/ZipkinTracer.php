<?php
declare(strict_types=1);

namespace Ueef\Postbox\Tracers {

    use Ueef\Postbox\Exceptions\Exception;
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
        /** @var array */
        private $tags;

        /** @var Span[] */
        private $spans;

        /** @var Tracing */
        private $zipkin;

        /** @var TraceContext */
        private $context;


        public function __construct(Tracing $zipkin, array $tags = [])
        {
            $this->zipkin = $zipkin;
            $this->tags = $tags;
        }

        public function spanStart(int $type, RequestInterface $request)
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
                $span = $tracer->newTrace();
            }

            $span->setName($this->getSpanName($request));
            foreach ($this->tags as $k => $v) {
                $span->tag($k, $v);
            }

            if (self::TYPE_REQUESTING == $type) {
                $span->setKind(Kind\CLIENT);
            }

            if (self::TYPE_SENDING == $type) {
                $span->setKind(Kind\PRODUCER);
            }

            if (self::TYPE_HANDLING == $type) {
                $span->tag('request', json_encode($request->getData(), JSON_UNESCAPED_UNICODE));
                $span->setKind(Kind\SERVER);
            }

            $requestContext = [];
            $this->zipkin->getPropagation()->getInjector(new Map())($span->getContext(), $requestContext);
            $request->setContext($requestContext);

            $span->start();
            $this->spans[] = $span;
        }

        public function spanFinish(int $type, ?ResponseInterface $response = null)
        {
            $span = array_pop($this->spans);

            if ($type == self::TYPE_HANDLING) {
                if ($response->getErrorCode() == Exception::NONE) {
                    $span->tag('response', json_encode($response->getData(), JSON_UNESCAPED_UNICODE));
                } else {
                    $span->tag('error_code', ($response->getErrorCode()));
                    $span->tag('error_message', ($response->getErrorMessage()));
                }
            }

            $span->finish();
            $span->flush();
        }

        private function getSpanName(RequestInterface $request)
        {
            return implode(':', $request->getRoute());
        }
    }
}