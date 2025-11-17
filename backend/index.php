<?php
require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/rest/services/UsersService.php';
require __DIR__ . '/rest/services/CurrenciesService.php';
require __DIR__ . '/rest/services/WalletsService.php';
require __DIR__ . '/rest/services/OrdersService.php';
require __DIR__ . '/rest/services/TransactionsService.php';

Flight::register('usersService', 'UsersService');
Flight::register('currenciesService', 'CurrenciesService');
Flight::register('walletsService', 'WalletsService');
Flight::register('ordersService', 'OrdersService');
Flight::register('transactionsService', 'TransactionsService');

require __DIR__ . '/rest/routes/UsersRoutes.php';
require __DIR__ . '/rest/routes/CurrenciesRoutes.php';
require __DIR__ . '/rest/routes/WalletsRoutes.php';
require __DIR__ . '/rest/routes/OrdersRoutes.php';
require __DIR__ . '/rest/routes/TransactionsRoutes.php';
require __DIR__ . '/rest/routes/DocsRoutes.php';
require __DIR__ . '/rest/routes/DocsRoutes.php';



Flight::start();
