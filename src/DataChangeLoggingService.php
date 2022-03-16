<?php

namespace Humi\StructuredLogger;

use Psr\Log\LoggerInterface;

/**
 * DataChangeLoggingService is responsible for logging changes to models.
 * It accepts a LoggerInterface in its constructor, which by default will
 * be the structured log channel.
 */
class DataChangeLoggingService
{
    const DATA_CREATED = 'Data Created';
    const DATA_UPDATED = 'Data Updated';
    const DATA_DELETED = 'Data Deleted';

    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * created is used to log the creation of a model
     */
    public function created($model): void
    {
        $context = [
            LogTypes::DATA_CHANGED => [
                'id' => $model->id,
                'attributes' => $model->getAttributes(),
                'data_type' => get_class($model),
            ],
        ];

        $this->logger->info(self::DATA_CREATED, $context);
    }

    /**
     * updated is used to log the update of a model
     */
    public function updated($model): void
    {
        $changedAttributes = $model->getChanges();
        $keys = array_keys($changedAttributes);

        $onlyUpdatedAtChanged = count($keys) === 1 && $keys[0] === 'updated_at';

        if ($onlyUpdatedAtChanged) {
            return;
        }

        $oldAttributes = [];
        foreach ($keys as $key) {
            $oldAttributes[$key] = $model->getRawOriginal($key);
        }

        $context = [
            LogTypes::DATA_CHANGED => [
                'id' => $model->id,
                'old_attributes' => $oldAttributes,
                'new_attributes' => $changedAttributes,
                'data_type' => get_class($model),
            ],
        ];

        $this->logger->info(self::DATA_UPDATED, $context);
    }

    /**
     * deleted is used to log the deletion of a model
     */
    public function deleted($model): void
    {
        $context = [
            LogTypes::DATA_CHANGED => [
                'id' => $model->id,
                'attributes' => $model->getAttributes(),
                'data_type' => get_class($model),
            ],
        ];

        $this->logger->info(self::DATA_DELETED, $context);
    }
}
