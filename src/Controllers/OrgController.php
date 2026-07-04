<?php

namespace Faktura\Controllers;

use GingerTek\Routy;
use Faktura\Services\OrgsDataService;

class OrgController
{
  public static function getCurrent(Routy $app)
  {
    $user = $app->getCtx('user');
    $org = OrgsDataService::getById($user->org_id);
    if (!$org)
      return $app->status(404)->sendJson(['error' => 'Organization not found']);
    return $app->sendJson($org);
  }

  public static function updateCurrent(Routy $app)
  {
    $user = $app->getCtx('user');
    $org = OrgsDataService::getById($user->org_id);
    if (!$org)
      return $app->status(404)->sendJson(['error' => 'Organization not found']);
    $data = $app->getBody();
    if (!$data || !isset($data->display_name, $data->org_code) || !is_string($data->display_name) || !is_string($data->org_code))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $org->display_name = $data->display_name;
    $org->org_code = $data->org_code;
    $org->logo = $data->logo ?? $org->logo;
    $org->updated_by = $user->id;
    $org = OrgsDataService::update($org);
    return $app->sendJson($org);
  }
}