<?php

use Chriha\DataObjects\Attributes\MapFrom;
use Chriha\DataObjects\Attributes\TransformWith;
use Chriha\DataObjects\DataObject;
use Tests\Fixtures\Classes\Transformer1;
use Tests\Fixtures\Classes\Transformer2;
use Tests\Fixtures\Classes\Transformer3;

it('can repeatably transform attributes', function (): void {
    $class = new class () extends DataObject {
        #[MapFrom('text')]
        #[TransformWith(Transformer1::class)]
        #[TransformWith(Transformer2::class)]
        #[TransformWith(Transformer3::class)]
        public string $text;
    };

    expect($class::from(['text' => 'Test'])->text)->toEqual(
        'Test|Transformer1|Transformer2|Transformer3'
    );
});

it('throws exception for invalid transformer class', function (): void {
    new TransformWith('stdClass');
})->throws(InvalidArgumentException::class, 'The transformer class [stdClass] must implement the [Chriha\DataObjects\Contracts\Transformer] contract.');

it('throws exception for non-existent transformer class', function (): void {
    new TransformWith('NonExistentClass');
})->throws(ErrorException::class, 'The transformer class [NonExistentClass] does not exist.');
