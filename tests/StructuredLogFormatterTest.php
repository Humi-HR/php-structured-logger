<?php

namespace Humi\StructuredLogger\Tests;

use Humi\StructuredLogger\LogTypes;
use Humi\StructuredLogger\StructuredLogFormatter;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class StructuredLogFormatterTest extends TestCase
{
    /** @test */
    public function it_formats_logs(): void
    {
        /**
         * @var StructuredLogFormatter $formatter
         */
        $formatter = new StructuredLogFormatter();

        $record = [
            'level' => Logger::INFO,
            'datetime' => '2021-03-29',
            'level_name' => 'INFO',
            'message' => 'Some message',
            'context' => [],
        ];

        $formattedRecord = $formatter->format($record);

        $this->assertArrayHasKey('args', $formattedRecord);
        $this->assertArrayHasKey('status_code', $formattedRecord);
        $this->assertArrayHasKey('process_context', $formattedRecord);
        $this->assertArrayHasKey('process_id', $formattedRecord);
        $this->assertArrayHasKey('delta', $formattedRecord);

        $this->assertSame($record['level_name'], $formattedRecord['level_name']);
        $this->assertSame(LogTypes::GENERAL, $formattedRecord['type']);
        $this->assertSame($record['message'], $formattedRecord['message']);
        $this->assertSame('', $formattedRecord['impersonator'], 'impersonator should default to an empty string');
    }

    /** @test */
    public function it_sets_the_correct_type(): void
    {
        /**
         * @var StructuredLogFormatter $formatter
         */
        $formatter = new StructuredLogFormatter();

        $record = [
            'level' => Logger::INFO,
            'datetime' => '2021-03-29',
            'level_name' => 'INFO',
            'message' => 'Some message',
            'context' => [],
        ];

        $formattedRecord = $formatter->format($record);

        $this->assertSame(LogTypes::GENERAL, $formattedRecord['type']);

        $record = [
            'level' => Logger::INFO,
            'datetime' => '2021-03-29',
            'level_name' => 'INFO',
            'message' => 'Some message',
            'context' => ['action' => []],
        ];

        $formattedRecord = $formatter->format($record);

        $this->assertSame(LogTypes::ACTION, $formattedRecord['type']);

        $record = [
            'level' => Logger::INFO,
            'datetime' => '2021-03-29',
            'level_name' => 'INFO',
            'message' => 'Some message',
            'context' => ['data_changed' => []],
        ];

        $formattedRecord = $formatter->format($record);

        $this->assertSame(LogTypes::DATA_CHANGED, $formattedRecord['type']);
    }
}
