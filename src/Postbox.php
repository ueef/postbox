<?php

namespace Ueef\Postbox {

    use ArrayObject;
    use Throwable;
    use Ueef\Postbox\Exceptions\Exception;
    use Ueef\Postbox\Exceptions\HandlerException;
    use Ueef\Postbox\Interfaces\DriverInterface;
    use Ueef\Postbox\Interfaces\PostboxInterface;
    use Ueef\Postbox\Interfaces\EnvelopeInterface;
    use Ueef\Assignable\Traits\AssignableTrait;
    use Ueef\Assignable\Interfaces\AssignableInterface;
    use const Zipkin\Kind\SERVER;
    use Zipkin\Propagation\Map;
    use function Zipkin\Timestamp\now;
    use Zipkin\TraceContext;
    use Zipkin\Tracing;

    class Postbox implements AssignableInterface, PostboxInterface
    {
        use AssignableTrait;

        /**
         * @var bool
         */
        private $verbose = false;

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


        public function wait(string $from, callable $handler)
        {
            $this->driver->wait($from, function (string $encodedRequest) use ($handler) {

                $response = new Response();
                $request = $this->envelope->parseRequest($encodedRequest);

                if ($this->verbose) {
                    echo $request . PHP_EOL . PHP_EOL;
                }

                $extractor = $this->tracing->getPropagation()->getExtractor(new Map());
                $this->incomingContext = $extractor(new ArrayObject($request->getContext()));
                $tracer = $this->tracing->getTracer();
                $span = $tracer->joinSpan($this->incomingContext);
                $span->setKind(SERVER);
                $span->setName(implode(':', $request->getRoute()));

                try {
                    $span->start(now());
                    $data = call_user_func($handler, $request);
                    $span->finish(now());
                    $tracer->flush();

                    if (null === $data) {
                        $data = [];
                    }

                    if (!is_array($data)) {
                        throw new HandlerException('Handler returns not an array');
                    }

                    $response->assign([
                        'route' => $request->getRoute(),
                        'data' => $data,
                    ]);
                } catch (Throwable $e) {
                    $errorCode = $e->getCode();
                    $errorMessage = $e->getMessage();
                    if (HandlerException::NONE == $errorCode) {
                        $errorCode = HandlerException::UNKNOWN;
                    }

                    $response->assign([
                        'error_code' => $errorCode,
                        'error_message' => $errorMessage,
                    ]);
                }

                if ($this->verbose) {
                    echo $response . PHP_EOL . PHP_EOL;
                }

                $encodedResponse = $this->envelope->makeResponse($response);
                return $encodedResponse;
            });
        }
    }
}