<?php

if (! function_exists('is_empty')) {
    function is_empty(mixed $value, string|int|null $key = null): bool
    {
        if ($value instanceof Countable) {
            return count($value) === 0;
        }

        if (is_array($value) && $key !== null) {
            $value = $value[$key] ?? null;
        }

        return $value === null || $value === '' || $value === [];
    }
}
