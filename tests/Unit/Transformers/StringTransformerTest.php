<?php

use Chriha\DataObjects\Transformers\StringTransformer;

it('transforms string values', function (): void {
    $transformer = new StringTransformer();

    expect($transformer->transform(null, 'property', 'class'))
        ->toBeNull()
        ->and($transformer->transform(56, 'property', 'class'))
        ->toBeString()->toBe('56')
        ->and($transformer->transform(0, 'property', 'class'))
        ->toBeString()->toBe('0');
});
