<?php

// POST /auth/login  → samo guest (nelogovan korisnik)
Flight::route('POST /auth/login', function () {
    // zabrani da logovan user ponovo ide na login
    Flight::auth_middleware()->requireGuest();

    // JSON tijelo (iz RequestValidationMiddleware) ili fallback na form-data
    $body = Flight::get('jsonBody');
    if ($body === null) {
        $body = Flight::request()->data->getData();
    }

    $email    = $body['email']    ?? '';
    $password = $body['password'] ?? '';

    try {
        // JwtAuthService je registriran kao "authService" u index.php
        $result = Flight::authService()->login($email, $password);
        // $result = [ 'token' => '...', 'user' => [ ... ] ]
        Flight::json($result);
    } catch (InvalidArgumentException $e) {
        Flight::json(['error' => $e->getMessage()], 400);
    } catch (Throwable $e) {
        Flight::json(['error' => $e->getMessage()], 500);
    }
});

// GET /auth/me  → samo logovan user
Flight::route('GET /auth/me', function () {
    // mora imati validan JWT token
    Flight::auth_middleware()->requireAuth();

    $user = Flight::get('user'); // postavljeno u AuthMiddleware::requireAuth
    Flight::json($user);
});
