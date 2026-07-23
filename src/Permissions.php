<?php

namespace App;

class Permissions
{
  public const int CREATE_INVOICE = 2;
  public const int LIST_INVOICES = 4;
  public const int VIEW_INVOICE = 8;
  public const int EDIT_INVOICE = 16;
  public const int DELETE_INVOICE = 32;
  public const int CREATE_CLIENT = 64;
  public const int LIST_CLIENTS = 128;
  public const int VIEW_CLIENT = 256;
  public const int EDIT_CLIENT = 512;
  public const int DELETE_CLIENT = 1024;
  public const int LIST_EXPENSES = 2048;
  public const int VIEW_SETTINGS = 4096;
  public const int EDIT_SETTINGS = 8192;
  public const int CREATE_USER = 16384;
  public const int VIEW_USERS = 32768;
  public const int EDIT_USERS = 65536;
  public const int DELETE_USER = 131072;

  public static function has(int $permissions, int $bit): bool
  {
    return ($permissions & $bit) === $bit;
  }

  /**
   * @return array<string>
   */
  public static function toNames(int $permissions): array
  {
    $names = [];
    foreach (self::list() as $name => $bit)
      if (self::has($permissions, $bit))
        $names[] = $name;
    return $names;
  }

  /**
   * @return array<string, int>
   */
  public static function list(): array
  {
    return (new \ReflectionClass(self::class))->getConstants();
  }

  public static function sum(?string $filter = '.*'): int
  {
    return array_reduce(array_values(array_filter(\App\Permissions::list(), fn($v, $k) => preg_match("/{$filter}/i", $k), ARRAY_FILTER_USE_BOTH)), fn($c, $b) => $c | $b, 0);
  }
}