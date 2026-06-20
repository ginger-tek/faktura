<?php

namespace Faktura\Services;

use Ramsey\Uuid\Uuid;
use Faktura\Data\Db;

class OrgsDataService
{
  public static function create(array $data): object
  {
    $id = Uuid::uuid4()->toString();
    Db::run("insert into orgs (id, name, org_code, created_by)
    values (?, ?, ?, ?)", [
      $id,
      $data['name'],
      $data['org_code'],
      $data['user_id']
    ]);
    Db::run("insert into org_settings (org_id, setting_key, setting_value, created_by)
    values (:org_id, 'default_labor_rate', '60.00', :created_by),
    (:org_id, 'invoice_template', '{{ invoice.items }}', :created_by),
    (:org_id, 'contact_website', '', :created_by),
    (:org_id, 'contact_email', '', :created_by),
    (:org_id, 'contact_phone', '', :created_by),
    (:org_id, 'contact_address', '', :created_by)", [
      ':org_id' => $id,
      ':created_by' => $data['user_id']
    ]);
    Db::run("insert into roles (org_id, role_name, bit_value, created_by)
    values (:org_id, 'admin', :admin_bit_value, :created_by),
    (:org_id, 'reader', :reader_bit_value, :created_by),
    (:org_id, 'user', :user_bit_value, :created_by)", [
      ':org_id' => $id,
      ':admin_bit_value' => PermissionsService::getAllPermissionsBitValue(),
      ':reader_bit_value' => PermissionsService::getPermissionsByNameFilter('INVOICE_READ|CLIENT_READ|EXPENSE_READ', true),
      ':user_bit_value' => PermissionsService::getPermissionsByNameFilter('INVOICE_|CLIENT_|EXPENSE_', true),
      ':created_by' => $data['user_id']
    ]);
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
      updated_by = ?,
      updated_at = (unixepoch())
    where id = ?", [
      $org->display_name,
      $org->logo,
      $org->updated_by,
      $org->id
    ]);
    return self::getById($org->id);
  }
}