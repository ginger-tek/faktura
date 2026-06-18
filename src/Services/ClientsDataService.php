<?php

namespace Faktura\Services;

use Ramsey\Uuid\Uuid;
use Faktura\Data\Db;

class ClientsDataService
{
  public static function create(string $org_id, string $full_name, string $contact_email): object
  {
    $id = Uuid::uuid4()->toString();
    Db::run("insert into clients (id, org_id, full_name, contact_email)
    values (?, ?, ?, ?)", [$id, $org_id, $full_name, $contact_email]);
    return self::getById($id, $org_id);
  }

  public static function getById(string $id, string $org_id): ?object
  {
    return Db::run("select * from clients
    where id = ? and org_id = ?", [$id, $org_id])->fetch() ?: null;
  }

  public static function isClientInvoiced(string $client_id, string $org_id): bool
  {
    $result = Db::run("select count(*) as count from invoices
    where org_id = ? and client_id = ?", [$org_id, $client_id])->fetch();
    return $result->count > 0;
  }

  public static function list(string $org_id): array
  {
    return Db::run("select * from clients
    where org_id = ?", [$org_id])->fetchAll();
  }

  public static function update(object $client): ?object
  {
    Db::run("update clients set
      full_name = ?,
      contact_email = ?,
      contact_phone = ?,
      contact_address = ?
    where id = ? and org_id = ?", [
      $client->full_name,
      $client->contact_email,
      $client->contact_phone,
      $client->contact_address,
      $client->id,
      $client->org_id
    ]);
    return self::getById($client->id, $client->org_id);
  }

  public static function delete(object $client): void
  {
    Db::run("delete from clients
    where id = ? and org_id = ?", [$client->id, $client->org_id]);
  }
}