<?php

namespace Chriha\DataObjects\Attributes;

use Chriha\DataObjects\DataObject;
use Attribute;
use Illuminate\Support\Fluent;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class CollectionOf
{
    public function __construct(
        /** @var class-string<DataObject|Fluent> $class */
        public string $class
    ) {
        if (! is_subclass_of($this->class, DataObject::class)
            && $this->class !== Fluent::class) {
            throw new InvalidArgumentException(
                "Class [{$this->class}] must extend `DataObject::class` or be class `Fluent`."
            );
        }
    }
}
