<?php

namespace App\Http\Middleware;

use App\Services\SystemActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogApiAccessMiddleware
{
    public function __construct(
        private SystemActivityLogService $activityLogService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        try {
            $response = $next($request);

            $this->activityLogService->logAccess(
                $request,
                $response->getStatusCode(),
                (microtime(true) - $start) * 1000
            );

            return $response;
        } catch (Throwable $exception) {
            $this->activityLogService->logAccess(
                $request,
                500,
                (microtime(true) - $start) * 1000
            );

            throw $exception;
        }
    }
}
