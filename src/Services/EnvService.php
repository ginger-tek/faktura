<?php

namespace Faktura\Services;

class EnvService
{
  public static function load(): void
  {
    if (is_file(ROOT . '/.env'))
      foreach (parse_ini_file(ROOT . '/.env') as $key => $value)
        putenv("$key=$value");
    date_default_timezone_set(getenv('TIMEZONE') ?: 'America/New_York');
  }
}