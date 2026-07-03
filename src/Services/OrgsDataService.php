<?php

namespace Faktura\Services;

use Ramsey\Uuid\Uuid;
use Faktura\Data\Crud;

class OrgsDataService
{
  public static function create(array $data): object
  {
    $id = Uuid::uuid4()->toString();
    Crud::create('orgs', [
      'id' => $id,
      'display_name' => $data['display_name'],
      'org_code' => $data['org_code'],
      'created_at' => time()
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
    return Crud::read('orgs', ['org_code' => $code]);
  }

  public static function getById(string $id): ?object
  {
    return Crud::read('orgs', ['id' => $id]);
  }

  public static function update(object $org): ?object
  {
    if (
      Crud::update('orgs', [
        'display_name' => $org->display_name,
        'logo' => $org->logo,
        'updated_at' => time()
      ], [
        'id' => $org->id
      ])
    )
      return self::getById($org->id);
    return null;
  }

  public static function delete(string $id, string $org_code): bool
  {
    return Crud::delete('orgs', ['id' => $id, 'org_code' => $org_code]);
  }
}