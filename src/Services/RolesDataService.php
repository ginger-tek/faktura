<?php

namespace Faktura\Services;

use Faktura\Data\Db;
use Ramsey\Uuid\Uuid;

class RolesDataService
{
  public static function create(string $org_id, string $role_name): object
  {
    $id = Uuid::uuid4()->toString();
    Db::run("insert into roles (id, org_id, role_name)
    values (?, ?, ?)", [
      $id,
      $org_id,
      $role_name
    ]);
    return self::getById($id, $org_id);
  }

  public static function getById(string $id, string $org_id): ?object
  {
    return Db::run("select * from roles
    where id = ? and org_id = ?", [$id, $org_id])->fetch() ?: null;
  }

  public static function list(string $org_id): array
  {
    return Db::run("select * from roles
    where org_id = ?", [$org_id])->fetchAll();
  }

  public static function update(object $role): ?object
  {
    Db::run("update roles set
      role_name = ?,
      bit_value = ?
    where id = ? and org_id = ?", [
      $role->role_name,
      $role->bit_value,
      $role->id,
      $role->org_id
    ]);
    return self::getById($role->id, $role->org_id);
  }

  public static function delete(object $role): void
  {
    Db::run("delete from roles
    where id = ? and org_id = ?", [$role->id, $role->org_id]);
  }

  public static function hasRole(int $userRoleBit, int $requiredRoleBit): bool
  {
    return ($userRoleBit & $requiredRoleBit) === $requiredRoleBit;
  }
}