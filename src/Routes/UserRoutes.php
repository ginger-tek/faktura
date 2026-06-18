<?php

namespace Faktura\Routes;

use GingerTek\Routy;
use Faktura\Middleware\AuthMiddleware;
use Faktura\Controllers\UsersController;
use Faktura\Services\PermissionsService;

class UserRoutes
{
  public static function index(Routy $app): void
  {
    $app->post('/', AuthMiddleware::authorize(PermissionsService::USER_CREATE)(...), UsersController::submitCreate(...));
    $app->get('/', AuthMiddleware::authorize(PermissionsService::USER_READ_ALL)(...), UsersController::list(...));
    $app->get('/:id', AuthMiddleware::authorize(PermissionsService::USER_READ)(...), UsersController::get(...));
    $app->put('/:id', AuthMiddleware::authorize(PermissionsService::USER_UPDATE)(...), UsersController::update(...));
    $app->delete('/:id', AuthMiddleware::authorize(PermissionsService::USER_DELETE)(...), UsersController::delete(...));
  }
}