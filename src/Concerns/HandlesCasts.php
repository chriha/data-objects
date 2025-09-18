<?php

namespace Chriha\DataObjects\Concerns;

use Chriha\DataObjects\Attributes\CollectionOf;
use Chriha\DataObjects\Attributes\WithoutTrimming;
use Chriha\DataObjects\DataObject;
use BackedEnum;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use InvalidArgumentException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

/**
 * @mixin HandlesAttributes
 * @mixin HandlesTransformations
 */
trait HandlesCasts
{
    /** Determines if a value can be cast to a given type. */
    protected function isCastableType(ReflectionNamedType $type): bool
    {
        // let basic PHP types pass through, assume they can be cast
        if ($type->isBuiltin()) {
            return true;
        }

        $typeName = $type->getName();

        // check for specific classes, nested data objects or enums that could
        // be used for casting
        return enum_exists($typeName) || class_exists($typeName);
    }

    protected function castToSingleType(mixed $value, ReflectionProperty $property)
    {
        /** @var ReflectionNamedType $type */
        $type = $property->getType();
        $typeName = $type->getName();

        return match (true) {
            is_null($value) => $this->handleNullValue($type, $property),
            /** @phpstan-ignore-next-line */
            $type->isBuiltin() => $this->handleBuiltInType($typeName, $value, $property),
            $typeName && enum_exists($typeName) => $this->handleEnumType($typeName, $value, $property),
            is_subclass_of($typeName, DataObject::class) => $this->handleDataObjectType($typeName, $value),
            $this->isCollectionOf($property) => $this->handleCollectionType($property, $value),
            default => $this->handleOtherTypes($typeName, $value),
        };
    }

    protected function handleNullValue(ReflectionNamedType $type, ReflectionProperty $property): null
    {
        if ($type->allowsNull()) {
            return null;
        }

        throw new InvalidArgumentException(
            'Cannot assign null or empty value to property '
            . "{$property->class}::\${$property->getName()} with type [{$type->getName()}]."
        );
    }

    protected function handleBuiltInType(string $typeName, mixed $value, ReflectionProperty $property): mixed
    {
        $converted = $this->castToBuiltInType($typeName, $value);

        return (is_string($converted) && ! $this->hasAttribute($property, WithoutTrimming::class))
            ? trim($converted)
            : $converted;
    }

    /**
     * @return ?BackedEnum
     */
    protected function handleEnumType(string $typeName, mixed $value, ReflectionProperty $property): mixed
    {
        if (is_string($value)) {
            /** @var BackedEnum $typeName */
            return $property->getType()?->allowsNull()
                ? $typeName::tryFrom($value)
                : $typeName::from($value);
        }

        return $value;
    }

    protected function handleDataObjectType(string $typeName, mixed $value): DataObject
    {
        if ($value instanceof DataObject) {
            return $value;
        }

        /** @var DataObject $typeName */
        return $typeName::from(is_array($value) ? $value : []);
    }

    /**
     * Handle collections or arrays of nested data objects
     *
     * @return Collection<int|string, DataObject|Fluent<string, mixed>>
     */
    protected function handleCollectionType(ReflectionProperty $property, mixed $value): Collection
    {
        $collectionType = $this->getAttribute($property, CollectionOf::class)->class;

        if (is_null($collectionType) || ! class_exists($collectionType)) {
            throw new InvalidArgumentException(
                "{$property->class}::\${$property->getName()} uses an invalid "
                . "collection type [{$collectionType}]."
            );
        }

        /** @var Collection<int|string, DataObject|Fluent<string, mixed>> $collection */
        $collection = collect($value)
            ->map(function ($item) use ($collectionType): DataObject|Fluent {
                if ($collectionType === Fluent::class) {
                    return new Fluent($item);
                }

                return $collectionType::from(is_array($item) ? $item : []);
            });

        return $collection;
    }

    protected function handleOtherTypes(string $typeName, mixed $value): mixed
    {
        $implementations = class_implements($typeName);

        if ($implementations && in_array(CarbonInterface::class, $implementations, true)) {
            /** @var Carbon $typeName */
            return $typeName::parse($value);
        }

        return $value;
    }

    protected function canBeCastedToType(mixed $value, ReflectionProperty $property): bool
    {
        // this method is called before transformation, '' becomes NULL later on
        return ! ((is_null($value) || $value === '')
            && ! $property->getType()?->allowsNull());
    }

    protected function propertyCanBeNull(ReflectionProperty $property): bool
    {
        $type = $property->getType();

        return $type
            && method_exists($type, 'allowsNull')
            && $type->allowsNull();
    }

    protected function castValueToType($value, ReflectionProperty $property)
    {
        $value = $this->transformValue($value, $property);
        $type = $property->getType();

        if ($type instanceof ReflectionUnionType) {
            return $this->castUnionType($value, $property, $type);
        } elseif ($type instanceof ReflectionNamedType) {
            return $this->castToSingleType($value, $property);
        }

        // none or other type defined, return value as is
        return $value;
    }

    /**
     * @return DataObject|BackedEnum|Collection|mixed|null
     */
    protected function castUnionType(mixed $value, ReflectionProperty $property, ReflectionUnionType $type): mixed
    {
        // assuming nullable types or a mix of types
        foreach ($type->getTypes() as $typeOption) {
            if ($this->isCastableType($typeOption)) {
                return $this->castToSingleType($value, $property);
            }
        }

        return $value;
    }

    /** Cast a value to a built-in PHP type. */
    protected function castToBuiltInType(string $typeName, mixed $value): mixed
    {
        return match ($typeName) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'string' => (string) $value,
            default => $value,
        };
    }
}
