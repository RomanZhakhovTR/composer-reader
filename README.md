```markdown
# Composer Reader

A simple `composer.json` file reader.

## Installation

You can install the package via Composer:

```bash
composer require ability/composer-reader
```

## Usage

### Basic Usage

To read and parse a `composer.json` file, you can use the `Reader` class:

```php
use Ability\ComposerReader\Reader;

$context = Reader::create('/path/to/composer.json');
```

### Accessing Data

The `Context` class provides methods to access the data:

```php
use Ability\ComposerReader\Context;

// Get a value by key
$value = $context->get('name');

// Check if a key exists
$exists = $context->has('require.php');
```

### Array Access

The `Context` class implements `ArrayAccess`, so you can use it like an array:

```php
// Get a value by key
$value = $context['name'];

// Check if a key exists
$exists = isset($context['require.php']);
```

### JSON Serialization

The `Context` class implements `JsonSerializable`, so you can easily convert it to JSON:

```php
$json = json_encode($context);
```

## Requirements

- PHP >= 8.0

## Development

To contribute to this project, you can install the development dependencies:

```bash
composer install
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Authors

- Roman Zhakhov <roman.zhakhov@thomsonreuters.com>
```
