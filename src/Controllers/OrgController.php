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
    $body = $app->getBody();
    if (!$body || !isset($body->display_name, $body->org_code) || !is_string($body->display_name) || !is_string($body->org_code))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $org = OrgsDataService::getById($user->org_id);
    if (!$org)
      return $app->status(404)->sendJson(['error' => 'Organization not found']);
    $org->display_name = $body->display_name;
    $org->org_code = $body->org_code;
    $org->logo = $body->logo ?? $org->logo;
    $org->updated_by = $user->id;
    $org = OrgsDataService::update($org);
    return $app->sendJson($org);
  }
}