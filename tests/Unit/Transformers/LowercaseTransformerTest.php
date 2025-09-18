<?php

use Chriha\DataObjects\Transformers\LowercaseTransformer;

it('transforms strings to lower case', function (): void {
    $transformer = new LowercaseTransformer();

    expect($transformer->transform(null, 'property', 'class'))
        ->toBeNull()
        ->and($transformer->transform('LorEm IpsUm', 'property', 'class'))
        ->toBeString()->toBe('lorem ipsum')
        ->not->toBe('LorEm IpsUm');
});
