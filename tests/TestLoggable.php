<?php

namespace Humi\StructuredLogger\Tests;

use Humi\StructuredLogger\DataChangeLoggable;

class TestLoggable implements DataChangeLoggable
{
    public array $attributesToObfuscateForLogging = ['sensitive data'];

    public array $attributes = [];

    public function __construct(array $attributes = null)
    {
        if ($attributes) {
            $this->attributes = $attributes;
        }
    }

    public function getIdForLogging(): string
    {
        return $this->attributes['id'];
    }

    public function getTypeForLogging(): string
    {
        return get_class($this);
    }

    public function getAttributeNamesToObfuscateForLogging(): array
    {
        return $this->attributesToObfuscateForLogging;
    }

    public function getAttributesForLogging(): array
    {
        return $this->attributes;
    }

    public function getOriginalAttributesForLogging(): array
    {
        return [
            'name' => $this->attributes['name'],
            'sensitive data' => $this->attributes['sensitive data'],
        ];
    }

    public function getChangedAttributesForLogging(): array
    {
        return [
            'name' => 'Toby',
            'sensitive data' => 'another secret',
        ];
    }
}
