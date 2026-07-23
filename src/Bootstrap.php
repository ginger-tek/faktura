<?php

const ROOT = __DIR__ . '/..';

require ROOT . '/vendor/autoload.php';

date_default_timezone_set(getenv('TIMEZONE') ?: 'America/New_York');

if (file_exists(ROOT . '/.env'))
  foreach (parse_ini_file(ROOT . '/.env') as $key => $value)
    putenv("$key=$value");