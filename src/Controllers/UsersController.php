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
    $defaultRole = RolesDataService::getByName($user->org_id, 'user');
    if (!$defaultRole)
      return $app->status(500)->sendJson(['error' => 'Default role not found']);    
    $data = $app->getBody();
    if (!isset($data->display_name, $data->username, $data->password))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
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
    $userObj = UsersDataService::getById($id, $user->org_id);
    if (!$userObj)
      return $app->status(404)->sendJson(['error' => 'User not found']);
    return $app->sendJson($userObj);
  }

  public static function list(Routy $app)
  {
    $user = $app->getCtx('user');
    $users = UsersDataService::list($user->org_id);
    return $app->sendJson($users);
  }

  public static function update(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $userObj = UsersDataService::getById($id, $user->org_id);
    if (!$userObj)
      return $app->status(404)->sendJson(['error' => 'User not found']);
    $data = $app->getBody();
    if (
      !$data || !isset($data->username, $data->display_name, $data->role_id)
      || !is_string($data->username) || !is_string($data->display_name) || !is_string($data->role_id)
    )
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $userObj->display_name = $data->display_name;
    $userObj->username = $data->username;
    $userObj->role_id = $data->role_id;
    $userObj->updated_by = $user->id;
    $userObj = UsersDataService::update($userObj);
    return $app->sendJson($userObj);
  }

  public static function delete(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $userObj = UsersDataService::getById($id, $user->org_id);
    if (!$userObj)
      return $app->status(404)->sendJson(['error' => 'User not found']);
    UsersDataService::delete($userObj);
    return $app->sendJson(['message' => 'User deleted']);
  }
}