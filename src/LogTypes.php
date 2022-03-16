<?php

namespace Humi\StructuredLogger;

/**
 * LogTypes is an enum that lists the different types of logs we produce.
 *
 * The "type" of a log is its category.
 * It is *not* the same as level (info, error, etc.).
 */
class LogTypes
{
    /**
     * GENERAL is the default log type.
     */
    const GENERAL = 'general';

    /**
     * ACTION represents some action happening in our system.
     * ex: an employee being hired
     */
    const ACTION = 'action';

    /**
     * DATA_CHANGED is for logs that describe the creation or modification of
     * a model.
     * ex: changing a department's description
     */
    const DATA_CHANGED = 'data_changed';
}
