<?php

namespace Faktura\Routes;

use GingerTek\Routy;
use Faktura\Middleware\AuthMiddleware;
use Faktura\Controllers\OrgSettingsController;
use Faktura\Services\PermissionsService;

class OrgSettingRoutes
{
  public static function index(Routy $app): void
  {
    $app->post('/', AuthMiddleware::authorize(PermissionsService::ORG_SETTINGS_UPDATE)(...), OrgSettingsController::create(...));
    $app->get('/', AuthMiddleware::authorize(PermissionsService::ORG_SETTINGS_READ_ALL)(...), OrgSettingsController::list(...));
    $app->get('/:key', AuthMiddleware::authorize(PermissionsService::ORG_SETTINGS_READ)(...), OrgSettingsController::get(...));
    $app->put('/:key', AuthMiddleware::authorize(PermissionsService::ORG_SETTINGS_UPDATE)(...), OrgSettingsController::update(...));
    $app->delete('/:key', AuthMiddleware::authorize(PermissionsService::ORG_SETTINGS_UPDATE)(...), OrgSettingsController::delete(...));
  }
}