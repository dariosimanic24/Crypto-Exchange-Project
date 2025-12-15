<?php
require __DIR__ . '/../vendor/autoload.php';

// middleware
require __DIR__ . '/middleware/LoggingMiddleware.php';
require __DIR__ . '/middleware/RequestValidationMiddleware.php';
require __DIR__ . '/middleware/AuthMiddleware.php';

// services
require __DIR__ . '/rest/services/UsersService.php';
require __DIR__ . '/rest/services/CurrenciesService.php';
require __DIR__ . '/rest/services/WalletsService.php';
require __DIR__ . '/rest/services/OrdersService.php';
require __DIR__ . '/rest/services/TransactionsService.php';
require __DIR__ . '/rest/services/ExchangeService.php';


// register services / middleware
Flight::register('logging_middleware', 'LoggingMiddleware');
Flight::register('request_validation_middleware', 'RequestValidationMiddleware');
Flight::register('auth_middleware', 'AuthMiddleware');

Flight::register('usersService', 'UsersService');
Flight::register('currenciesService', 'CurrenciesService');
Flight::register('walletsService', 'WalletsService');
Flight::register('ordersService', 'OrdersService');
Flight::register('transactionsService', 'TransactionsService');
Flight::register('authService', 'JwtAuthService');
Flight::register('exchangeService', 'ExchangeService');

// global error handler
Flight::map('error', function (Throwable $e) {
    $code = $e->getCode();
    if ($code < 400 || $code > 599) {
        $code = 500;
    }

    Flight::json([
        'error' => $e->getMessage(),
        'type'  => get_class($e),
    ], $code);
});

// 404 handler
Flight::map('notFound', function () {
    Flight::json(['error' => 'Not found'], 404);
});

// routes
require __DIR__ . '/rest/routes/UsersRoutes.php';
require __DIR__ . '/rest/routes/CurrenciesRoutes.php';
require __DIR__ . '/rest/routes/WalletsRoutes.php';
require __DIR__ . '/rest/routes/OrdersRoutes.php';
require __DIR__ . '/rest/routes/TransactionsRoutes.php';
require __DIR__ . '/rest/routes/DocsRoutes.php';
require __DIR__ . '/rest/routes/AuthRoutes.php';
require __DIR__ . '/rest/routes/ExchangeRoutes.php';


// global middlewares (logging + JSON validation)
Flight::before('start', function () {
    Flight::logging_middleware()->before();
    Flight::request_validation_middleware()->before();
});

Flight::start();




