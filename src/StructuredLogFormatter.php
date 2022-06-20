<?php

namespace Humi\StructuredLogger;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Monolog\Formatter\FormatterInterface;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * StructuredLogFormatter formats log records so that they conform to our
 * structured logging requirements.
 *
 * StructuredLogger expects to be run in a Laravel context.
 *
 * This class should be extended to provide service specific information.
 */
class StructuredLogFormatter implements FormatterInterface
{
    /**
     * SERVICE is the name of the service using this formatter.
     * This should be overwritten in the child class.
     *
     * Ex: HR, Payroll, Admin, etc.
     */
    const SERVICE = '';

    /**
     * A UUID that groups all records in the current process.
     * The process is usually a request, but might also be a job, command, etc.
     */
    private string $uuid;

    /**
     * The request associated with this process
     * This will be null for cli processes, including commands and jobs
     */
    private ?Request $request = null;

    /**
     * The respone associated with this process
     * This will be null for cli processes, including commands and jobs
     */
    private ?Response $response = null;

    public function __construct()
    {
        $this->uuid = Str::uuid()->toString();
        $this->request = $this->getRequest();
        $this->response = $this->getResponse();
    }

    /**
     * format adds keys to the record that are required for structured logging.
     * The record returned will have its keys ordered alphabetically.
     *
     * This is the only place a developer should have to look to see what keys exist
     * in our structured log records.
     */
    final public function format(array $record): array
    {
        $formattedRecord = [];

        $formattedRecord['args'] = $this->getArgs();
        $formattedRecord['causer_id'] = $this->getCauserID();
        $formattedRecord['causer_type'] = $this->getCauserType();
        $formattedRecord['context'] = $record['context'];
        $formattedRecord['context_as_string'] = json_encode($record['context']);
        $formattedRecord['data_id'] = $this->getDataId($record);
        $formattedRecord['data_type'] = $this->getDataType($record);
        $formattedRecord['datetime'] = Carbon::parse($record['datetime'])->toISOString();
        $formattedRecord['delta'] = $this->getDelta($record);
        $formattedRecord['env'] = $this->getEnvironment();
        $formattedRecord['impersonator'] = $this->getImpersonator();
        $formattedRecord['level'] = $record['level_name'];
        $formattedRecord['message'] = $record['message'];
        $formattedRecord['process_context'] = $this->getProcessContext();
        $formattedRecord['process_id'] = $this->uuid;
        $formattedRecord['process_start'] = $this->getProcessStart();
        $formattedRecord['remote_address'] = $this->getRemoteAddress();
        $formattedRecord['request_method'] = $this->getRequestMethod();
        $formattedRecord['request_query'] = $this->getRequestQuery();
        $formattedRecord['request_url'] = $this->getRequestURL();
        $formattedRecord['status_code'] = $this->getStatusCode();
        $formattedRecord['service'] = static::SERVICE;
        $formattedRecord['type'] = $this->getType($record);

        return $formattedRecord;
    }

    final public function formatBatch(array $records): array
    {
        $formattedRecords = [];

        foreach ($records as $record) {
            $formattedRecords[] = $this->format($record);
        }

        return $formattedRecords;
    }

    /**
     * getCauserID returns a numeric id that represents
     * the entity (usually user) that caused this log to
     * exist.
     */
    protected function getCauserID(): string
    {
        return '';
    }

    /**
     * getCauserType returns a string that represents the
     * type of the entity (usually user) that caused this
     * log to exist exist.
     */
    protected function getCauserType(): string
    {
        return '';
    }

    /**
     * getImpersonator returns a string that represents the
     * impersonator of the current process, if an impersonator exists
     *
     * What an impersonator is depends on the system using logger,
     * but most likely it is one person logging into the account of another person.
     */
    protected function getImpersonator(): string
    {
        return '';
    }

    final protected function getStatusCode(): int
    {
        if (!$this->hasResponse()) {
            return 0;
        }

        return $this->getResponse()->getStatusCode();
    }

    final protected function getArgs(): string
    {
        if (!isset($_SERVER['argv'])) {
            return '';
        }

        return implode(' ', $_SERVER['argv']);
    }

    final protected function getRequestMethod(): string
    {
        if (!$this->hasRequest()) {
            return '';
        }

        return $this->request->method();
    }

    final protected function getRemoteAddress(): string
    {
        if (!$this->hasRequest()) {
            return '';
        }

        return $this->request->ip();
    }

    final protected function getRequestURL(): string
    {
        if (!$this->hasRequest()) {
            return '';
        }

        return $this->request->url();
    }

    final protected function getRequestQuery(): string
    {
        if (!$this->hasRequest()) {
            return '';
        }

        $url = $this->request->fullUrl();

        $parsedUrl = parse_url($url);

        if (!$parsedUrl || !isset($parsedUrl['query'])) {
            return '';
        }

        return $parsedUrl['query'];
    }

    final protected function getProcessContext(): string
    {
        if ($this->hasRequest()) {
            return LogContexts::REQUEST;
        }

        return LogContexts::CLI;
    }

    /**
     * getDataId will return an id associated with a data change
     * if this record is not a data change record, it will return an empty string
     *
     * the return type is a string in the anticipation that ids could be non numeric
     */
    final protected function getDataId(array $record): string
    {
        if (!$this->isDataChange($record)) {
            return '';
        }

        return (string) $record['context'][LogTypes::DATA_CHANGED]['id'];
    }

    /**
     * getDataType will return a type associated with a data change
     * if this record is not a data change record, it will return an empty string
     */
    final protected function getDataType(array $record): string
    {
        if (!$this->isDataChange($record)) {
            return '';
        }

        return $record['context'][LogTypes::DATA_CHANGED]['data_type'];
    }

    /**
     * getType determines the type of the log record.
     */
    final protected function getType(array $record): string
    {
        if (Arr::exists($record['context'], LogTypes::ACTION)) {
            return LogTypes::ACTION;
        }

        if (Arr::exists($record['context'], LogTypes::DATA_CHANGED)) {
            return LogTypes::DATA_CHANGED;
        }

        return LogTypes::GENERAL;
    }

    /**
     * getResponse returns the Response that can be associated with this log.
     * It is valid for no response to exist and thus null is returned.
     *
     * This should be extended by the child class.
     *
     * We are type hinting with Symfony\Component\HttpFoundation\Response
     * because not all Laravel responses extend Illuminate\Http\Response
     */
    protected function getResponse(): ?Response
    {
        return null;
    }

    final protected function hasResponse(): bool
    {
        return (bool) $this->response;
    }

    final protected function hasRequest(): bool
    {
        return (bool) $this->request;
    }

    /**
     * getRequest returns the Request that can be associated with this log.
     * It is valid for no request to exist and thus null is returned.
     *
     * This should be extended by the child class.
     */
    protected function getRequest(): ?Request
    {
        return null;
    }

    /**
     * getProcessStart returns the datetime of the process start as a string
     * or returns null if the process start is unknown.
     */
    protected function getProcessStart(): ?string
    {
        return null;
    }

    /**
     * getDelta returns the difference in milliseconds since the process started
     * or returns null if the process start is unknown
     */
    protected function getDelta(array $record): ?int
    {
        return null;
    }

    protected function getEnvironment(): ?string
    {
        return null;
    }

    final protected function isDataChange(array $record): bool
    {
        return Arr::exists($record['context'], LogTypes::DATA_CHANGED);
    }
}
