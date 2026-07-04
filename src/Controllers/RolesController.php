<?php

namespace Faktura\Controllers;

use GingerTek\Routy;
use Faktura\Services\RolesDataService;
use Faktura\Services\PermissionsService;

class RolesController
{
  public static function submitCreate(Routy $app)
  {
    $user = $app->getCtx('user');
    $data = $app->getBody();
    if (!isset($data->role_name) || !is_string($data->role_name) || preg_match('/[^a-zA-Z_]/', $data->role_name))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $role = RolesDataService::create([
      'org_id' => $user->org_id,
      'role_name' => $data->role_name,
      'created_by' => $user->id
    ]);
    return $app->status(201)->sendJson($role);
  }

  public static function get(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $role = RolesDataService::getById($id, $user->org_id);
    if (!$role)
      return $app->status(404)->sendJson(['error' => 'Role not found']);
    return $app->sendJson($role);
  }

  public static function list(Routy $app)
  {
    $user = $app->getCtx('user');
    $roles = RolesDataService::list($user->org_id);
    return $app->sendJson($roles);
  }

  public static function listPermissions(Routy $app)
  {
    $permissions = PermissionsService::listPermissions();
    return $app->sendJson($permissions);
  }

  public static function update(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $data = $app->getBody();
    if (!isset($data->role_name, $data->bit_value) || !is_string($data->role_name) || preg_match('/[^a-zA-Z_]/', $data->role_name) || !is_int($data->bit_value))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $role = RolesDataService::getById($id, $user->org_id);
    if (!$role)
      return $app->status(404)->sendJson(['error' => 'Role not found']);
    $role->role_name = $data->role_name;
    $role->bit_value = $data->bit_value;
    $role->updated_by = $user->id;
    $role = RolesDataService::update($role);
    return $app->sendJson($role);
  }

  public static function delete(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $role = RolesDataService::getById($id, $user->org_id);
    if (!$role)
      return $app->status(404)->sendJson(['error' => 'Role not found']);
    RolesDataService::delete($role);
    return $app->sendJson(['message' => 'Role deleted']);
  }
}