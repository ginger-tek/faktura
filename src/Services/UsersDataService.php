<?php

namespace Faktura\Services;

use Ramsey\Uuid\Uuid;
use Faktura\Data\Crud;

class UsersDataService
{
  public static function create(array $data): object
  {
    $id = Uuid::uuid4()->toString();
    Crud::create('users', [
      'id' => $id,
      'org_id' => $data['org_id'],
      'display_name' => $data['display_name'],
      'username' => $data['username'],
      'passhash' => $data['passhash'],
      'active' => (int) ($data['active'] ?? 1),
      'role_id' => $data['role_id'],
      'created_by' => $data['created_by'] ?? null,
      'created_at' => time()
    ]);
    return self::getById($id, $data['org_id']);
  }

  public static function getById(string $id, string $org_id, ?bool $full = false): ?object
  {
    return Crud::read($full ? 'v_users_full' : 'v_users', ['id' => $id, 'org_id' => $org_id]);
  }

  public static function findByUsername(string $username, string $org_id): ?object
  {
    return Crud::read('users', ['username' => $username, 'org_id' => $org_id, 'active' => 1]);
  }

  public static function list(string $org_id): array
  {
    return Crud::readAll('v_users', ['org_id' => $org_id]);
  }

  public static function update(object $user): ?object
  {
    if (
      Crud::update('users', [
        'username' => $user->username,
        'passhash' => $user->passhash,
        'display_name' => $user->display_name,
        'active' => (int) $user->active,
        'role_id' => $user->role_id,
        'updated_by' => $user->updated_by,
        'updated_at' => time()
      ], [
        'id' => $user->id,
        'org_id' => $user->org_id
      ])
    )
      return self::getById($user->id, $user->org_id);
    return null;
  }

  public static function delete(object $user): bool
  {
    return Crud::delete('users', ['id' => $user->id, 'org_id' => $user->org_id]);
  }
}
