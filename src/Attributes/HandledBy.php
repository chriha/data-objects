<?php

namespace Chriha\DataObjects\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class HandledBy
{
    public function __construct(
        public string $method
    ) {
    }
}
