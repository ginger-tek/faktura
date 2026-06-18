<?php

namespace Faktura\Routes;

use GingerTek\Routy;
use Faktura\Middleware\AuthMiddleware;
use Faktura\Controllers\ExpensesController;
use Faktura\Services\PermissionsService;

class ExpenseRoutes
{
  public static function index(Routy $app): void
  {
    $app->post('/', AuthMiddleware::authorize(PermissionsService::EXPENSE_CREATE)(...), ExpensesController::submitCreate(...));
    $app->get('/', AuthMiddleware::authorize(PermissionsService::EXPENSE_READ_ALL)(...), ExpensesController::list(...));
    $app->get('/:id', AuthMiddleware::authorize(PermissionsService::EXPENSE_READ)(...), ExpensesController::get(...));
    $app->put('/:id', AuthMiddleware::authorize(PermissionsService::EXPENSE_UPDATE)(...), ExpensesController::update(...));
    $app->delete('/:id', AuthMiddleware::authorize(PermissionsService::EXPENSE_DELETE)(...), ExpensesController::delete(...));
  }
}