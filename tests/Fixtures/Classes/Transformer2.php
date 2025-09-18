<?php

namespace Tests\Fixtures\Classes;

use Chriha\DataObjects\Contracts\Transformer;

class Transformer2 implements Transformer
{
    public function transform(mixed $value, string $property, string $class): mixed
    {
        return $value . '|' . class_basename($this);
    }
}
