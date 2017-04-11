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
            $this->driver->wait($from, function (string $request) use ($handler) {

                $response = new Response();
                $request = $this->envelope->parseRequest($request);
                $this->tracer->setId(null);
                $this->tracer->setTraceId($request->getTraceId());
                $this->tracer->setTraceName(implode('.', $request->getRoute()));
                $this->tracer->startTracing();

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

                    $this->tracer->setTraceMessage("code: $errorCode message: $errorMessage");
                }

                $this->tracer->stopTracing();
                $this->tracer->saveTrace();
                return $this->envelope->makeResponse($response);
            });
        }
    }
}