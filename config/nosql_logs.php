<?php

return [
    'enabled' => env('NOSQL_LOG_ENABLED', true),
    'dsn' => env('NOSQL_LOG_DSN', 'mongodb://127.0.0.1:27017'),
    'database' => env('NOSQL_LOG_DATABASE', 'taller_php_logs'),
    'collection' => env('NOSQL_LOG_COLLECTION', 'activity_logs'),
    'connect_timeout_ms' => env('NOSQL_LOG_CONNECT_TIMEOUT_MS', 2000),
    'server_selection_timeout_ms' => env('NOSQL_LOG_SERVER_SELECTION_TIMEOUT_MS', 2000),
];
