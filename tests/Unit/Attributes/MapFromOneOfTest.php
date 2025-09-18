<?php

use Chriha\DataObjects\Attributes\MapFromOneOf;
use Chriha\DataObjects\DataObject;
use Chriha\DataObjects\Exceptions\NoMappingKeyFoundException;

it('should set the MapFromOneOf default value, if no fields found', function (): void {
    $class = new class () extends DataObject {
        #[MapFromOneOf(['test'], default: 'Some String')]
        public string $name;
    };

    expect($class::from(['test2' => 'fubar'])->name)->toBe('Some String');
});

it('should not throw NoMappingKeyFoundException when property has default value', function (): void {
    $testClass = new class () extends DataObject {
        #[MapFromOneOf(['test', 'text'])]
        public ?string $test = 'default';
    };

    expect($testClass::from(['dummy' => 'test'])->test)->toEqual('default')
        ->and($testClass::from(['test' => 'test text'])->test)->toEqual('test text');
});

it('throws NoMappingKeyFoundException when property does not have default value', function (): void {
    $testClass = new class () extends DataObject {
        #[MapFromOneOf(['test', 'text'])]
        public ?string $test;
    };

    $testClass::from(['dummy' => 'test'])->test;
})->throws(NoMappingKeyFoundException::class);
