<?php

namespace Ueef\Postbox {

    use Throwable;
    use Ueef\Postbox\Exceptions\Exception;
    use Ueef\Postbox\Exceptions\HandlerException;
    use Ueef\Postbox\Interfaces\DriverInterface;
    use Ueef\Postbox\Interfaces\PostboxInterface;
    use Ueef\Postbox\Interfaces\EnvelopeInterface;
    use Ueef\Assignable\Traits\AssignableTrait;
    use Ueef\Assignable\Interfaces\AssignableInterface;
    use Ueef\Postbox\Interfaces\TracerInterface;

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

        /**
         * @var TracerInterface
         */
        private $tracer;

        public function wait(string $from, callable $handler)
        {
            $this->driver->wait($from, function (string $encodedRequest) use ($handler) {

                $response = new Response();
                $request = $this->envelope->parseRequest($encodedRequest);

                $this->tracer->setTraceId($request->getTraceId());
                $this->tracer->setSpanName(implode('.', $request->getRoute()));
                $this->tracer->setSpanId($request->getSpanId());
                $this->tracer->setParentSpanId($request->getParentSpanId());
                $this->tracer->log(TracerInterface::EVENT_START, $encodedRequest);

                try {
                    $data = call_user_func($handler, $request);

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

                $encodedResponse = $this->envelope->makeResponse($response);
                $this->tracer->log(TracerInterface::EVENT_COMPLETE, $encodedResponse);

                return $encodedResponse;
            });
        }
    }
}