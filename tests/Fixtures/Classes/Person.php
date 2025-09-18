<?php

namespace Tests\Fixtures\Classes;

use Chriha\DataObjects\DataObject;

final class Person extends DataObject
{
    public string $name;

    public int $age;

    public array $address;
}
