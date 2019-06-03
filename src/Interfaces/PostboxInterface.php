<?php
declare(strict_types=1);

namespace Ueef\Postbox\Interfaces;

interface PostboxInterface
{
    public function wait();
    public function send(string $queue, array $message): void;
    public function consume(string $queue, HandlerInterface $handler);
}