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


        public function wait(string $from, callable $handler)
        {
            $this->driver->wait($from, function (string $request) use ($handler) {
                $response = new Response();

                try {
                    $request = $this->envelope->parseRequest($request);
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
                    if (HandlerException::NONE == $errorCode) {
                        $errorCode = HandlerException::UNKNOWN;
                    }

                    $response->assign([
                        'error_code' => $errorCode,
                        'error_message' => $e->getMessage(),
                    ]);
                }

                return $this->envelope->makeResponse($response);
            });
        }
    }
}