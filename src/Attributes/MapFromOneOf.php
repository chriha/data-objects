<?php

namespace Chriha\DataObjects\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MapFromOneOf
{
    public const string IGNORE_DEFAULT = 'IGNORE_DEFAULT';

    /**
     * In case the input key from the response cannot be mapped from the $keys,
     * we can use $default to assign a default value to a specific property. For
     * example, if input keys are missing, and we're unable to assign the competition
     * name we can set $default to 'Unknown Competition Name'.
     */
    public function __construct(
        public array $keys,
        public mixed $default = self::IGNORE_DEFAULT,
    ) {
    }

    public function hasDefault(): bool
    {
        return $this->default !== self::IGNORE_DEFAULT;
    }
}
