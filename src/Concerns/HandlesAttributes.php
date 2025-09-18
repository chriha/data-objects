<?php

namespace Chriha\DataObjects\Concerns;

use Chriha\DataObjects\Attributes\CollectionOf;
use Chriha\DataObjects\Attributes\Computed;
use Chriha\DataObjects\Attributes\Ignore;
use Chriha\DataObjects\Attributes\MapFromOneOf;
use Chriha\DataObjects\Exceptions\NoMappingKeyFoundException;
use Illuminate\Support\Arr;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

trait HandlesAttributes
{
    /**
     * Determines if a property has a given PHP attribute.
     */
    protected function hasAttribute(ReflectionProperty $property, string $class): bool
    {
        return ! is_empty($property->getAttributes($class));
    }

    /**
     * Get the attribute instance for a given property and class.
     */
    protected function getAttribute(ReflectionProperty $property, string $class): mixed
    {
        $attributes = $property->getAttributes($class);

        if (! is_empty($attributes)) {
            return $attributes[0]->newInstance();
        }

        return null;
    }

    /**
     * Get the all repeatable attribute instances for a given property and class.
     */
    protected function getRepeatableAttribute(ReflectionProperty $property, string $class): array
    {
        $attributes = $property->getAttributes($class);

        return array_map(fn ($attribute): object => $attribute->newInstance(), $attributes);
    }

    /**
     * Determines if a property is meant to be computed. This assumes
     * you have defined a PHP attribute #[Computed]
     */
    protected function isComputed(ReflectionProperty $property): bool
    {
        return $this->hasAttribute($property, Computed::class);
    }

    /**
     * Determines if a property is meant to be a collection of data objects.
     * This assumes you have defined a PHP attribute #[CollectionOf(...)]
     */
    protected function isCollectionOf(ReflectionProperty $property): bool
    {
        return $this->hasAttribute($property, CollectionOf::class);
    }

    protected function handleComputed(ReflectionClass $class): void
    {
        $isComputable = false;

        foreach ($class->getProperties() as $property) {
            if ($isComputable = $this->hasAttribute($property, Computed::class)) {
                break;
            }
        }

        if (! $isComputable) {
            return;
        }

        if (! method_exists($this, 'compute')) {
            throw new RuntimeException(
                'Class [' . static::class . '] with computed properties must '
                . 'implement method [compute].',
            );
        }

        $this->compute();
    }

    protected function getMappedKeyFromOneOf(ReflectionProperty $property): ?string
    {
        /** @var MapFromOneOf $attribute */
        $attribute = $this->getAttribute($property, MapFromOneOf::class);
        $keys = $attribute->keys;

        foreach ($keys as $key) {
            if ($this->keyExists($key)
                && $this->canBeCastedToType(Arr::get($this->filledAttributes, $key), $property)) {
                return $key;
            }
        }

        if ($attribute->hasDefault()) {
            return null;
        }

        throw new NoMappingKeyFoundException(
            "No castable mapping key found for {$property->class}::\${$property->getName()}."
        );
    }

    protected function shouldIgnoreProperty(ReflectionProperty $property): bool
    {
        return $this->hasAttribute($property, Ignore::class);
    }

    protected function shouldUseDefaultValue(ReflectionProperty $property, ?string $inputKey): bool
    {
        return is_null($inputKey) && $this->hasAttribute($property, MapFromOneOf::class);
    }
}
