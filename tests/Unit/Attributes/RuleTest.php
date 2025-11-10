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

    expect(static fn (): never => $class::from(['name' => 'some name']))
        ->toThrow(ValidationException::class, 'The name field must not be greater than 2 characters.');
});

it('throws an exception in another language, if the passed rules fail validation', function (): void {
    DataObject::setValidationLocale('de');
    DataObject::setValidationTranslationPath(dirname(__DIR__, 2) . '/Fixtures/Lang/de.php');

    $class = new class () extends DataObject {
        #[Rules(['required', 'max:2'])]
        public string $name;
    };

    try {
        expect(static fn (): never => $class::from(['name' => 'some name']))
            ->toThrow(ValidationException::class, 'Das Feld name darf nicht mehr als 2 Zeichen haben.');
    } finally {
        DataObject::resetValidationSettings();
    }
});

it("throws an exception with translation path, if translation cannot be found", function (): void {
    DataObject::setValidationLocale('de');

    $class = new class () extends DataObject {
        #[Rules(['required', 'max:2'])]
        public string $name;
    };

    try {
        expect(static fn (): never => $class::from(['name' => 'some name']))
            ->toThrow(ValidationException::class, 'validation.max.string');
    } finally {
        DataObject::resetValidationSettings();
    }
});

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
            'name' => ['The name field is required.'],
            'age' => ['The age field must not be greater than 30.'],
            'gender' => ['The selected gender is invalid.'],
            'email' => ['The email field is required.'],
        ]);
})->with([
    'invalid-input' => [
        ['name' => null, 'age' => 32, 'gender' => 'invalid'],
    ],
]);
