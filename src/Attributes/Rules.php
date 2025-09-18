<?php

namespace Chriha\DataObjects\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Rules
{
    public function __construct(
        /** @var array<int, mixed> */
        public array $rules
    ) {
    }
}
