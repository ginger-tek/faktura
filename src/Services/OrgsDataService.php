<?php

namespace Faktura\Services;

use Ramsey\Uuid\Uuid;
use Faktura\Data\Db;

class OrgsDataService
{
  public static function create(string $name, string $code): object
  {
    $id = Uuid::uuid4()->toString();
    Db::run("insert into orgs (id, name, org_code)
    values (?, ?, ?)", [$id, $name, $code]);
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
      updated_at = (unixepoch())
    where id = ?", [
      $org->display_name,
      $org->logo,
      $org->id
    ]);
    return self::getById($org->id);
  }
}