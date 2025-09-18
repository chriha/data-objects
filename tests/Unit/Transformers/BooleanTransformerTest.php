<?php

use Chriha\DataObjects\Transformers\BooleanTransformer;

it('transforms values to a boolean', function (): void {
    $transformer = new BooleanTransformer();

    expect($transformer->transform(null, 'property', 'class'))->toBeFalse()
        ->and($transformer->transform(false, 'property', 'class'))->toBeFalse()
        ->and($transformer->transform('ye', 'property', 'class'))->toBeFalse()
        ->and($transformer->transform('false', 'property', 'class'))->toBeFalse()
        ->and($transformer->transform('fal', 'property', 'class'))->toBeFalse()
        ->and($transformer->transform('', 'property', 'class'))->toBeFalse()
        ->and($transformer->transform(0, 'property', 'class'))->toBeFalse()
        ->and($transformer->transform(1, 'property', 'class'))->toBeTrue()
        ->and($transformer->transform(345, 'property', 'class'))->toBeTrue()
        ->and($transformer->transform('yes', 'property', 'class'))->toBeTrue()
        ->and($transformer->transform('y', 'property', 'class'))->toBeTrue()
        ->and($transformer->transform('true', 'property', 'class'))->toBeTrue()
        ->and($transformer->transform('1', 'property', 'class'))->toBeTrue()
        ->and($transformer->transform('on', 'property', 'class'))->toBeTrue()
        ->and($transformer->transform('enabled', 'property', 'class'))->toBeTrue()
        ->and($transformer->transform('active', 'property', 'class'))->toBeTrue()
        ->and($transformer->transform('ok', 'property', 'class'))->toBeTrue()
        ->and($transformer->transform(true, 'property', 'class'))->toBeTrue();
});
