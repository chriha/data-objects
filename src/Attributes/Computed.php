<?php

namespace Chriha\DataObjects\Attributes;

use Attribute;

/**
 * Computed properties are properties that are not directly mapped
 * from the input. Instead, they are properties that are built based
 * on the values of other properties.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Computed
{
}
