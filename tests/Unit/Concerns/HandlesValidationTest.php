<?php

use Chriha\DataObjects\Attributes\MapFrom;
use Chriha\DataObjects\Attributes\MapFromOneOf;
use Chriha\DataObjects\Attributes\Rules;
use Chriha\DataObjects\DataObject;
use Illuminate\Validation\ValidationException;

it('throws a ValidationException when required rule fails during fill', function (): void {
    class RequiredEmailObject extends DataObject
    {
        #[Rules(['required', 'email'])]
        public ?string $email = null;
    }

    expect(fn () => RequiredEmailObject::from([]))
        ->toThrow(ValidationException::class);

    $obj = RequiredEmailObject::from(['email' => 'john@example.com']);
    expect($obj->toArray()['email'])->toBe('john@example.com');
});

it('uses MapFrom to resolve validation key', function (): void {
    class MappedEmailObject extends DataObject
    {
        #[MapFrom('email_address')]
        #[Rules(['required', 'email'])]
        public ?string $email = null;
    }

    // Missing mapped key should fail
    expect(fn () => MappedEmailObject::from(['email' => 'john@example.com']))
        ->toThrow(ValidationException::class);

    // Providing the mapped key should pass
    $obj = MappedEmailObject::from(['email_address' => 'john@example.com']);
    expect($obj->toArray()['email'])->toBe('john@example.com');
});

it('uses MapFromOneOf to select the available key for validation', function (): void {
    class OneOfObject extends DataObject
    {
        #[MapFromOneOf(['a', 'b'])]
        #[Rules(['required', 'integer'])]
        public ?int $value = null;
    }

    // Provide key "b" only; validation should use that key
    $obj = OneOfObject::from(['b' => 42]);
    expect($obj->toArray()['value'])->toBe(42);

    // Missing both keys should bubble up as a validation failure because the rule is required
    expect(fn () => OneOfObject::from([]))
        ->toThrow(ValidationException::class);
});

it('validator() respects custom messages and validation attributes', function (): void {
    class MessagesObject extends DataObject
    {
        public function messages(): array
        {
            return [
                'name.required' => 'Your name is required.',
            ];
        }

        public function validationAttributes(): array
        {
            return [
                'name' => 'Full Name',
            ];
        }
    }

    $obj = new class () extends MessagesObject {
        #[Rules(['required'])]
        public ?string $name = null;
    };

    $validator = $obj->validator(['name' => null], ['name' => ['required']]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('name'))
        ->toBe('Your name is required.');
});
