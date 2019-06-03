<?php
declare(strict_types=1);

namespace Ueef\Postbox;

use Ueef\Encoder\Interfaces\EncoderInterface;
use Ueef\Postbox\Interfaces\DriverInterface;
use Ueef\Postbox\Interfaces\HandlerInterface;
use Ueef\Postbox\Interfaces\PostboxInterface;

class Postbox implements PostboxInterface
{
    /** @var DriverInterface */
    private $driver;

    /** @var EncoderInterface */
    private $encoder;


    public function __construct(DriverInterface $driver, EncoderInterface $encoder)
    {
        $this->driver = $driver;
        $this->encoder = $encoder;
    }

    public function wait()
    {
        $this->driver->wait();
    }

    public function send(string $queue, array $message): void
    {
        $this->driver->send($queue, $this->encoder->encode($message));
    }

    public function consume(string $queue, HandlerInterface $handler)
    {
        $this->driver->consume($queue, function (string $message) use ($handler) {
            $handler->handle($this->encoder->decode($message));
        });
    }
}