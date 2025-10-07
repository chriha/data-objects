<?php

/**
 * @phpcs:disable PSR1.Files.SideEffects
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
 */

use Chriha\DataObjects\Attributes\CollectionOf;
use Chriha\DataObjects\Attributes\HandledBy;
use Chriha\DataObjects\DataObject;
use Chriha\DataObjects\Exceptions\MissingHandlerMethodException;
use Illuminate\Support\Collection;

// Simple concrete DataObject for testing
class HandledPerson extends DataObject
{
    #[HandledBy('handleName')]
    public string $name;

    protected function handleName(): string
    {
        return rtrim(trim($this->getRawAttributes()['full-name'], '_'), '-');
    }
}

class FailedPerson extends DataObject
{
    #[HandledBy('handleName')]
    public string $name;
}

it('handles a property by using the specified method', function (): void {
    $object = HandledPerson::from([
        'full-name' => '_Homer Simpson-',
    ]);

    expect($object->name)->toBe('Homer Simpson');
});

it('throws an exception if the method does not exist', function (): void {
    FailedPerson::from([
        'full-name' => '_Homer Simpson-',
    ]);
})->expectException(MissingHandlerMethodException::class);
