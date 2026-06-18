<?php

namespace Faktura\Routes;

use GingerTek\Routy;
use Faktura\Middleware\AuthMiddleware;
use Faktura\Controllers\ClientsController;
use Faktura\Services\PermissionsService;

class ClientRoutes
{
  public static function index(Routy $app): void
  {
    $app->post('/', AuthMiddleware::authorize(PermissionsService::CLIENT_CREATE)(...), ClientsController::submitCreate(...));
    $app->get('/', AuthMiddleware::authorize(PermissionsService::CLIENT_READ_ALL)(...), ClientsController::list(...));
    $app->get('/:id', AuthMiddleware::authorize(PermissionsService::CLIENT_READ)(...), ClientsController::get(...));
    $app->put('/:id', AuthMiddleware::authorize(PermissionsService::CLIENT_UPDATE)(...), ClientsController::update(...));
    $app->delete('/:id', AuthMiddleware::authorize(PermissionsService::CLIENT_DELETE)(...), ClientsController::delete(...));
  }
}