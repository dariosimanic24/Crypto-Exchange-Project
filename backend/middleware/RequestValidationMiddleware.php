<?php

class RequestValidationMiddleware
{
    public function before(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Only validate for POST, PUT, PATCH
        if (!in_array($method, ['POST', 'PUT', 'PATCH'])) {
            return;
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        // Only validate JSON requests
        if (stripos($contentType, 'application/json') === false) {
            return;
        }

        $raw = file_get_contents('php://input');

        if ($raw === '' || $raw === null) {
            return;
        }

        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Flight::halt(400, json_encode(['error' => 'Invalid JSON body']));
        }

        Flight::set('jsonBody', $data);
    }
}
