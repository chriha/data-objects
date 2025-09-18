<?php

namespace Chriha\DataObjects\Attributes;

use Attribute;
use Chriha\DataObjects\Contracts\Transformer;
use ErrorException;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class TransformWith
{
    public function __construct(
        public Transformer|string $class,
    ) {
        if (! class_exists($this->class)) {
            throw new ErrorException('The transformer class [' . $this->class . '] does not exist.');
        }

        $implementations = class_implements($this->class);

        if (! $implementations || ! in_array(Transformer::class, $implementations, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The transformer class [%s] must implement the [%s] contract.',
                    $this->class,
                    Transformer::class
                )
            );
        }
    }
}
