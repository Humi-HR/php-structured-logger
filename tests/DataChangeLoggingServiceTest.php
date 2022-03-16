<?php

namespace Humi\StructuredLogger\Test;

use Humi\StructuredLogger\DataChangeLoggingService;
use Humi\StructuredLogger\LogTypes;
use Humi\StructuredLogger\MockModel;
use Humi\StructuredLogger\TestLogger;
use PHPUnit\Framework\TestCase;

class DataChangeLoggingServiceTest extends TestCase
{
    /** @test */
    public function it_logs_creation_info(): void
    {
        $logger = new TestLogger();
        $dataChangeLoggingService = new DataChangeLoggingService($logger);

        $attributes = ['id' => 123, 'name' => 'Rick'];
        $model = new MockModel($attributes);

        $dataChangeLoggingService->created($model);

        $records = $logger->records;
        $record = $records[0];
        $attributes = $record['context'][LogTypes::DATA_CHANGED]['attributes'];

        $this->assertCount(1, $records);
        $this->assertSame(DataChangeLoggingService::DATA_CREATED, $record['message']);
        $this->assertSame(MockModel::class, $record['context'][LogTypes::DATA_CHANGED]['data_type']);
        $this->assertSame(123, $record['context'][LogTypes::DATA_CHANGED]['id']);
        $this->assertSame('Rick', $attributes['name']);
        $this->assertCount(2, $attributes);
    }

    /** @test */
    public function it_logs_update_info(): void
    {
        $logger = new TestLogger();
        $dataChangeLoggingService = new DataChangeLoggingService($logger);

        $attributes = ['id' => 123, 'name' => 'Rick'];
        $model = new MockModel($attributes);

        $model->name = 'Steven';

        $dataChangeLoggingService->updated($model);

        $records = $logger->records;
        $record = $records[0];
        $newAttributes = $record['context'][LogTypes::DATA_CHANGED]['new_attributes'];
        $originalAttributes = $record['context'][LogTypes::DATA_CHANGED]['old_attributes'];

        $this->assertCount(1, $records);
        $this->assertSame(DataChangeLoggingService::DATA_UPDATED, $record['message']);
        $this->assertSame(MockModel::class, $record['context'][LogTypes::DATA_CHANGED]['data_type']);
        $this->assertSame(123, $record['context'][LogTypes::DATA_CHANGED]['id']);
        $this->assertSame('Steven', $newAttributes['name']);
        $this->assertSame('Rick', $originalAttributes['name']);
        $this->assertCount(1, $newAttributes);
    }

    /** @test */
    public function it_logs_deletion_info(): void
    {
        $logger = new TestLogger();
        $dataChangeLoggingService = new DataChangeLoggingService($logger);

        $attributes = ['id' => 123, 'name' => 'Rick'];
        $model = new MockModel($attributes);

        $dataChangeLoggingService->deleted($model);

        $records = $logger->records;
        $record = $records[0];
        $attributes = $record['context'][LogTypes::DATA_CHANGED]['attributes'];

        $this->assertCount(1, $records);
        $this->assertSame(DataChangeLoggingService::DATA_DELETED, $record['message']);
        $this->assertSame(MockModel::class, $record['context'][LogTypes::DATA_CHANGED]['data_type']);
        $this->assertSame(123, $record['context'][LogTypes::DATA_CHANGED]['id']);
        $this->assertSame('Rick', $attributes['name']);
        $this->assertCount(2, $attributes);
    }
}
