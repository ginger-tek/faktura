<?php

namespace Faktura\Routes;

use GingerTek\Routy;
use Faktura\Middleware\AuthMiddleware;
use Faktura\Controllers\OrgController;
use Faktura\Services\PermissionsService;

class OrgRoutes
{
  public static function index(Routy $app): void
  {
    $app->get('/', AuthMiddleware::authorize(PermissionsService::ORG_READ)(...), OrgController::getCurrent(...));
    $app->put('/', AuthMiddleware::authorize(PermissionsService::ORG_UPDATE)(...), OrgController::updateCurrent(...));
  }
}