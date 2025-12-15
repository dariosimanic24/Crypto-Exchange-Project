<?php

class LoggingMiddleware
{
    private string $logFile;

    public function __construct()
    {
        // Log file in backend folder
        $this->logFile = __DIR__ . '/../server.log';
    }

    public function before(): void
    {
        $startTime = microtime(true);

        $time   = date('Y-m-d H:i:s');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $uri    = $_SERVER['REQUEST_URI'] ?? 'UNKNOWN';
        $ip     = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

        // user info (ako postoji)
        $user = Flight::get('user');
        $userInfo = $user ? 'user_id=' . $user['id'] : 'guest';

        // log AFTER request is processed
        Flight::after('start', function () use (
            $startTime,
            $time,
            $method,
            $uri,
            $ip,
            $userInfo
        ) {
            $status   = http_response_code();
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $line = sprintf(
                "[%s] %s \"%s %s\" status=%d %s %sms\n",
                $time,
                $ip,
                $method,
                $uri,
                $status,
                $userInfo,
                $duration
            );

            file_put_contents($this->logFile, $line, FILE_APPEND);
        });
    }
}
