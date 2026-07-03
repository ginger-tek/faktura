<?php

namespace Faktura\Services;

use Ramsey\Uuid\Uuid;
use Faktura\Data\Crud;

class RolesDataService
{
  public static function create(array $data): object
  {
    $id = Uuid::uuid4()->toString();
    Crud::create('roles', [
      'id' => $id,
      'org_id' => $data['org_id'],
      'role_name' => $data['role_name'],
      'bit_value' => $data['bit_value'] ?? 0,
      'created_by' => $data['created_by'] ?? null,
      'created_at' => time()
    ]);
    return self::getById($id, $data['org_id']);
  }

  public static function getById(string $id, string $org_id): ?object
  {
    return Crud::read('v_roles', ['id' => $id, 'org_id' => $org_id]);

  }

  public static function getByName(string $org_id, string $role_name): ?object
  {
    return Crud::read('v_roles', ['org_id' => $org_id, 'role_name' => $role_name]);
  }

  public static function list(string $org_id): array
  {
    return Crud::readAll('v_roles', ['org_id' => $org_id]);
  }

  public static function update(object $role): ?object
  {
    if (
      Crud::update('roles', [
        'role_name' => $role->role_name,
        'bit_value' => $role->bit_value,
        'updated_by' => $role->updated_by,
        'updated_at' => time()
      ], [
        'id' => $role->id,
        'org_id' => $role->org_id
      ])
    )
      return self::getById($role->id, $role->org_id);
    return null;
  }

  public static function delete(object $role): bool
  {
    return Crud::delete('roles', ['id' => $role->id, 'org_id' => $role->org_id]);
  }
}