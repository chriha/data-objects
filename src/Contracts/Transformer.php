<?php

namespace Chriha\DataObjects\Contracts;

interface Transformer
{
    public function transform(mixed $value, string $property, string $class): mixed;
}
