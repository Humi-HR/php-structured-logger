<?php

namespace Humi\StructuredLogger\Laravel;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ResponseStasher stashes the Response in the service container for use
 * after the Request has been returned to the client.
 */
class ResponseStasher
{
    /**
     * RESPONSE_KEY is the key under which we stash the Response object
     */
    const RESPONSE_KEY = '_response';

    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response)
    {
        app()->instance(self::RESPONSE_KEY, $response);
    }
}
