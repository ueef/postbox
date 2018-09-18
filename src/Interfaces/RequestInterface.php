<?php
declare(strict_types=1);

namespace Ueef\Postbox\Interfaces;

use Ueef\Packable\Interfaces\PackableInterface;

interface RequestInterface extends PackableInterface
{
    public function getData(): array;
    public function getRoute(): array;
    public function getQueue(): string;
    public function setContext(array $context): void;
    public function getContext(): array;
}

