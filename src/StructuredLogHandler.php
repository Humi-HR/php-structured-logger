<?php

namespace Humi\StructuredLogger;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * StructuredLogHandler is a Monolog log handler used to generate structured logs
 * It batches log records from a process. Right before the process exists, the
 * logs are formatted and then sent to the StreamHandler which writes the logs.
 *
 * While this class works as is, you may want to extend this class and overwrite
 * the handle method. Perhaps you want to handle based on environment or feature
 * flag.
 *
 * @see https://github.com/Seldaek/monolog
 * @see https://laravel.com/docs/8.x/logging
 */
class StructuredLogHandler extends AbstractHandler
{
    private array $records = [];
    private StreamHandler $streamHandler;
    private FormatterInterface $formatter;

    /**
     * @param mixed $stream can be any stream (ex: file, stdout, etc.)
     */
    public function __construct(
        $stream,
        FormatterInterface $formatter,
        $level = Logger::DEBUG,
        bool $bubble = true,
        ?int $filePermission = null,
        bool $useLocking = false
    ) {
        parent::__construct($level, $bubble);

        $this->streamHandler = new StreamHandler($stream, $level, $bubble, $filePermission, $useLocking);
        $this->streamHandler->setFormatter(new JsonFormatter());

        $this->formatter = $formatter;
    }

    public function handle(array $record): bool
    {
        if ($record['level'] < $this->level) {
            return false;
        }

        $this->records[] = $record;

        return false === $this->bubble;
    }

    /**
     * close is called by Laravel when the app exits
     */
    final public function close(): void
    {
        $this->format();
        $this->flush();
        $this->streamHandler->close();
    }

    /**
     * Send logs off to the stream
     */
    private function flush()
    {
        $this->streamHandler->handleBatch($this->records);
    }

    /**
     * format modifies each record so that the format is consistent
     *
     * @see StructuredLogFormatter
     */
    private function format(): void
    {
        $this->records = $this->formatter->formatBatch($this->records);
    }
}
