<?php

namespace Chriha\DataObjects\Transformers;

use Chriha\DataObjects\Contracts\Transformer;

final readonly class StringTransformer implements Transformer
{
    public function transform(mixed $value, string $property, string $class): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return (string) $value;
    }
}
