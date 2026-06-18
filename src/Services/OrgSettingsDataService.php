<?php

namespace Faktura\Services;

use Ramsey\Uuid\Uuid;
use Faktura\Data\Db;

class OrgSettingsDataService
{
  public static function create(string $org_id, string $setting_key, string $setting_value): object
  {
    Db::run("insert into org_settings (org_id, setting_key, setting_value)
    values (?, ?, ?)", [$org_id, $setting_key, $setting_value]);
    return self::getByKey($org_id, $setting_key);
  }

  public static function getByKey(string $org_id, string $setting_key): ?object
  {
    return Db::run("select * from org_settings
    where org_id = ? and setting_key = ?", [$org_id, $setting_key])->fetch() ?: null;
  }

  public static function getValueByKey(string $org_id, string $setting_key): ?string
  {
    $setting = self::getByKey($org_id, $setting_key);
    return $setting?->setting_value ?? null;
  }

  public static function list(string $org_id): array
  {
    return Db::run("select * from v_org_settings
    where org_id = ?", [$org_id])->fetchAll();
  }

  public static function update(object $org_setting): ?object
  {
    Db::run("update org_settings set
      setting_value = ?
    where org_id = ? and setting_key = ?", [
      $org_setting->setting_value,
      $org_setting->org_id,
      $org_setting->setting_key
    ]);
    return self::getByKey($org_setting->org_id, $org_setting->setting_key);
  }

  public static function delete(object $org_setting): void
  {
    Db::run("delete from org_settings
    where org_id = ? and setting_key = ?", [$org_setting->org_id, $org_setting->setting_key]);
  }
}