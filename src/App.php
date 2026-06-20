<?php

const ROOT = __DIR__ . '/..';
require_once ROOT . '/vendor/autoload.php';

if (is_file(ROOT . '/.env'))
  foreach (parse_ini_file(ROOT . '/.env') as $key => $value)
    putenv("$key=$value");

date_default_timezone_set(getenv('TIMEZONE') ?: 'America/New_York');

$app = new \GingerTek\Routy;

try {
  $app->group('/api', function () use ($app) {
    $app->group('/auth', \Faktura\Routes\AuthRoutes::index(...));

    $app->group('/', \Faktura\Middleware\AuthMiddleware::authenticate(...), function () use ($app) {
      $app->group('/invoices', \Faktura\Routes\InvoiceRoutes::index(...));
      $app->group('/expenses', \Faktura\Routes\ExpenseRoutes::index(...));
      $app->group('/clients', \Faktura\Routes\ClientRoutes::index(...));
      $app->group('/users', \Faktura\Routes\UserRoutes::index(...));
      $app->group('/roles', \Faktura\Routes\RoleRoutes::index(...));
      $app->group('/org', \Faktura\Routes\OrgRoutes::index(...));
      $app->group('/settings', \Faktura\Routes\OrgSettingRoutes::index(...));
    });

    $app->fallback(fn() => $app->sendJson(['error' => 'Route not found']));
  });
} catch (\Exception $ex) {
  error_log($ex->getMessage());
  $app->status(500)->sendJson(['error' => 'Internal server error']);
}