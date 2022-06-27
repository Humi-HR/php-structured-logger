<?php

namespace Humi\StructuredLogger\Tests;

use Humi\StructuredLogger\DataChangeLoggingService;
use Humi\StructuredLogger\LogTypes;
use Humi\StructuredLogger\TestLogger;
use PHPUnit\Framework\TestCase;

class DataChangeLoggingServiceTest extends TestCase
{
    /** @test */
    public function it_logs_creation_info(): void
    {
        $logger = new TestLogger();
        $dataChangeLoggingService = new DataChangeLoggingService($logger);

        $testLoggable = new TestLoggable(['id' => 123, 'name' => 'Rick', 'age' => 40, 'sensitive data' => 'my secret']);

        $dataChangeLoggingService->created($testLoggable);

        $records = $logger->records;
        $record = $records[0];
        $attributes = $record['context'][LogTypes::DATA_CHANGED]['attributes'];

        $this->assertCount(1, $records);
        $this->assertSame(DataChangeLoggingService::DATA_CREATED, $record['message']);
        $this->assertSame($testLoggable->getTypeForLogging(), $record['context'][LogTypes::DATA_CHANGED]['data_type']);
        $this->assertSame('123', $record['context'][LogTypes::DATA_CHANGED]['id']);
        $this->assertSame('Rick', $attributes['name']);
        $this->assertSame(DataChangeLoggingService::OBFUSCATED_VALUE, $attributes['sensitive data']);
        $this->assertCount(4, $attributes);
    }

    /** @test */
    public function it_logs_update_info(): void
    {
        $logger = new TestLogger();
        $dataChangeLoggingService = new DataChangeLoggingService($logger);

        $testLoggable = new TestLoggable(['id' => 123, 'name' => 'Rick', 'age' => 40, 'sensitive data' => 'my secret']);

        $dataChangeLoggingService->updated($testLoggable);

        $records = $logger->records;
        $record = $records[0];
        $originalAttributes = $record['context'][LogTypes::DATA_CHANGED]['original_attributes'];
        $changedAttributes = $record['context'][LogTypes::DATA_CHANGED]['changed_attributes'];

        $this->assertCount(1, $records);
        $this->assertSame(DataChangeLoggingService::DATA_UPDATED, $record['message']);
        $this->assertSame($testLoggable->getTypeForLogging(), $record['context'][LogTypes::DATA_CHANGED]['data_type']);
        $this->assertSame('123', $record['context'][LogTypes::DATA_CHANGED]['id']);
        $this->assertSame('Toby', $changedAttributes['name']);
        $this->assertSame('Rick', $originalAttributes['name']);
        $this->assertSame(DataChangeLoggingService::OBFUSCATED_VALUE, $originalAttributes['sensitive data']);
        $this->assertSame(DataChangeLoggingService::OBFUSCATED_VALUE, $changedAttributes['sensitive data']);
        $this->assertCount(2, $changedAttributes);
    }

    /** @test */
    public function it_logs_deletion_info(): void
    {
        $logger = new TestLogger();
        $dataChangeLoggingService = new DataChangeLoggingService($logger);

        $testLoggable = new TestLoggable(['id' => 123, 'name' => 'Rick', 'age' => 40, 'sensitive data' => 'my secret']);

        $dataChangeLoggingService->deleted($testLoggable);

        $records = $logger->records;
        $record = $records[0];
        $attributes = $record['context'][LogTypes::DATA_CHANGED]['attributes'];

        $this->assertCount(1, $records);
        $this->assertSame(DataChangeLoggingService::DATA_DELETED, $record['message']);
        $this->assertSame($testLoggable->getTypeForLogging(), $record['context'][LogTypes::DATA_CHANGED]['data_type']);
        $this->assertSame('123', $record['context'][LogTypes::DATA_CHANGED]['id']);
        $this->assertSame('Rick', $attributes['name']);
        $this->assertSame(DataChangeLoggingService::OBFUSCATED_VALUE, $attributes['sensitive data']);
        $this->assertCount(4, $attributes);
    }
}
