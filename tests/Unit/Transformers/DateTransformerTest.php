<?php

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Chriha\DataObjects\Transformers\DateTransformer;

it('transforms string values', function (): void {
    $transformer = new DateTransformer();
    $date = Carbon::parse('2025-01-20 01:23:45');

    /** @var Carbon $transformed */
    $transformed = $transformer->transform($date->format('Y-m-d h:i:s'), 'property', 'class');

    expect($transformed)->not->toBeNull()
        ->and($transformed)->toBeInstanceOf(Carbon::class)
        ->and($transformed->toAtomString())->toBe($date->toAtomString());
});

it('returns null for empty values', function (): void {
    $transformer = new DateTransformer();

    expect($transformer->transform(null, 'property', 'class'))->toBeNull()
        ->and($transformer->transform('', 'property', 'class'))->toBeNull()
        ->and($transformer->transform([], 'property', 'class'))->toBeNull();
});

it('throws an exception for invalid date strings', function (): void {
    $transformer = new DateTransformer();

    $transformer->transform('invalid-date-string', 'property', 'class');
})->throws(InvalidFormatException::class);
