<?php

namespace Faktura\Controllers;

use GingerTek\Routy;
use Faktura\Services\OrgSettingsDataService;

class OrgSettingsController
{
  public static function create(Routy $app)
  {
    $user = $app->getCtx('user');
    if (!PermissionsService::hasPermission($user->role_bit_value, PermissionsService::ORG_SETTINGS_UPDATE))
      return $app->status(403)->sendJson(['error' => 'Forbidden']);
    $data = $app->getBody();
    if (!isset($data->setting_key, $data->setting_value) || !is_string($data->setting_key) || !is_string($data->setting_value))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $setting = OrgSettingsDataService::create(
      $user->org_id,
      $data->setting_key,
      $data->setting_value
    );
    return $app->status(201)->sendJson($setting);
  }

  public static function list(Routy $app)
  {
    $user = $app->getCtx('user');
    $settings = OrgSettingsDataService::list($user->org_id);
    return $app->sendJson($settings);
  }

  public static function get(Routy $app)
  {
    $user = $app->getCtx('user');
    $setting_key = $app->getParam('key');
    $setting = OrgSettingsDataService::getByKey($user->org_id, $setting_key);
    if (!$setting)
      return $app->status(404)->sendJson(['error' => 'Setting not found']);
    return $app->sendJson($setting);
  }

  public static function update(Routy $app)
  {
    $user = $app->getCtx('user');
    $setting_key = $app->getParam('key');
    $body = $app->getBody();
    if (!$body || !isset($body->setting_value) || !is_string($body->setting_value))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $setting = OrgSettingsDataService::getByKey($user->org_id, $setting_key);
    if (!$setting)
      return $app->status(404)->sendJson(['error' => 'Setting not found']);
    $setting->setting_value = $body->setting_value;
    $setting = OrgSettingsDataService::update($setting);
    return $app->sendJson($setting);
  }

  public static function delete(Routy $app)
  {
    $user = $app->getCtx('user');
    $setting_key = $app->getParam('key');
    $setting = OrgSettingsDataService::getByKey($user->org_id, $setting_key);
    if (!$setting)
      return $app->status(404)->sendJson(['error' => 'Setting not found']);
    OrgSettingsDataService::delete($setting);
    return $app->sendJson(['message' => 'Setting deleted successfully']);
  }
}