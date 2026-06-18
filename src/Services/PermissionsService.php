<?php

namespace Faktura\Services;

class PermissionsService
{
  public const int INVOICE_READ_ALL = 2;
  public const int INVOICE_READ = 4;
  public const int INVOICE_CREATE = 8;
  public const int INVOICE_UPDATE = 16;
  public const int INVOICE_DELETE = 32;
  public const int CLIENT_READ_ALL = 64;
  public const int CLIENT_READ = 128;
  public const int CLIENT_CREATE = 256;
  public const int CLIENT_UPDATE = 512;
  public const int CLIENT_DELETE = 1024;
  public const int EXPENSE_READ_ALL = 2048;
  public const int EXPENSE_READ = 4096;
  public const int EXPENSE_CREATE = 8192;
  public const int EXPENSE_UPDATE = 16384;
  public const int EXPENSE_DELETE = 32768;
  public const int USER_READ_ALL = 65536;
  public const int USER_READ = 131072;
  public const int USER_CREATE = 262144;
  public const int USER_UPDATE = 524288;
  public const int USER_DELETE = 1048576;
  public const int ROLE_READ_ALL = 2097152;
  public const int ROLE_PERM_READ_ALL = 4194304;
  public const int ROLE_READ = 8388608;
  public const int ROLE_CREATE = 16777216;
  public const int ROLE_UPDATE = 33554432;
  public const int ROLE_DELETE = 67108864;
  public const int ORG_READ = 134217728;
  public const int ORG_UPDATE = 268435456;
  public const int ORG_SETTINGS_READ_ALL = 536870912;
  public const int ORG_SETTINGS_READ = 1073741824;
  public const int ORG_SETTINGS_UPDATE = 2147483648;
  protected const int ALL = 4294967295;

  public static function hasPermission(int $userRoles, ?int $requiredPermission = null): bool
  {
    if ($requiredPermission === null)
      return false;
    return ($userRoles & $requiredPermission) === $requiredPermission;
  }

  public static function listPermissions(): array
  {
    $reflection = new \ReflectionClass(self::class);
    return $reflection->getConstants(\ReflectionClassConstant::IS_PUBLIC);
  }
}