<?php

namespace Humi\StructuredLogger;

/**
 * DataChangeLoggable is for an entity that wishes to have it's data changes logged.
 */
interface DataChangeLoggable
{
    public function getIdForLogging(): string;
    public function getTypeForLogging(): string;

    /**
     * getAttributeNamesToObfuscateForLogging returns an array of keys
     * whose values should be obfuscated in logging.
     *
     * If a field is already encrypted, it does not need to obfuscated.
     */
    public function getAttributeNamesToObfuscateForLogging(): array;

    /**
     * getAttributesForLogging returns an associative array of
     * key/value pairs for logging.
     */
    public function getAttributesForLogging(): array;

    /**
     * getOriginalAttributesForLogging returns an associative array of
     * key/value pairs of the original attributes before an update
     * for logging.
     */
    public function getOriginalAttributesForLogging(): array;

    /**
     * getChangedAttributesForLogging returns an associative array of
     * key/value pairs of the updated attributes after an update
     * for logging.
     */
    public function getChangedAttributesForLogging(): array;
}
