# Data Objects

A PHP project for working with flexible data objects, supporting dynamic properties and array initialization.

## Features

- **Dynamic Properties:** Easily set and get properties on data objects.
- **Array Initialization:** Instantiate objects with an associative array of properties.
- **Extensible:** Create your own data object classes by extending the base `DataObject`.

## Installation

```shell
composer require chriha/data-objects
```

## Example Usage

```php
use Chriha\DataObject;
use Chriha\DataObjects\Attributes\MapFrom;
use Chriha\DataObjects\Attributes\Rules;
use Illuminate\Support\Collection;

class Person extends DataObject
{
    #[MapFrom('name_attr')]
    public string $name;
    #[Rules(['required', 'integer'])]
    public int $age;
    #[CollectionOf(Address::class)]
    public Collection $addresses;
    #[TransformWith(UppercaseTransformer::class)]
    public string $country;
}

$person = Person::from(['name_attr' => 'Alice', 'age' => 30]);
echo $person->name; // Alice
echo $person->age;  // 30
```

## Running Tests

This project uses [PestPHP](https://pestphp.com/) for testing.

To run the tests:

```bash
composer install
./vendor/bin/pest
```

## Directory Structure

- `src/` - Source code for data objects
- `tests/` - PestPHP test suite

## Requirements

- PHP 8.3+
- Composer

## License

MIT
