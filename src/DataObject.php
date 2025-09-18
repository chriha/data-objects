<?php

namespace Chriha\DataObjects;

use Chriha\DataObjects\Concerns\HandlesAttributes;
use Chriha\DataObjects\Concerns\HandlesCasts;
use Chriha\DataObjects\Concerns\HandlesTransformations;
use Chriha\DataObjects\Concerns\HandlesValidation;
use Chriha\DataObjects\Contracts\StrictProperties;
use Chriha\DataObjects\Attributes\Ignore;
use Chriha\DataObjects\Attributes\MapFrom;
use Chriha\DataObjects\Attributes\MapFromOneOf;
use Chriha\DataObjects\Exceptions\FailedStrictPropertiesException;
use Chriha\DataObjects\Exceptions\NoMappingKeyFoundException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

abstract class DataObject implements Arrayable
{
    use HandlesAttributes;
    use HandlesCasts;
    use HandlesTransformations;
    use HandlesValidation;

    /** @var array<string, mixed> */
    #[Ignore]
    protected array $rawAttributes = [];

    /** @var array<string, mixed> */
    #[Ignore]
    protected array $filledAttributes = [];

    /**
     * @throws FailedStrictPropertiesException
     * @throws NoMappingKeyFoundException
     */
    public function fill(array $input): void
    {
        $this->rawAttributes = $this->filledAttributes = $input;

        if (method_exists($this, 'beforeFill')) {
            $this->filledAttributes = $this->beforeFill($input) ?? $input;
        }

        $reflect = new ReflectionClass($this);

        $this->validateInput($reflect);

        foreach ($reflect->getProperties() as $property) {
            // ignore computed properties for now. they will be handled, when
            // we have all the other properties to work with
            if ($this->isComputed($property)) {
                $this->handleComputedProperty($property, $reflect);

                continue;
            }

            $this->handleProperty($property, $reflect);
        }

        if (method_exists($this, 'afterFill')) {
            $this->afterFill();
        }

        $this->handleComputed($reflect);
    }

    public function toArray(): array
    {
        $data = [];
        $reflect = new ReflectionClass($this);

        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (is_subclass_of($this->{$property->getName()}, self::class)) {
                $data[$property->getName()] = $this->{$property->getName()}->toArray();
            } elseif ($this->{$property->getName()} instanceof Collection) {
                $data[$property->getName()] = $this->{$property->getName()}
                    ->map(fn (self $item): array => $item->toArray())
                    ->toArray();
            } else {
                $data[$property->getName()] = $this->{$property->getName()};
            }
        }

        return $data;
    }

    public function getRawAttributes(): array
    {
        return $this->rawAttributes;
    }

    public static function from(array $attributes): static
    {
        $reflection = new ReflectionClass(static::class);
        // bypass the constructor, as we are setting the properties manually
        // according to their specified attributes
        $instance = $reflection->newInstanceWithoutConstructor();
        $instance->fill($attributes);

        return $instance;
    }

    protected function keyExists(?string $inputKey): bool
    {
        if (is_null($inputKey)) {
            return false;
        }

        if (! str_contains($inputKey, '.')) {
            return array_key_exists($inputKey, $this->filledAttributes);
        }

        return array_key_exists($inputKey, Arr::dot($this->filledAttributes))
            || collect(Arr::dot($this->filledAttributes))
                ->contains(fn ($value, $key): bool => str_starts_with($key, "$inputKey."));
    }

    /**
     * @param ReflectionClass<DataObject> $reflect
     * @throws FailedStrictPropertiesException
     */
    protected function setProperty(ReflectionProperty $property, ?string $inputKey, ReflectionClass $reflect): void
    {
        $propertyName = $property->getName();

        if ($this->shouldUseDefaultValue($property, $inputKey)) {
            $this->{$propertyName} = $this->getAttribute($property, MapFromOneOf::class)->default;
        } elseif ($this->keyExists($inputKey)) {
            $this->{$propertyName} = $this->castValueToType(
                value: Arr::get($this->filledAttributes, $inputKey),
                property: $property
            );
        } elseif ($this->propertyHasDefaultValue($property, $reflect)) {
            $this->{$propertyName} = $this->getPropertyDefaultValue($property, $reflect);
        } elseif ($property->getType()?->allowsNull()) {
            $this->{$propertyName} = null;
        } elseif ($this instanceof StrictProperties) {
            throw new FailedStrictPropertiesException(
                "The property [$propertyName] is required, but was not found in the input data."
            );
        }
    }

    /**
     * @param ReflectionClass<DataObject> $reflect
     */
    protected function handleComputedProperty(ReflectionProperty $property, ReflectionClass $reflect): void
    {
        if ($this->propertyHasDefaultValue($property, $reflect)) {
            $this->{$property->getName()} = $this->getPropertyDefaultValue($property, $reflect);
        }
    }

    /**
     * @param ReflectionClass<DataObject> $reflect
     * @throws FailedStrictPropertiesException|NoMappingKeyFoundException
     */
    protected function handleProperty(ReflectionProperty $property, ReflectionClass $reflect): void
    {
        if ($this->shouldIgnoreProperty($property)) {
            return;
        }

        $inputKey = $this->determineInputKey($property, $reflect);

        $this->setProperty($property, $inputKey, $reflect);
    }

    /**
     * @param ReflectionClass<DataObject> $reflect
     * @throws NoMappingKeyFoundException
     */
    protected function determineInputKey(ReflectionProperty $property, ReflectionClass $reflect): ?string
    {
        if ($this->hasAttribute($property, MapFrom::class)) {
            return $this->getAttribute($property, MapFrom::class)->key;
        }

        if ($this->hasAttribute($property, MapFromOneOf::class)) {
            return $this->getMapFromOneOfKey($property, $reflect);
        }

        return $property->getName();
    }

    /**
     * @param ReflectionClass<DataObject> $reflect
     * @throws NoMappingKeyFoundException
     */
    protected function getMapFromOneOfKey(ReflectionProperty $property, ReflectionClass $reflect): ?string
    {
        try {
            return $this->getMappedKeyFromOneOf($property);
        } catch (NoMappingKeyFoundException $e) {
            if (! $this->propertyHasDefaultValue($property, $reflect)) {
                throw $e;
            }
        }

        return $property->getName();
    }

    /**
     * We're doing this, because promoted properties behave differently.
     * See https://bugs.php.net/bug.php?id=81386
     */
    protected function propertyHasDefaultValue(ReflectionProperty $property, ReflectionClass $class): bool
    {
        if (! $property->isPromoted()) {
            return $property->hasDefaultValue();
        }

        return $this->getPromotedProperty($property, $class)?->isDefaultValueAvailable() ?? false;
    }

    protected function getPromotedProperty(ReflectionProperty $property, ReflectionClass $class): ?ReflectionParameter
    {
        return collect($class->getConstructor()?->getParameters() ?? [])
            ->filter(fn (ReflectionParameter $param): bool => $param->getName() === $property->getName())
            ->first();
    }

    protected function getPropertyDefaultValue(ReflectionProperty $property, ReflectionClass $class): mixed
    {
        if (! $property->isPromoted()) {
            return $property->getDefaultValue();
        }

        return $this->getPromotedProperty($property, $class)?->getDefaultValue();
    }
}
