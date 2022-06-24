<?php

namespace Humi\StructuredLogger\Tests;

use Humi\StructuredLogger\StructuredLogHandler;
use PHPUnit\Framework\TestCase;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

class StructuredLogHandlerTest extends TestCase
{
    /** @test */
    public function it_handles_when_the_log_level_is_equal_to_threshold(): void
    {
        $stream = fopen('php://temp', 'r');
        $handler = new StructuredLogHandler($stream, Logger::DEBUG, false);
        $handler->setFormatter(new LineFormatter());

        $this->assertTrue($handler->handle(['level' => Logger::DEBUG]));
    }

    /** @test */
    public function it_handles_when_the_log_level_is_above_threshold(): void
    {
        $stream = fopen('php://temp', 'r');
        $handler = new StructuredLogHandler($stream, Logger::DEBUG, false);
        $handler->setFormatter(new LineFormatter());

        $this->assertTrue($handler->handle(['level' => Logger::EMERGENCY]));
    }

    /** @test */
    public function it_does_not_handle_when_the_log_level_is_below_threshold(): void
    {
        $stream = fopen('php://temp', 'r');
        $handler = new StructuredLogHandler($stream, Logger::INFO, false);
        $handler->setFormatter(new LineFormatter());

        $this->assertFalse($handler->handle(['level' => Logger::DEBUG]));
    }

    /** @test */
    public function it_throws_an_exception_when_no_formatter(): void
    {
        $stream = fopen('php://temp', 'r');
        $handler = new StructuredLogHandler($stream, Logger::INFO, false);

        $this->expectException(\LogicException::class);

        $handler->handle(['level' => Logger::DEBUG]);
        $handler->close();
    }
}
