<?php

namespace Faktura\Services;

use Faktura\Data\Crud;

class OrgSettingsDataService
{
  public static function create(array $data): object
  {
    Crud::create('org_settings', [
      'org_id' => $data['org_id'],
      'setting_key' => $data['setting_key'],
      'setting_value' => $data['setting_value'],
      'created_by' => $data['created_by'] ?? null,
      'created_at' => time()
    ]);
    return self::getByKey($data['org_id'], $data['setting_key']);
  }

  public static function getByKey(string $org_id, string $setting_key): ?object
  {
    return Crud::read('v_org_settings', ['org_id' => $org_id, 'setting_key' => $setting_key]);
  }

  public static function getValueByKey(string $org_id, string $setting_key): ?string
  {
    $setting = self::getByKey($org_id, $setting_key);
    return $setting?->setting_value ?? null;
  }

  public static function list(string $org_id): array
  {
    return Crud::readAll('v_org_settings_list', ['org_id' => $org_id]);
  }

  public static function update(object $org_setting): ?object
  {
    if (
      Crud::update('org_settings', [
        'setting_value' => $org_setting->setting_value,
        'updated_by' => $org_setting->updated_by,
        'updated_at' => time()
      ], [
        'org_id' => $org_setting->org_id,
        'setting_key' => $org_setting->setting_key
      ])
    )
      return self::getByKey($org_setting->org_id, $org_setting->setting_key);
    return null;
  }

  public static function delete(object $org_setting): bool
  {
    return Crud::delete('org_settings', ['org_id' => $org_setting->org_id, 'setting_key' => $org_setting->setting_key]);
  }
}