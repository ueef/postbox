<?php
declare(strict_types=1);

namespace Ueef\Postbox\Tests;

use PHPUnit\Framework\TestCase;
use Ueef\Encoder\Encoders\JsonEncoder;
use Ueef\Postbox\Interfaces\DriverInterface;
use Ueef\Postbox\Interfaces\HandlerInterface;
use Ueef\Postbox\Interfaces\RequestInterface;
use Ueef\Postbox\Postbox;

class PostboxTest extends TestCase
{
    public function testSend()
    {
        $request = [
            'text' => bin2hex(random_bytes(32)),
        ];
        $queue = bin2hex(random_bytes(8));
        $postbox = new Postbox($this->createDriverStub(), new JsonEncoder());

        $response = [];
        $postbox->listen($queue, new class($response) implements HandlerInterface
        {
            public $response;

            public function __construct(&$response)
            {
                $this->response = &$response;
            }

            public function handle(RequestInterface $request): array
            {
                $this->response = $request->getData();
                return [];
            }
        });
        $postbox->send([$queue], $request);
        $postbox->wait(true);

        $this->assertEquals($request, $response);

    }

    public function testRequest()
    {
        $request = [
            'text' => bin2hex(random_bytes(32)),
        ];
        $queue = bin2hex(random_bytes(8));
        $postbox = new Postbox($this->createDriverStub(), new JsonEncoder());

        $postbox->listen($queue, new class implements HandlerInterface
        {
            public function handle(RequestInterface $request): array
            {
                return $request->getData();
            }
        });
        $response = $postbox->request([$queue], $request);

        $this->assertEquals($request, $response);
    }

    private function createDriverStub(): DriverInterface
    {
        return new class implements DriverInterface
        {
            /** @var array */
            private $queues = [];

            /** @var callable[] */
            private $callbacks = [];


            public function wait(bool $nonBlocking = false): void
            {
                do {
                    foreach ($this->queues as $queue => &$messages) {
                        if ($messages && key_exists($queue, $this->callbacks)) {
                            $this->callbacks[$queue](...array_shift($messages));
                        }
                    }
                } while (!$nonBlocking);
            }

            public function listen(string $queue, callable $callback): void
            {
                $this->callbacks[$queue] = function (string $message, string $replyQueue) use ($callback) {
                    $response = $callback($message);
                    if ($replyQueue) {
                        $this->send($replyQueue, $response);
                    }
                };
            }

            public function send(string $queue, string $message, int $delayedTo = 0): void
            {
                $this->queues[$queue][] = [$message, ""];
            }

            public function request(string $queue, string $message): string
            {
                $replyQueue = uniqid();
                $this->queues[$queue][] = [$message, $replyQueue];

                $response = "";
                $this->listen($replyQueue, function ($message) use (&$response) {
                    $response = $message;
                });

                while (!$response) {
                    $this->wait(true);
                }

                return $response;
            }
        };
    }
}