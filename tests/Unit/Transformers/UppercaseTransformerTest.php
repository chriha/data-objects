<?php

use Chriha\DataObjects\Transformers\UppercaseTransformer;

it('transforms strings to upper case', function (): void {
    $transformer = new UppercaseTransformer();

    expect($transformer->transform(null, 'property', 'class'))
        ->toBeNull()
        ->and($transformer->transform('LorEm IpsUm', 'property', 'class'))
        ->toBeString()->toBe('LOREM IPSUM')
        ->not->toBe('LorEm IpsUm');
});
