<?php

namespace Chriha\DataObjects\Attributes;

use Attribute;

/** Allow properties to keep whitespace (or other characters) from the beginning and end string value */
#[Attribute(Attribute::TARGET_PROPERTY)]
class WithoutTrimming
{
}
