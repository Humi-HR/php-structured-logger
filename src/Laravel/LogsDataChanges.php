<?php

namespace Humi\StructuredLogger\Laravel;

/**
 * LogsDataChanges is an implementation of DataChangeLoggable
 * that works for Eloquent models.
 *
 * @see Humi\StructuredLogger\DataChangeLoggable
 */
trait LogsDataChanges
{
    /**
     * attributesToObfuscateForLogging is a list of keys whose values will be obfuscated.
     */
    protected array $attributesToObfuscateForLogging = [];

    public function getIdForLogging(): string
    {
        return $this->getKey();
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
        return $this->getAttributes();
    }

    public function getOriginalAttributesForLogging(): array
    {
        $changedAttributeKeys = array_keys($this->getChangedAttributesForLogging());

        $oldAttributes = [];
        foreach ($changedAttributeKeys as $key) {
            $oldAttributes[$key] = $this->getRawOriginal($key);
        }

        return $oldAttributes;
    }

    public function getChangedAttributesForLogging(): array
    {
        return $this->getChanges();
    }
}
