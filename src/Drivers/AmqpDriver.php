<?php
declare(strict_types=1);

namespace Ueef\Postbox\Drivers;

use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use Ueef\Postbox\Interfaces\DriverInterface;

class AmqpDriver implements DriverInterface
{
    /** @var AMQPChannel */
    private $channel;


    public function __construct(AbstractConnection $connection)
    {
        $this->channel = $connection->channel();
        $this->channel->basic_qos(null, 1, null);
    }

    public function wait(bool $nonBlocking = false, float $timeout = 0): void
    {
        try {
            $this->channel->wait(null, $nonBlocking, $timeout);
        } catch (AMQPTimeoutException $e) {}
    }

    public function send(string $queue, string $exchange, string $message): void
    {
        $this->channel->basic_publish(new AMQPMessage($message), $exchange, $queue);
    }

    public function bind(string $queue, string $exchange)
    {
        $this->channel->exchange_declare($exchange, 'direct', false, false, false);
        $this->channel->queue_bind($queue, $exchange);
    }

    public function consume(string $queue, callable $callback): void
    {
        $this->channel->queue_declare($queue, false, false, false, false);

        $this->channel->basic_consume($queue, '', false, false, false, false, function (AMQPMessage $message) use ($callback) {
            if ($callback($message->getBody())) {
                $this->channel->basic_ack($message->delivery_info['delivery_tag']);
            } else {
                $this->channel->basic_nack($message->delivery_info['delivery_tag'], false, true);
            }
        });
    }
}
