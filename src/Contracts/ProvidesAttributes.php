<?php

namespace Chriha\DataObjects\Contracts;

interface ProvidesAttributes
{
    /** @return array<string, mixed> */
    public function toAttributes(): array;
}
