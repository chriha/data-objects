<?php

namespace Chriha\DataObjects\Concerns;

use Chriha\DataObjects\Attributes\Rules;
use Chriha\DataObjects\DataObject;
use Chriha\DataObjects\Exceptions\NoMappingKeyFoundException;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory as ValidationFactory;
use Illuminate\Validation\Validator;
use ReflectionClass;

/**
 * @mixin DataObject
 */
trait HandlesValidation
{
    /**
     * @throws NoMappingKeyFoundException
     */
    private function validateInput(ReflectionClass $reflect): void
    {
        $rules = [];

        foreach ($reflect->getProperties() as $property) {
            if ($this->shouldIgnoreProperty($property)) {
                continue;
            }

            $inputKey = $this->determineInputKey($property, $reflect);

            if (! $this->hasAttribute($property, Rules::class)) {
                continue;
            }

            /** @var Rules $ruleAttribute */
            $ruleAttribute = $this->getAttribute($property, Rules::class);
            $rules[$inputKey] = $ruleAttribute->rules;
        }

        if (! is_empty($rules)) {
            $this->validator($this->filledAttributes, $rules)->validate();
        }
    }

    /**
     * Get the Validator Factory. If none was set, a minimal default is created
     * using an ArrayLoader-backed Translator with the "en" locale.
     */
    public static function validatorFactory(): ValidationFactory
    {
        $loader = new ArrayLoader();

        // Load Laravel's validation messages
        $validationPath = dirname(__DIR__, 2) . '/vendor/illuminate/translation/lang/en/validation.php';

        if (file_exists($validationPath)) {
            $loader->addMessages('en', 'validation', require $validationPath);
        }

        return new ValidationFactory(
            new Translator($loader, 'en')
        );
    }

    /**
     * Build a Validator for the given data (defaults to $this->toArray()).
     * If the data object defines rules(), messages(), or validationAttributes(),
     * they will be used automatically.
     */
    public function validator(array $data, array $rules = []): Validator
    {
        $factory = static::validatorFactory();
        $messages = method_exists($this, 'messages') ? $this->messages() : [];
        $attributes = method_exists($this, 'validationAttributes')
            ? $this->validationAttributes()
            : [];

        return $factory->make($data, $rules, $messages, $attributes);
    }
}
