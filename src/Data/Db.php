<?php

namespace Faktura\Data;

class Db
{
  private static ?\PDO $instance = null;

  public static function get()
  {
    if (!self::$instance) {
      $dsn = getenv('DB_DSN');
      if (preg_match('/^sqlite:(.*)$/', $dsn, $parts))
        $dsn = 'sqlite:' . ROOT . '/' . ltrim($parts[1], '/');
      self::$instance = new \PDO(
        $dsn,
        getenv('DB_USER') ?: null,
        getenv('DB_PASS') ?: null,
        [
          \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
          \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
        ]
      );
      self::$instance->exec("PRAGMA foreign_keys = ON");
      if (preg_match('/^sqlite/', $dsn))
        self::$instance->exec(file_get_contents(ROOT . '/schema.sql'));
    }
    return self::$instance;
  }

  public static function run(string $sql, array $params = []): \PDOStatement
  {
    $stmt = self::get()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
  }

  public static function lastInsertId(): string
  {
    return self::get()->lastInsertId();
  }
}