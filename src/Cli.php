<?php

const ROOT = __DIR__ . '/..';
require_once ROOT . '/vendor/autoload.php';

\Faktura\Services\EnvService::load();

if (php_sapi_name() === 'cli') {
  try {
    if (isset($argv[1]) && $argv[1] === 'create-org') {
      $org = \Faktura\Services\OrgsDataService::create([
        'display_name' => $argv[2] ?? 'New Org',
        'org_code' => $argv[3] ?? 'test_org'
      ]);
      echo "Created org:\n";
      print_r($org);
    } elseif (isset($argv[1]) && $argv[1] === 'create-admin-user') {
      $orgId = $argv[2] ?? throw new \Exception('Org ID is required');
      $adminRole = \Faktura\Services\RolesDataService::getByName($orgId, 'admin');
      $user = \Faktura\Services\UsersDataService::create([
        'org_id' => $orgId,
        'display_name' => $argv[3] ?? throw new \Exception('Display name is required'),
        'username' => $argv[4] ?? throw new \Exception('Username is required'),
        'passhash' => password_hash($argv[5] ?? throw new \Exception('Password is required'), PASSWORD_DEFAULT),
        'role_id' => $adminRole->id
      ]);
      echo "Created user:\n";
      print_r($user);
    } elseif (isset($argv[1]) && $argv[1] === 'delete-org') {
      $orgId = $argv[2] ?? throw new \Exception('Org ID is required');
      $orgCode = $argv[3] ?? throw new \Exception('Org code is required');
      $org = \Faktura\Services\OrgsDataService::getById($orgId);
      if (!$org || $org->org_code !== $orgCode)
        throw new \Exception('Org not found or org code does not match');
      \Faktura\Services\OrgsDataService::delete($orgId, $orgCode);
      echo "Deleted org with ID: $orgId\n";
    } else {
      echo "Usage:
      php src/Cli.php [command] [arguments]
      php src/Cli.php create-org [display_name] [org_code]
      php src/Cli.php create-admin-user [org_id] [display_name] [username] [password]
      php src/Cli.php delete-org [org_id] [org_code]
      ";
    }
  } catch (\Exception $ex) {
    echo "Error: " . $ex->getMessage() . "\n";
  }
} else {
  echo "This script is intended to be run from the command line.\n";
}