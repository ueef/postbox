<?php
declare(strict_types=1);

namespace Ueef\Postbox\Drivers;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Ueef\Encoder\Interfaces\EncoderInterface;
use Ueef\Postbox\Interfaces\DriverInterface;

class AmqpDriver implements DriverInterface
{
    /** @var AMQPChannel */
    private $channel;

    /** @var EncoderInterface */
    private $encoder;


    public function __construct(AMQPStreamConnection $connection, EncoderInterface $encoder)
    {
        $this->channel = $connection->channel();
        $this->encoder = $encoder;
    }

    public function wait(): void
    {
        while($this->channel->callbacks) {
            $this->channel->wait();
        }
    }

    public function send(string $queue, string $message): void
    {
        $this->channel->basic_publish(new AMQPMessage($message), '', $queue);
    }

    public function consume(string $queue, callable $callback): void
    {
        $this->channel->queue_declare($queue, false, false, false, false);
        $this->channel->basic_qos(null, 1, null);

        $this->channel->basic_consume($queue, '', false, false, false, false, function (AMQPMessage $message) use ($callback) {
            $result = $callback($message->getBody());
            if ($message->has('reply_to')) {
                $message->delivery_info['channel']->basic_publish(new AMQPMessage($result, ['correlation_id' => $message->get('correlation_id')]), '', $message->get('reply_to'));
            }

            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        });
    }
}
