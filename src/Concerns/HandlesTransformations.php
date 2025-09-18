<?php

namespace Chriha\DataObjects\Concerns;

use Attribute;
use Chriha\DataObjects\Contracts\Transformer;
use Chriha\DataObjects\Attributes\TransformWith;
use ReflectionProperty;

/**
 * @mixin HandlesAttributes
 */
trait HandlesTransformations
{
    /**
     * Transform the value of a property, if a transformer is defined. This assumes
     * you have defined a PHP attribute #[TransformWith(...)]
     */
    protected function transformValue($value, ReflectionProperty $property): mixed
    {
        $value = $value === '' ? null : $value;

        if (is_string($value)) {
            $value = trim($value);
        }

        if (! $this->hasAttribute($property, TransformWith::class)) {
            return $value;
        }

        $attributes = $this->getRepeatableAttribute($property, TransformWith::class);

        foreach ($attributes as $attribute) {
            $value = $this->getTransformer($attribute)
                ->transform($value, $property->getName(), $property->class);
        }

        return $value;
    }

    protected function getTransformer(TransformWith $attribute): Transformer
    {
        if ($attribute->class instanceof Transformer) {
            return $attribute->class;
        } elseif (function_exists('resolve')) {
            return resolve($attribute->class);
        }

        return new $attribute->class();
    }
}
