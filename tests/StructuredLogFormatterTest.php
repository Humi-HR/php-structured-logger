<?php

namespace Humi\StructuredLogger\Tests;

use Humi\StructuredLogger\LogTypes;
use Humi\StructuredLogger\StructuredLogFormatter;
use Illuminate\Http\Request;
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
            'context' => ['field1' => ['field2' => 'my_value']],
        ];

        $formattedRecord = $formatter->format($record);

        $this->assertArrayHasKey('args', $formattedRecord);
        $this->assertArrayHasKey('status_code', $formattedRecord);
        $this->assertArrayHasKey('process_context', $formattedRecord);
        $this->assertArrayHasKey('trace_id', $formattedRecord);
        $this->assertArrayHasKey('delta', $formattedRecord);

        $this->assertSame('INFO', $formattedRecord['level']);
        $this->assertSame('', $formattedRecord['causer_id'], 'causer_id should be a string');
        $this->assertSame(LogTypes::GENERAL, $formattedRecord['type']);
        $this->assertSame($record['message'], $formattedRecord['message']);
        $this->assertSame('', $formattedRecord['impersonator'], 'impersonator should default to an empty string');
        $this->assertSame(
            json_encode($record['context']),
            $formattedRecord['context_as_string'],
            'context_raw should be context formatted as json'
        );
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
            'context' => [
                'data_changed' => [
                    'id' => 123,
                    'data_type' => 'SOME\TYPE',
                ],
            ],
        ];

        $formattedRecord = $formatter->format($record);

        $this->assertSame(LogTypes::DATA_CHANGED, $formattedRecord['type']);
    }

    /** @test */
    public function it_sets_the_data_type_and_id_only_when_one_exists(): void
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

        $this->assertSame('', $formattedRecord['data_id']);
        $this->assertSame('', $formattedRecord['data_type']);

        $record = [
            'level' => Logger::INFO,
            'datetime' => '2021-03-29',
            'level_name' => 'INFO',
            'message' => 'Some message',
            'context' => [
                'data_changed' => [
                    'id' => 123,
                    'data_type' => 'SOME\TYPE',
                ],
            ],
        ];

        $formattedRecord = $formatter->format($record);

        $this->assertSame('123', $formattedRecord['data_id']);
        $this->assertSame('SOME\TYPE', $formattedRecord['data_type']);
    }

    /** @test */
    public function it_sets_a_trace_id(): void
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

        $this->assertNotNull(
            $formattedRecord['trace_id'],
            'a trace id should be set even if none is passed in through the header'
        );

        $testFormatter = new class extends StructuredLogFormatter {
            protected function getRequest(): ?Request
            {
                $request = new Request();
                $request->headers->set('x-trace-id', 'my-trace-id');
                $request->server->set('REMOTE_ADDR', '1.1.1.1');

                return $request;
            }
        };

        $formattedRecord = $testFormatter->format($record);

        $this->assertSame(
            'my-trace-id',
            $formattedRecord['trace_id'],
            'the trace id should be identical to that of the header'
        );
    }
}
