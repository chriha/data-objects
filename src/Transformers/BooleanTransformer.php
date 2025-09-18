<?php

namespace Chriha\DataObjects\Transformers;

use Chriha\DataObjects\Contracts\Transformer;

class BooleanTransformer implements Transformer
{
    protected array $values = ['yes', 'true', '1', 'on', 'enabled', 'active', 'ok', 'y'];

    public function transform(mixed $value, string $property, string $class): mixed
    {
        if (is_string($value)) {
            $value = strtolower($value);

            return in_array($value, $this->values, true);
        }

        return (bool) $value;
    }
}
