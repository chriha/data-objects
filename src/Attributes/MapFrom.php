<?php

namespace Chriha\DataObjects\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MapFrom
{
    public function __construct(
        public string $key
    ) {
    }
}
