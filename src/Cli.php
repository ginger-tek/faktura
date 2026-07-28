<?php

if (php_sapi_name() !== 'cli')
  exit('This script should be run from the command line.');

require __DIR__ . '/Bootstrap.php';

set_error_handler(function ($severity, $message, $file, $line) {
  throw new \ErrorException($message, 0, $severity, $file, $line);
});

try {
  (match ($argv[1]) {
    'setup-env' => function () use ($argv) {
        if (file_exists(ROOT . '/.env') || getenv('DB_DSN'))
          exit('Setup already completed');
        $env = file_get_contents(ROOT . '/example.env');
        $env = str_replace('JWT_SECRET=', 'JWT_SECRET=' . bin2hex(random_bytes(32)), $env);
        file_put_contents(ROOT . '/.env', $env);
        exit('Setup completed, .env created');
      },
    'init-schema' => function () {
        \App\Crud::initSchema();
        exit('Schema initialized');
      },
    'new-user' => function () use ($argv) {
        if (!isset($argv[2], $argv[3]))
          exit('Usage: php Cli.php new-user <username> <password> [<permissions_regex>]');
        exit(json_encode(\App\Crud::create('users', [
        'username' => $argv[2],
        'passhash' => password_hash($argv[3], PASSWORD_BCRYPT) ?? null,
        'permissions_bit' => \App\Permissions::sum($argv[4] ?? '(VIEW|LIST)_(INVOICE|CLIENT|EXPENSE)'),
        'created_at' => time()
        ]), JSON_PRETTY_PRINT));
      },
    'list-users' => function () {
        exit(json_encode(\App\Crud::list('users'), JSON_PRETTY_PRINT));
      },
    'list-permissions' => function () {
        exit(json_encode(\App\Permissions::list(), JSON_PRETTY_PRINT));
      },
    'filter-sum-permissions' => function () use ($argv) {
        if (!isset($argv[2]))
          exit('Usage: php Cli.php filter-sum-permissions <permissions_regex>');
        exit(json_encode(\App\Permissions::sum($argv[2]), JSON_PRETTY_PRINT));
      },
    default => function () use ($argv) {
        exit("Unknown command: {$argv[1]}");
      }
  })();
} catch (\Throwable $e) {
  exit('Error: ' . $e->getMessage());
}