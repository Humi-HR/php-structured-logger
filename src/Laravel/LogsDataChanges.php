<?php

namespace Humi\StructuredLogger\Laravel;

/**
 * LogsDataChanges is an implementation of DataChangeLoggable that works for Eloquent models.
 *
 * @see Humi\StructuredLogger\DataChangeLoggable
 */
trait LogsDataChanges
{
    protected array $attributesToNeverObfuscate = ['id', 'created_at', 'updated_at', 'deleted_at'];

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
        $attributesToObfuscate = [];

        if (isset($this->attributesToObfuscateForLogging)) {
            $attributesToObfuscate = array_merge($attributesToObfuscate, $this->getAttributesToObfuscateWhenPositive());
        }

        if (isset($this->attributesNotToObfuscateForLogging)) {
            $attributesToObfuscate = array_merge($attributesToObfuscate, $this->getAttributesToObfuscateWhenNegative());
        }

        $attributesToObfuscate = array_merge($attributesToObfuscate, $this->getEncryptedAttributes());

        return array_values(array_unique($attributesToObfuscate));
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

    /**
     * getAttributesToObfuscateWhenPositive gets attributes for when the user has specified $attributesToObfuscateForLogging
     */
    private function getAttributesToObfuscateWhenPositive(): array
    {
        $attrToObfuscate = $this->attributesToObfuscateForLogging;
        $neverObfuscateMap = array_flip($this->attributesToNeverObfuscate);

        // remove attributes that should never be obfuscated
        $attrToObfuscate = array_filter($attrToObfuscate, fn($attr) => !array_key_exists($attr, $neverObfuscateMap));

        // remove attributes that are not actually attributes on this object
        $attrToObfuscate = array_filter($attrToObfuscate, fn($attr) => array_key_exists($attr, $this->getAttributes()));

        return array_values($attrToObfuscate);
    }

    /**
     * getAttributesToObfuscateWhenNegative gets attributes for when the user has specified $attributesNotToObfuscateForLogging
     */
    private function getAttributesToObfuscateWhenNegative(): array
    {
        $neverObfuscateMap = array_flip($this->attributesToNeverObfuscate);
        $notObfuscateMap = array_flip($this->attributesNotToObfuscateForLogging);
        $attrToObfuscate = [];

        foreach ($this->getAttributes() as $attr => $_) {
            if (!array_key_exists($attr, $neverObfuscateMap) && !array_key_exists($attr, $notObfuscateMap)) {
                $attrToObfuscate[] = $attr;
            }
        }

        return $attrToObfuscate;
    }

    /**
     * getEncryptedAttributes looks through the casts property for additional attributes to encrypt
     */
    private function getEncryptedAttributes(): array
    {
        if (!isset($this->casts)) {
            return [];
        }

        $attributes = [];

        foreach ($this->casts as $key => $value) {
            if (str_starts_with($value, 'encrypted')) {
                $attributes[] = $key;
            }
        }

        return $attributes;
    }
}
