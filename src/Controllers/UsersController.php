<?php

namespace Faktura\Controllers;

use GingerTek\Routy;
use Faktura\Services\UsersDataService;
use Faktura\Services\PermissionsService;

class UsersController
{
  public static function submitCreate(Routy $app)
  {
    $user = $app->getCtx('user');
    $data = $app->getBody();
    if (!isset($data->display_name, $data->username, $data->password))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $user = UsersDataService::create(
      $user->org_id,
      $data->display_name,
      $data->username,
      password_hash($data->password, PASSWORD_BCRYPT)
    );
    return $app->status(201)->sendJson($user);
  }

  public static function get(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $user = UsersDataService::getById($id, $user->org_id);
    if (!$user)
      return $app->status(404)->sendJson(['error' => 'User not found']);
    return $app->sendJson($user);
  }

  public static function list(Routy $app)
  {
    $user = $app->getCtx('user');
    $users = UsersDataService::list($user->org_id);
    return $app->sendJson($users);
  }

  public static function updatePassword(Routy $app)
  {
    $user = $app->getCtx('user');
    $body = $app->getBody();
    if (
      !$body || !isset($body->new_password, $body->confirm_new_password)
      || !is_string($body->new_password) || !is_string($body->confirm_new_password) || $body->new_password !== $body->confirm_new_password
    )
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $user = UsersDataService::getById($user->id, $user->org_id);
    if (!$user)
      return $app->status(404)->sendJson(['error' => 'User not found']);
    $user->passhash = password_hash($body->new_password, PASSWORD_BCRYPT);
    UsersDataService::update($user);
    return $app->sendJson(['message' => 'Password updated']);
  }

  public static function update(Routy $app)
  {
    $user = $app->getCtx('user');
    $body = $app->getBody();
    if (!$body || !isset($body->display_name, $body->role_id) || !is_string($body->display_name) || !is_string($body->role_id))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $user = UsersDataService::getById($user->id, $user->org_id);
    if (!$user)
      return $app->status(404)->sendJson(['error' => 'User not found']);
    $user->display_name = $body->display_name;
    $user->role_id = $body->role_id;
    $user = UsersDataService::update($user);
    return $app->sendJson($user);
  }

  public static function delete(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $user = UsersDataService::getById($id, $user->org_id);
    if (!$user)
      return $app->status(404)->sendJson(['error' => 'User not found']);
    UsersDataService::delete($user);
    return $app->sendJson(['message' => 'User deleted']);
  }
}