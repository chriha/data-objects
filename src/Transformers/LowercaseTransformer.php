<?php

namespace Chriha\DataObjects\Transformers;

use Chriha\DataObjects\Contracts\Transformer;

class LowercaseTransformer implements Transformer
{
    public function transform(mixed $value, string $property, string $class): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return strtolower($value);
    }
}
