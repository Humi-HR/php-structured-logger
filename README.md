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
            'with' => [
                'stream' => storage_path('logs/structured.log'),
                'formatter' => app(HRStructuredLogFormatter::class), // use your extended formatter
            ],
        ]
    ]
]
```

## Notes

This logger will output JSON to a text file. It is not the logger's responsibility to send the contents of the text file somewhere useful.
