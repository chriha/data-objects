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
use ReflectionException;

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

        // Try to load Laravel's validation messages from possible locations
        $validationPath = static::findValidationTranslationPath();

        if ($validationPath && file_exists($validationPath)) {
            $loader->addMessages('en', 'validation', require $validationPath);
        }

        return new ValidationFactory(
            new Translator($loader, 'en')
        );
    }

    /**
     * Find the path to Laravel's validation translation file.
     * Checks multiple possible locations to support both local development
     * and when installed as a composer package.
     */
    protected static function findValidationTranslationPath(): ?string
    {
        try {
            $reflector = new ReflectionClass(Translator::class);
            $translatorPath = dirname($reflector->getFileName());

            $path = dirname($translatorPath) . '/lang/en/validation.php';

            if (file_exists($path)) {
                return $path;
            }
        } catch (ReflectionException $e) {
            // Continue to fallback paths
        }

        // Fallback: Check common locations
        $possiblePaths = [
            // When this package is installed in a project
            dirname(__DIR__, 4) . '/illuminate/translation/lang/en/validation.php',
            // When developing locally
            dirname(__DIR__, 2) . '/vendor/illuminate/translation/lang/en/validation.php',
            // Laravel project structure
            dirname(__DIR__, 4) . '/laravel/framework/src/Illuminate/Translation/lang/en/validation.php',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
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
