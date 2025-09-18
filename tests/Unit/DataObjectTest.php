<?php

use Chriha\DataObjects\DataObject;
use Tests\Fixtures\Classes\Person;

it('serializes nested DataObject', function (): void {
    $object = Person::from([
        'name' => 'Homer Simpson',
        'age' => 39,
        'address' => ['street' => '742 Evergreen Terrace', 'city' => 'Springfield'],
    ]);

    expect($object->name)->toBe('Homer Simpson')
        ->and($object->age)->toBe(39)
        ->and($object->address['street'])->toBe('742 Evergreen Terrace')
        ->and($object->address['city'])->toBe('Springfield');
});

it('can be serialized to array', function (): void {
    $object = Person::from([
        'name' => 'Homer Simpson',
        'age' => 39,
        'address' => ['street' => '742 Evergreen Terrace', 'city' => 'Springfield'],
    ]);

    expect($object->toArray())->toBe([
        'name' => 'Homer Simpson',
        'age' => 39,
        'address' => ['street' => '742 Evergreen Terrace', 'city' => 'Springfield'],
    ]);
});

it('can return the raw attributes array', function (): void {
    $object = Person::from([
        'name' => 'Homer Simpson',
        'age' => 39,
        'address' => ['street' => '742 Evergreen Terrace', 'city' => 'Springfield'],
    ]);

    expect($object->getRawAttributes())->toBe([
        'name' => 'Homer Simpson',
        'age' => 39,
        'address' => ['street' => '742 Evergreen Terrace', 'city' => 'Springfield'],
    ]);
});

it('allows to modify raw input in beforeFill', function (): void {
    $do = new class () extends DataObject {
        public function __construct(
            public string $name = 'John Doe',
            public int $age = 32
        ) {
        }

        /**
         * @param array<string, mixed> $rawInput
         * @return array<string, mixed>
         */
        public function beforeFill(array $rawInput): array
        {
            if (is_empty($rawInput['name'])) {
                unset($rawInput['name']);
            }

            if (is_empty($rawInput['age'])) {
                unset($rawInput['age']);
            }

            return $rawInput;
        }
    };

    $do->fill(['name' => null, 'age' => null]);

    expect($do->name)
        ->toBe('John Doe')
        ->and($do->age)
        ->toBe(32);
})->todo();

it('keeps raw input if nothing to return from beforeFill', function (): void {
    $do = new class () extends DataObject {
        public function __construct(
            public string $name = 'John Doe',
            public int $age = 32
        ) {
        }

        public function beforeFill(): void
        {
            // do some logic as usual
        }
    };

    $do->fill(['name' => null, 'age' => null]);
})
    ->throws(InvalidArgumentException::class)
    ->todo();
