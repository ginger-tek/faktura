<?php

namespace Faktura\Services;

use Ramsey\Uuid\Uuid;
use Faktura\Data\Crud;

class ClientsDataService
{
  public static function create(array $data): object
  {
    $id = Uuid::uuid4()->toString();
    Crud::create('clients', [
      'id' => $id,
      'org_id' => $data['org_id'],
      'full_name' => $data['full_name'],
      'contact_email' => $data['contact_email'],
      'created_by' => $data['created_by'],
      'created_at' => time()
    ]);
    return self::getById($id, $data['org_id']);
  }

  public static function getById(string $id, string $org_id): ?object
  {
    return Crud::read('v_clients', ['id' => $id, 'org_id' => $org_id]);
  }

  public static function isClientInvoiced(string $client_id, string $org_id): bool
  {
    $result = Crud::query("select count(*) as count from invoices
    where org_id = ? and client_id = ?", [$org_id, $client_id])->fetch();
    return $result->count > 0;
  }

  public static function list(string $org_id): array
  {
    return Crud::readAll('v_clients', ['org_id' => $org_id]);
  }

  public static function update(object $client): ?object
  {
    if (
      Crud::update('clients', [
        'full_name' => $client->full_name,
        'contact_email' => $client->contact_email,
        'contact_phone' => $client->contact_phone,
        'contact_address' => $client->contact_address,
        'updated_by' => $client->updated_by,
        'updated_at' => time()
      ], [
        'id' => $client->id,
        'org_id' => $client->org_id
      ])
    )
      return self::getById($client->id, $client->org_id);
    return null;
  }

  public static function delete(object $client): bool
  {
    return Crud::delete('clients', ['id' => $client->id, 'org_id' => $client->org_id]);
  }
}