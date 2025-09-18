<?php

/**
 * @phpcs:disable PSR1.Files.SideEffects
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
 */

use Chriha\DataObjects\Attributes\CollectionOf;
use Chriha\DataObjects\DataObject;
use Illuminate\Support\Collection;

/**
 * These tests target the toArray() logic of App\DataObjects\DataObject,
 * specifically the branch that maps over Illuminate\Support\Collection
 * items and calls ->toArray() on each (line ~83).
 */

// Simple concrete DataObject for testing
class Person extends DataObject
{
    public string $name;

    /** @var ?Collection<int, Person> */
    #[CollectionOf(Person::class)]
    public ?Collection $children = null;
}

it('serializes a Collection of DataObjects, and a Collection of Fluent items', function (): void {
    $object = Person::from([
        'name' => 'Homer Simpson',
        'children' => [
            ['name' => 'Bart Simpson'],
            ['name' => 'Lisa Simpson'],
            ['name' => 'Maggie Simpson'],
        ],
    ]);

    expect($object->name)->toBe('Homer Simpson')
        ->and($object->children)->toBeInstanceOf(Collection::class)
        ->and($object->children->all())->toHaveCount(3)
        ->and($object->children->first())->toBeInstanceOf(Person::class)
        ->and($object->children->first()->name)->toBe('Bart Simpson');
});

it('serializes an empty Collection property to an empty array', function (): void {
    $person = Person::from([
        'name' => 'Homer Simpson',
        'children' => [],
    ]);

    expect($person->toArray())->toBe([
        'name' => 'Homer Simpson',
        'children' => [],
    ]);
});
