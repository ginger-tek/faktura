<?php

namespace Faktura\Routes;

use GingerTek\Routy;
use Faktura\Middleware\AuthMiddleware;
use Faktura\Controllers\InvoicesController;
use Faktura\Services\PermissionsService;

class InvoiceRoutes
{
  public static function index(Routy $app): void
  {
    $app->post('/', AuthMiddleware::authorize(PermissionsService::INVOICE_CREATE)(...), InvoicesController::submitCreate(...));
    $app->get('/', AuthMiddleware::authorize(PermissionsService::INVOICE_READ_ALL)(...), InvoicesController::list(...));
    $app->get('/:id', AuthMiddleware::authorize(PermissionsService::INVOICE_READ)(...), InvoicesController::get(...));
    $app->post('/:id/clone', AuthMiddleware::authorize(PermissionsService::INVOICE_CREATE)(...), InvoicesController::submitClone(...));
    $app->get('/:id/items', AuthMiddleware::authorize(PermissionsService::INVOICE_READ)(...), InvoicesController::listItemizations(...));
    $app->post('/:id/add-expense', AuthMiddleware::authorize(PermissionsService::INVOICE_UPDATE)(...), InvoicesController::addExpense(...));
    $app->post('/:id/remove-expense', AuthMiddleware::authorize(PermissionsService::INVOICE_UPDATE)(...), InvoicesController::removeExpense(...));
    $app->put('/:id', AuthMiddleware::authorize(PermissionsService::INVOICE_UPDATE)(...), InvoicesController::update(...));
    $app->get('/:id/print', AuthMiddleware::authorize(PermissionsService::INVOICE_READ)(...), InvoicesController::renderPrint(...));
    $app->delete('/:id', AuthMiddleware::authorize(PermissionsService::INVOICE_DELETE)(...), InvoicesController::delete(...));
  }
}