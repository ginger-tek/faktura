<?php

namespace Faktura\Controllers;

use GingerTek\Routy;
use Faktura\Services\UsersDataService;
use Faktura\Services\RolesDataService;

class UsersController
{
  public static function submitCreate(Routy $app)
  {
    $user = $app->getCtx('user');
    $data = $app->getBody();
    if (!isset($data->display_name, $data->username, $data->password))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $defaultRole = RolesDataService::getByName($user->org_id, 'user');
    if (!$defaultRole)
      return $app->status(500)->sendJson(['error' => 'Default role not found']);
    $userObj = UsersDataService::create([
      'org_id' => $user->org_id,
      'display_name' => $data->display_name,
      'username' => $data->username,
      'passhash' => password_hash($data->password, PASSWORD_BCRYPT),
      'role_id' => $defaultRole->id,
      'created_by' => $user->id
    ]);
    return $app->status(201)->sendJson($userObj);
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