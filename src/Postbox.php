<?php
declare(strict_types=1);

namespace Ueef\Postbox {

    use Throwable;
    use Ueef\Postbox\Interfaces\HandlerInterface;
    use Ueef\Postbox\Interfaces\TracerInterface;
    use Ueef\Postbox\Interfaces\DriverInterface;
    use Ueef\Postbox\Interfaces\PostboxInterface;
    use Ueef\Postbox\Exceptions\HandlerException;
    use Ueef\Encoder\Interfaces\EncoderInterface;

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

        public function send(array $route, array $data, int $delay = 0): void
        {
            $delayedTo = 0;
            if ($delay) {
                $delayedTo = time() + $delay;
            }

            $request = new Request($data, $route, $delayedTo);

            if ($this->tracer) {
                $this->tracer->spanStart(TracerInterface::TYPE_SENDING, $request);
            }

            $this->driver->send($request->getQueue(), $this->encoder->encode($request->pack()));

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

            $response = new Response();
            $response->assign(
                $this->encoder->decode(
                    $this->driver->request($request->getQueue(), $this->encoder->encode($request->pack()))
                )
            );

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
            $this->driver->listen($queue, function (string $rawRequest) use ($handler) {

                $request = new Request();
                $request->assign($this->encoder->decode($rawRequest));

                if ($this->tracer) {
                    $this->tracer->spanStart(TracerInterface::TYPE_HANDLING, $request);
                }
                try {
                    $data = $handler($request);

                    if (null === $data) {
                        $data = [];
                    }

                    if (!is_array($data)) {
                        throw new HandlerException('handler returns not an array');
                    }

                    $response = new Response($data, $request);
                } catch (Throwable $e) {
                    $errorCode = $e->getCode();
                    $errorMessage = $e->getMessage();

                    $response = new Response([], $request, $errorCode, $errorMessage);
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
    }
}