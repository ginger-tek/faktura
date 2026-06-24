<?php

namespace Faktura\Services;

use Ramsey\Uuid\Uuid;
use Faktura\Data\Db;

class UsersDataService
{
  public static function create(array $data): object
  {
    $id = Uuid::uuid4()->toString();
    Db::run("insert into users (id, org_id, display_name, username, passhash, active, role_id, created_by, created_at)
    values (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
      $id,
      $data['org_id'],
      $data['display_name'],
      $data['username'],
      $data['passhash'],
      $data['active'] ?? 1,
      $data['role_id'],
      $data['created_by'] ?? null,
      time()
    ]);
    return self::getById($id, $data['org_id']);
  }

  public static function getById(string $id, string $org_id, ?bool $full = false): ?object
  {
    if ($full) {
      return Db::run("select * from v_users_full
      where id = ? and org_id = ?", [$id, $org_id])->fetch() ?: null;
    }
    return Db::run("select * from v_users
    where id = ? and org_id = ?", [$id, $org_id])->fetch() ?: null;
  }

  public static function findByUsername(string $username, string $org_id): ?object
  {
    return Db::run("select id, org_id, username, passhash from users
    where username = ? and org_id = ? and active = 1", [$username, $org_id])->fetch() ?: null;
  }

  public static function list(string $org_id): array
  {
    return Db::run("select * from v_users
    where org_id = ?", [$org_id])->fetchAll();
  }

  public static function update(object $user): object
  {
    Db::run("update users set
      username = ?,
      display_name = ?,
      active = ?,
      role_id = ?,
      updated_by = ?,
      updated_at = ?
    where id = ? and org_id = ?", [
      $user->username,
      $user->display_name,
      $user->active,
      $user->role_id,
      $user->updated_by,
      time(),
      $user->id,
      $user->org_id
    ]);
    return self::getById($user->id, $user->org_id);
  }

  public static function updatePassword(object $user): object
  {
    Db::run("update users set
      passhash = ?,
      updated_by = ?,
      updated_at = ?
    where id = ? and org_id = ?", [
      $user->passhash,
      $user->updated_by,
      time(),
      $user->id,
      $user->org_id
    ]);
    return self::getById($user->id, $user->org_id);
  }

  public static function delete(object $user): void
  {
    Db::run("delete from users
    where id = ? and org_id = ?", [$user->id, $user->org_id]);
  }
}
