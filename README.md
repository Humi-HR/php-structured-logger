# Humi PHP Structured Logger

## What

A structured logger for use in our PHP projects.

<figure>
    <img width="300" src="structured-logger.jpg"
         alt="log structure">
    <figcaption>A log structure.</figcaption>
</figure>

## Why

1. To unify logging across our platforms.
1. To make logging better.

## Install

Add this repo as a repository in Composer.

```json
    "repositories": [
    ...
        {
            "type": "vcs",
            "url": "git@github.com:Humi-HR/php-structured-logger.git"
        }
    ]
```

Require the package.

```bash
composer require Humi-HR/php-structured-logger
```

## Configuration

Configuration is service dependent. Not all services will have the same configuration. However, there are a few things which will likely be similar.

### Extend

The purpose of extending is to layer behaviour on top of the structured logger. The structured logger is ignorant of service specific details.

Begin by extending the StructuredLogHandler and StructuredLogFormatter. Examples of this can be seen in our [HR](https://github.com/Humi-HR/application) app.

### Configure

This is an example of configuration for the HR app found in `/config/logging.php`. This is for a Laravel app.

```php
[
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['structured', 'single'], // add structured to stack
        ],
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],
        'structured' => [
            'driver' => 'monolog',
            'handler' => HRStructuredLogHandler::class, // use your extended handler
            'formatter' => HRStructuredLogFormatter::class,
            'with' => [
                'stream' => storage_path('logs/structured.log'),
            ],
        ],
    ],
];
```

## Additional Helpers

### Test Logger

This package contains a `TestLogger`. This logger is useful for checking that the correct values were logged. It should not be used in production because it doesn't store its log files anywhere.

### Response Stasher

This package contains a `ResponseStasher`. The response stasher should be one of the earliest middelware in the stack. It stashes the response in the service container for use after the request has completed. It can only be used with Laravel.

## Obfuscation

This PHP logger allows for attribute obfuscation on data changes.

Please review the guidelines for what not to log located in [logging section of Humi docs](https://devdocs.humi.ca/guides/logging.html).

### Simple attribute obfuscation

The base model in our HR and Admin apps use the `LogsDataChanges` trait and implements the DataChangeLoggable interface to automatically have their fields logged when data changes.

To obfuscate an attribute in our logs, create an instance variable called `attributesToObfuscateForLogging` on the model, and set its value to an array of keys.

Some model:

```php
protected array $attributesToObfuscateForLogging = ['sin_number', 'home_address'];
```

These fields will now be obfuscated in our logs.

If instead you want to specify only the attributes not to obfuscate, create an instance variable called `attributesNotToObfuscateForLogging` on the model, and set its value to an array of keys. All other values will be obfuscated.

Some model:

```php
protected array $attributesNotToObfuscateForLogging = ['favorite_food'];
```

Do not set both `$attributesToObfuscateForLogging` and `$attributesNotToObfuscateForLogging`. Choose one or the other.

### Dynamic attribute obfuscation

There may be times that we want to obfuscate an attribute only if certain conditions are true.

We can do this by overriding the getAttributeNamesToObfuscateForLogging method.

```php
    public function getAttributeNamesToObfuscateForLogging(): array
    {
        // if the secret value is null, we log null
        if ($this->secret_value === null) {
            return [];
        }

        // if the secret value is not null, we obfuscate it
        return ['secret_value'];
    }
```

### Encrypted attribute obfuscation

The logger will automatically encrypt any value that is _cast_ as encrypted.

```php
 public $casts = [
        'some_secret_thing' => 'encrypted', // automatically encrypted
        'some_number' => 'float', // not automatically encrypted
    ];
```

## Notes

This logger will output JSON to a text file. It is not the logger's responsibility to send the contents of the text file somewhere useful.
