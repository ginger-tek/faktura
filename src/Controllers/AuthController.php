<?php

namespace Faktura\Controllers;

use GingerTek\Routy;
use Faktura\Services\OrgsDataService;
use Faktura\Services\TokenService;
use Faktura\Services\UsersDataService;

class AuthController
{
  public static function findOrg(Routy $app)
  {
    $body = $app->getBody();
    if (!$body || (!isset($body->org_code) && !isset($body->org_id)))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    if (isset($body->org_id) && !is_string($body->org_id))
      return $app->status(400)->sendJson(['error' => 'Invalid org_id']);
    if (isset($body->org_code) && !is_string($body->org_code))
      return $app->status(400)->sendJson(['error' => 'Invalid org_code']);
    $org = match (true) {
      isset($body->org_id) => OrgsDataService::getById($body->org_id),
      isset($body->org_code) => OrgsDataService::findByCode($body->org_code),
      default => null
    };
    if (!$org)
      return $app->status(404)->sendJson(['error' => 'Organization not found']);
    $app->sendJson(['id' => $org->id, 'display_name' => $org->display_name]);
  }

  public static function submitLogin(Routy $app)
  {
    $body = $app->getBody();
    if (!$body || !isset($body->org_id, $body->username, $body->password) || !is_string($body->org_id) || !is_string($body->username) || !is_string($body->password))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $result = UsersDataService::findByUsername($body->username, $body->org_id);
    if (!$result || !password_verify($body->password, $result->passhash))
      return $app->status(401)->sendJson(['error' => 'Invalid credentials']);
    $user_dto = UsersDataService::getById($result->id, $result->org_id);
    [$token, $exp] = TokenService::sign([
      'sub' => $user_dto->id,
      'preferred_username' => $user_dto->username,
      'name' => $user_dto->display_name,
      'org_id' => $user_dto->org_id,
      'org_display_name' => $user_dto->org_display_name
    ], $body->remember ? (30 * 24 * 60 * 60) : null);
    setcookie('token', $token, ['expires' => $exp, 'path' => '/', 'secure' => false, 'httponly' => true]);
    setcookie('token_exp', $exp, ['expires' => $exp, 'path' => '/', 'secure' => false, 'httponly' => false]);
    $app->sendJson(['token' => $token, 'exp' => $exp]);
  }

  public static function me(Routy $app)
  {
    $user = $app->getCtx('user');
    $app->sendJson([
      'id' => $user->id,
      'display_name' => $user->display_name,
      'username' => $user->username,
      'org_id' => $user->org_id,
      'org_display_name' => $user->org_display_name,
      'role' => $user->role_name,
      'created_at' => $user->created_at,
      'updated_at' => $user->updated_at
    ]);
  }

  public static function submitLogout(Routy $app)
  {
    setcookie('token', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => false, 'httponly' => true]);
    setcookie('token_exp', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => false, 'httponly' => false]);
    $app->sendJson(['message' => 'Logged out']);
  }
}