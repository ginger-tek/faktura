<?php

namespace Faktura\Routes;

use GingerTek\Routy;
use Faktura\Middleware\AuthMiddleware;
use Faktura\Controllers\RolesController;
use Faktura\Services\PermissionsService;

class RoleRoutes
{
  public static function index(Routy $app): void
  {
    $app->post('/', AuthMiddleware::authorize(PermissionsService::ROLE_CREATE)(...), RolesController::submitCreate(...));
    $app->get('/', AuthMiddleware::authorize(PermissionsService::ROLE_READ_ALL)(...), RolesController::list(...));
    $app->get('/permissions', AuthMiddleware::authorize(PermissionsService::ROLE_PERM_READ_ALL)(...), RolesController::listPermissions(...));
    $app->get('/:id', AuthMiddleware::authorize(PermissionsService::ROLE_READ)(...), RolesController::get(...));
    $app->put('/:id', AuthMiddleware::authorize(PermissionsService::ROLE_UPDATE)(...), RolesController::update(...));
    $app->delete('/:id', AuthMiddleware::authorize(PermissionsService::ROLE_DELETE)(...), RolesController::delete(...));
  }
}