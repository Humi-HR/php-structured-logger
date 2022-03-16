<?php

namespace Humi\StructuredLogger;

/**
 * MockModel tries hard to act like a Laravel model.
 */
class MockModel
{
    public array $original;

    public function __construct(public array $attributes = [])
    {
        $this->original = $this->attributes;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getChanges(): array
    {
        $changes = [];

        foreach ($this->attributes as $key => $value) {
            if ($this->original[$key] !== $value) {
                $changes[$key] = $value;
            }
        }

        return $changes;
    }

    public function getRawOriginal(string $name)
    {
        return $this->original[$name];
    }

    public function __get(string $key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        return null;
    }

    public function __set(string $key, string $value)
    {
        $this->attributes[$key] = $value;
    }
}
