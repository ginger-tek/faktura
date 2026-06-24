<?php

namespace Faktura\Services;

use Faktura\Data\Db;

class OrgSettingsDataService
{
  public static function create(array $data): object
  {
    Db::run("insert into org_settings (org_id, setting_key, setting_value, created_by, created_at)
    values (?, ?, ?, ?, ?)", [
      $data['org_id'],
      $data['setting_key'],
      $data['setting_value'],
      $data['created_by'] ?? null,
      time()
    ]);
    return self::getByKey($data['org_id'], $data['setting_key']);
  }

  public static function getByKey(string $org_id, string $setting_key): ?object
  {
    return Db::run("select * from v_org_settings
    where org_id = ? and setting_key = ?", [$org_id, $setting_key])->fetch() ?: null;
  }

  public static function getValueByKey(string $org_id, string $setting_key): ?string
  {
    $setting = self::getByKey($org_id, $setting_key);
    return $setting?->setting_value ?? null;
  }

  public static function list(string $org_id): array
  {
    return Db::run("select * from v_org_settings_list
    where org_id = ?", [$org_id])->fetchAll();
  }

  public static function update(object $org_setting): ?object
  {
    Db::run("update org_settings set
      setting_value = ?,
      updated_by = ?,
      updated_at = ?
    where org_id = ? and setting_key = ?", [
      $org_setting->setting_value,
      $org_setting->updated_by,
      time(),
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