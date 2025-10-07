<?php

/**
 * @phpcs:disable PSR1.Files.SideEffects
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
 */

use Chriha\DataObjects\Attributes\CollectionOf;
use Chriha\DataObjects\DataObject;
use Illuminate\Support\Collection;

class EmptyStringNullableDataObject extends DataObject
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?int $age = null,
    ) {
    }
}

class EmptyStringNonNullableDataObject extends DataObject
{
    public function __construct(
        public string $name,
        public string $email,
        public int $age,
    ) {
    }
}

class NestedEmptyStringDataObject extends DataObject
{
    public function __construct(
        public ?string $title = null,
        public ?EmptyStringNullableDataObject $child = null,
    ) {
    }
}

class EmptyStringCollectionDataObject extends DataObject
{
    public function __construct(
        public ?string $name = null,
        /** @var ?Collection<int, EmptyStringNullableDataObject> */
        #[CollectionOf(EmptyStringNullableDataObject::class)]
        public ?Collection $items = null,
    ) {
    }
}

it('converts empty strings to null for nullable string properties', function (): void {
    $data = EmptyStringNullableDataObject::from([
        'name' => '',
        'email' => '',
        'age' => '',
    ]);

    expect($data->name)->toBeNull()
        ->and($data->email)->toBeNull()
        ->and($data->age)->toBeNull();
});

it('throws exception for non-nullable properties without defaults when empty string provided', function (): void {
    EmptyStringNonNullableDataObject::from([
        'name' => '',
        'email' => 'test@example.com',
        'age' => 25,
    ]);
})->throws(InvalidArgumentException::class);

it('handles empty strings in nested DataObjects', function (): void {
    $data = NestedEmptyStringDataObject::from([
        'title' => '',
        'child' => [
            'name' => '',
            'email' => '',
            'age' => '',
        ],
    ]);

    expect($data->title)->toBeNull()
        ->and($data->child)->toBeInstanceOf(EmptyStringNullableDataObject::class)
        ->and($data->child->name)->toBeNull()
        ->and($data->child->email)->toBeNull()
        ->and($data->child->age)->toBeNull();
});

it('handles empty strings in collections of DataObjects', function (): void {
    $data = EmptyStringCollectionDataObject::from([
        'name' => '',
        'items' => [
            ['name' => '', 'email' => 'test@example.com', 'age' => ''],
            ['name' => 'John', 'email' => '', 'age' => 25],
        ],
    ]);

    expect($data->name)->toBeNull()
        ->and($data->items)->toBeInstanceOf(Collection::class)
        ->and($data->items->count())->toBe(2)
        ->and($data->items->first()->name)->toBeNull()
        ->and($data->items->first()->email)->toBe('test@example.com')
        ->and($data->items->first()->age)->toBeNull()
        ->and($data->items->last()->name)->toBe('John')
        ->and($data->items->last()->email)->toBeNull()
        ->and($data->items->last()->age)->toBe(25);
});

it('handles mixed null and empty string values', function (): void {
    $data = EmptyStringNullableDataObject::from([
        'name' => null,
        'email' => '',
        'age' => 0,
    ]);

    expect($data->name)->toBeNull()
        ->and($data->email)->toBeNull()
        ->and($data->age)->toBe(0);
});

it('preserves non-empty string values', function (): void {
    $data = EmptyStringNullableDataObject::from([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'age' => 30,
    ]);

    expect($data->name)->toBe('John Doe')
        ->and($data->email)->toBe('john@example.com')
        ->and($data->age)->toBe(30);
});

it('handles empty strings in deeply nested arrays', function (): void {
    $data = EmptyStringCollectionDataObject::from([
        'name' => '',
        'items' => [
            [
                'name' => '',
                'email' => '',
                'age' => '',
            ],
        ],
    ]);

    expect($data->name)->toBeNull()
        ->and($data->items->first()->name)->toBeNull()
        ->and($data->items->first()->email)->toBeNull()
        ->and($data->items->first()->age)->toBeNull();
});

it('handles whitespace-only strings as trimmed empty strings', function (): void {
    $data = EmptyStringNullableDataObject::from([
        'name' => '   ',
        'email' => "\t\n",
        'age' => null,
    ]);

    expect($data->name)->toBeNull()
        ->and($data->email)->toBeNull()
        ->and($data->age)->toBeNull();
});
