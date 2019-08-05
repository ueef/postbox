<?php
declare(strict_types=1);

namespace Ueef\Postbox;

use Ueef\Postbox\Interfaces\DriverInterface;
use Ueef\Postbox\Interfaces\PostboxInterface;
use Ueef\Encoder\Interfaces\EncoderInterface;

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

    public function wait(bool $nonBlocking = false, float $timeout = 0): void
    {
        $this->driver->wait($nonBlocking, $timeout);
    }

    public function send(string $queue, string $exchange, array $message): void
    {
        $this->driver->send($queue, $exchange, $this->encoder->encode($message));
    }

    public function bind(string $queue, string $exchange)
    {
        $this->driver->bind($queue, $exchange);
    }

    public function consume(string $queue, callable $handler)
    {
        $this->driver->consume($queue, function (string $message) use ($handler) {
            return $handler($this->encoder->decode($message));
        });
    }
}