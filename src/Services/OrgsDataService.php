<?php

namespace Faktura\Services;

use Ramsey\Uuid\Uuid;
use Faktura\Data\Db;

class OrgsDataService
{
  public static function create(array $data): object
  {
    $id = Uuid::uuid4()->toString();
    Db::run("insert into orgs (id, display_name, org_code, created_at)
    values (?, ?, ?, ?)", [
      $id,
      $data['display_name'],
      $data['org_code'],
      time()
    ]);
    OrgSettingsDataService::create(['org_id' => $id, 'setting_key' => 'default_labor_rate', 'setting_value' => '60.00']);
    OrgSettingsDataService::create(['org_id' => $id, 'setting_key' => 'invoice_template', 'setting_value' => '{{ invoice.items }}']);
    OrgSettingsDataService::create(['org_id' => $id, 'setting_key' => 'contact_website', 'setting_value' => '']);
    OrgSettingsDataService::create(['org_id' => $id, 'setting_key' => 'contact_email', 'setting_value' => '']);
    OrgSettingsDataService::create(['org_id' => $id, 'setting_key' => 'contact_phone', 'setting_value' => '']);
    OrgSettingsDataService::create(['org_id' => $id, 'setting_key' => 'contact_address', 'setting_value' => '']);
    RolesDataService::create(['org_id' => $id, 'role_name' => 'admin', 'bit_value' => PermissionsService::getAllPermissionsBitValue()]);
    RolesDataService::create(['org_id' => $id, 'role_name' => 'reader', 'bit_value' => PermissionsService::getPermissionsByNameFilter('INVOICE_READ|CLIENT_READ|EXPENSE_READ', true)]);
    RolesDataService::create(['org_id' => $id, 'role_name' => 'user', 'bit_value' => PermissionsService::getPermissionsByNameFilter('INVOICE_|CLIENT_|EXPENSE_', true)]);
    return self::getById($id);
  }

  public static function findByCode(string $code): ?object
  {
    return Db::run("select * from orgs
    where org_code = ?", [$code])->fetch() ?: null;
  }

  public static function getById(string $id): ?object
  {
    return Db::run("select * from orgs
    where id = ?", [$id])->fetch() ?: null;
  }

  public static function update(object $org): ?object
  {
    Db::run("update orgs set
      display_name = ?,
      logo = ?,
      updated_at = ?
    where id = ?", [
      $org->display_name,
      $org->logo,
      time(),
      $org->id
    ]);
    return self::getById($org->id);
  }

  public static function delete(string $id, string $org_code): void
  {
    Db::run("delete from orgs where id = ? and org_code = ?", [$id, $org_code]);
  }
}