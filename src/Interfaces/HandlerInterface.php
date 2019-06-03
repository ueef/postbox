<?php
declare(strict_types=1);

namespace Ueef\Postbox\Interfaces;

interface HandlerInterface
{
    public function handle(array $message): void;
}