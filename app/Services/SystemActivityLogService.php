<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use Throwable;

class SystemActivityLogService
{
    private ?Client $client = null;
    private bool $warned = false;

    public function __construct()
    {
        $enabled = (bool) config('nosql_logs.enabled', false);
        $dsn = (string) config('nosql_logs.dsn', '');

        if (!$enabled || $dsn === '' || !class_exists(Client::class)) {
            return;
        }

        try {
            $options = [
                'connectTimeoutMS' => (int) config('nosql_logs.connect_timeout_ms', 2000),
                'serverSelectionTimeoutMS' => (int) config('nosql_logs.server_selection_timeout_ms', 2000),
            ];

            $this->client = new Client($dsn, [], $options);
        } catch (Throwable $exception) {
            $this->notifyFallback($exception);
        }
    }

    public function logAccess(Request $request, int $statusCode, float $durationMs): void
    {
        $this->write([
            'type' => 'access',
            'level' => $statusCode >= 500 ? 'error' : 'info',
            'message' => 'Acceso API',
            'path' => $request->path(),
            'method' => $request->method(),
            'status_code' => $statusCode,
            'duration_ms' => round($durationMs, 2),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => optional($request->user())->id,
            'trace_id' => $request->header('X-Request-Id'),
            'context' => [
                'query' => $request->query(),
            ],
        ]);
    }

    public function logDomainEvent(string $action, array $context = []): void
    {
        $this->write([
            'type' => 'action',
            'level' => 'info',
            'message' => 'Accion relevante del dominio',
            'action' => $action,
            'context' => $context,
        ]);
    }

    public function logError(Throwable $exception, array $context = []): void
    {
        $this->write([
            'type' => 'error',
            'level' => 'error',
            'message' => $exception->getMessage(),
            'action' => 'exception.reported',
            'context' => $context,
            'exception' => [
                'class' => $exception::class,
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ],
        ]);
    }

    private function write(array $document): void
    {
        if ($this->client === null) {
            return;
        }

        try {
            $this->client
                ->selectDatabase((string) config('nosql_logs.database', 'taller_php_logs'))
                ->selectCollection((string) config('nosql_logs.collection', 'activity_logs'))
                ->insertOne(array_merge($document, [
                    'created_at' => new UTCDateTime(),
                    'environment' => config('app.env'),
                    'app_name' => config('app.name'),
                ]));
        } catch (Throwable $exception) {
            $this->notifyFallback($exception);
        }
    }

    private function notifyFallback(Throwable $exception): void
    {
        if ($this->warned) {
            return;
        }

        $this->warned = true;

        Log::warning('NoSQL logs no disponibles. Se mantiene logging principal relacional/archivo.', [
            'exception' => $exception->getMessage(),
        ]);
    }
}
