<?php

/**
 * @phpcs:disable PSR1.Files.SideEffects
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Chriha\DataObjects\Attributes\Rules;
use Chriha\DataObjects\DataObject;
use Illuminate\Validation\ValidationException;

class ValidationTest extends DataObject
{
    public function __construct(
        #[Rules(['required'])]
        public string $name,
        #[Rules(['required', 'integer', 'max:30'])]
        public int $age,
        #[Rules(['required', 'in:female,male'])]
        public string $gender,
        #[Rules(['required'])]
        public string $email,
    ) {
    }
}

it('throws an exception, if the passed rules fail validation', function (): void {
    $class = new class () extends DataObject {
        #[Rules(['required', 'max:2'])]
        public string $name;
    };

    $class::from(['name' => 'some name']);
})->expectExceptionMessage('validation.max.string');

it('validates the input according to the specified rules', function (): void {
    $class = new class () extends DataObject {
        #[Rules(['string', 'max:2'])]
        public string $country;
    };

    expect($class::from(['country' => 'DE']))->toBeInstanceOf(DataObject::class);
});

it('validates the whole input before filling the object', function (array $input): void {
    $exception = null;

    try {
        ValidationTest::from($input);
    } catch (ValidationException $e) {
        $exception = $e;
    }

    expect($exception)->toBeInstanceOf(ValidationException::class)
        ->and($exception->errors())->toBe([
            'name' => ['validation.required'],
            'age' => ['validation.max.numeric'],
            'gender' => ['validation.in'],
            'email' => ['validation.required'],
        ]);
})->with([
    'invalid-input' => [
        ['name' => null, 'age' => 32, 'gender' => 'invalid'],
    ],
]);
