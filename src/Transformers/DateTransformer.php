<?php

namespace Chriha\DataObjects\Transformers;

use Carbon\Carbon;
use Chriha\DataObjects\Contracts\Transformer;

use function Chriha\DataObjects\is_empty;

final class DateTransformer implements Transformer
{
    public function transform(mixed $value, string $property, string $class): mixed
    {
        if (is_empty($value)) {
            return null;
        }

        return Carbon::parse($value);
    }
}
