<?php

namespace Faktura\Routes;

use GingerTek\Routy;
use Faktura\Controllers\AuthController;
use Faktura\Middleware\AuthMiddleware;

class AuthRoutes
{
  public static function index(Routy $app): void
  {
    $app->post('/login', AuthController::submitLogin(...));
    $app->post('/find-org', AuthController::findOrg(...));
    $app->post('/logout', AuthMiddleware::authenticate(...), AuthController::submitLogout(...));
    $app->get('/me', AuthMiddleware::authenticate(...), AuthController::getMe(...));
    $app->put('/me', AuthMiddleware::authenticate(...), AuthController::updateMe(...));
    $app->post('/me/password', AuthMiddleware::authenticate(...), AuthController::updateMyPassword(...));
  }
}