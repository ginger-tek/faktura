<?php

namespace Faktura\Middleware;

use GingerTek\Routy;
use Faktura\Services\TokenService;
use Faktura\Services\UsersDataService;

class AuthMiddleware
{
  public static function getToken(Routy $app): ?string
  {
    return $_COOKIE['token'] ?? $app->getHeader('authorization') ?: null;
  }

  public static function authenticate(Routy $app)
  {
    $token = self::getToken($app);
    if (!$token)
      return $app->status(401)->sendJson(['error' => 'Unauthorized']);
    $data = TokenService::verify($token);
    if ($data === false)
      return $app->status(401)->sendJson(['error' => 'Token expired']);
    if ($data === null)
      return $app->status(401)->sendJson(['error' => 'Unauthorized']);
    $user = UsersDataService::getById($data->sub, $data->org_id);
    if (!$user)
      return $app->status(401)->sendJson(['error' => 'Unauthorized']);
    $app->setCtx('user', $user);
  }

  public static function authorize(int $permission_bit)
  {
    return function (Routy $app) use ($permission_bit) {
      $user = $app->getCtx('user');
      if (!$user || ($permission_bit & $user->role_bit_value) === 0)
        return $app->status(403)->sendJson(['error' => 'Forbidden']);
    };
  }
}