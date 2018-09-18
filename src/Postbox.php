<?php
declare(strict_types=1);

namespace Ueef\Postbox {

    use Throwable;
    use Ueef\Postbox\Interfaces\TracerInterface;
    use Ueef\Postbox\Interfaces\DriverInterface;
    use Ueef\Postbox\Interfaces\PostboxInterface;
    use Ueef\Postbox\Interfaces\HandlerInterface;
    use Ueef\Postbox\Interfaces\RequestInterface;
    use Ueef\Encoder\Interfaces\EncoderInterface;
    use Ueef\Postbox\Interfaces\ResponseInterface;
    use Ueef\Postbox\Exceptions\HandlerException;

    class Postbox implements PostboxInterface
    {
        /** @var TracerInterface */
        private $tracer = null;

        /** @var DriverInterface */
        private $driver;

        /** @var EncoderInterface */
        private $encoder;


        public function __construct(DriverInterface $driver, EncoderInterface $encoder, ?TracerInterface $tracer = null)
        {
            $this->driver = $driver;
            $this->tracer = $tracer;
            $this->encoder = $encoder;
        }

        public function send(array $route, array $data, int $delayedTo = 0): void
        {
            $request = new Request($data, $route);

            if ($this->tracer) {
                $this->tracer->spanStart(TracerInterface::TYPE_SENDING, $request);
            }

            $this->driverSend($request, $delayedTo);

            if ($this->tracer) {
                $this->tracer->spanFinish(TracerInterface::TYPE_SENDING);
            }
        }

        public function request(array $route, array $data): array
        {
            $request = new Request($data, $route);

            if ($this->tracer) {
                $this->tracer->spanStart(TracerInterface::TYPE_REQUESTING, $request);
            }

            $response = $this->driverRequest($request);

            if ($this->tracer) {
                $this->tracer->spanFinish(TracerInterface::TYPE_REQUESTING, $response);
            }

            if ($response->getErrorCode() > 0) {
                throw new HandlerException($response->getErrorMessage(), $response->getErrorCode());
            }

            return $response->getData();
        }

        public function listen(string $queue, HandlerInterface $handler): void
        {
            $this->driver->listen($queue, function (string $rawRequest) use ($handler): string {

                $request = new Request();
                $request->assign($this->encoder->decode($rawRequest));

                if ($this->tracer) {
                    $this->tracer->spanStart(TracerInterface::TYPE_HANDLING, $request);
                }
                try {
                    $data = $handler->handle($request);

                    if (!is_array($data)) {
                        throw new HandlerException("handler returns not an array");
                    }

                    $response = new Response($data, $request);
                } catch (Throwable $e) {
                    $response = new Response([], $request, $e->getCode() ?: 1, $e->getMessage());
                }
                if ($this->tracer) {
                    $this->tracer->spanFinish(TracerInterface::TYPE_HANDLING, $response);
                }

                return $this->encoder->encode($response->pack());
            });
        }

        public function wait(bool $nonBlocking = false): void
        {
            $this->driver->wait($nonBlocking);
        }

        private function driverSend(RequestInterface $request, int $delayedTo = 0): void
        {
            $this->driver->send($request->getQueue(), $this->encoder->encode($request->pack()), $delayedTo);
        }

        private function driverRequest(RequestInterface $request): ResponseInterface
        {
            $rawResponse = $this->driver->request($request->getQueue(), $this->encoder->encode($request->pack()));
            $rawResponse = $this->encoder->decode($rawResponse);

            $response = new Response();
            $response->assign($rawResponse);

            return $response;
        }
    }
}