<?php

namespace Chriha\DataObjects\Attributes;

use Attribute;

/** Allow properties to be completely ignored, when filling the object */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Ignore
{
}
