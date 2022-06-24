<?php

namespace Humi\StructuredLogger;

use Psr\Log\LoggerInterface;

/**
 * DataChangeLoggingService is responsible for logging changes.
 */
class DataChangeLoggingService
{
    const DATA_CREATED = 'Data Created';
    const DATA_UPDATED = 'Data Updated';
    const DATA_DELETED = 'Data Deleted';
    const OBFUSCATED_VALUE = '**REDACTED**';

    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * created is used to log the creation of a loggable.
     */
    public function created(DataChangeLoggable $loggable): void
    {
        $attributes = $this->obfuscateValues(
            $loggable->getAttributesForLogging(),
            $loggable->getAttributeNamesToObfuscateForLogging()
        );

        $context = [
            LogTypes::DATA_CHANGED => [
                'id' => $loggable->getIdForLogging(),
                'attributes' => $attributes,
                'data_type' => $loggable->getTypeForLogging(),
            ],
        ];

        $this->logger->info(self::DATA_CREATED, $context);
    }

    /**
     * updated is used to log the update of a loggable.
     */
    public function updated(DataChangeLoggable $loggable): void
    {
        $originalAttributes = $this->obfuscateValues(
            $loggable->getOriginalAttributesForLogging(),
            $loggable->getAttributeNamesToObfuscateForLogging()
        );
        $changedAttributes = $this->obfuscateValues(
            $loggable->getChangedAttributesForLogging(),
            $loggable->getAttributeNamesToObfuscateForLogging()
        );

        $context = [
            LogTypes::DATA_CHANGED => [
                'id' => $loggable->getIdForLogging(),
                'original_attributes' => $originalAttributes,
                'changed_attributes' => $changedAttributes,
                'data_type' => get_class($loggable),
            ],
        ];

        $this->logger->info(self::DATA_UPDATED, $context);
    }

    /**
     * deleted is used to log the deletion of a loggable.
     */
    public function deleted(DataChangeLoggable $loggable): void
    {
        $attributes = $this->obfuscateValues(
            $loggable->getAttributesForLogging(),
            $loggable->getAttributeNamesToObfuscateForLogging()
        );

        $context = [
            LogTypes::DATA_CHANGED => [
                'id' => $loggable->getIdForLogging(),
                'attributes' => $attributes,
                'data_type' => $loggable->getTypeForLogging(),
            ],
        ];

        $this->logger->info(self::DATA_DELETED, $context);
    }

    /**
     * obfuscateValues removes values if their keys are in the list of attributes to obfuscate.
     */
    private function obfuscateValues(array $attributes, array $attributesToObfuscate): array
    {
        $obfuscateMap = array_flip($attributesToObfuscate);

        foreach ($attributes as $key => $_) {
            if (array_key_exists($key, $obfuscateMap)) {
                $attributes[$key] = self::OBFUSCATED_VALUE;
            }
        }

        return $attributes;
    }
}
