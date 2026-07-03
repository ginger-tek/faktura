<?php

namespace Faktura\Data;

class Database
{
  private static ?\PDO $instance = null;

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new \PDO(
        getenv('DB_DSN'),
        getenv('DB_USER') ?: null,
        getenv('DB_PASS') ?: null,
        [
          \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
          \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
        ]
      );
    }
    return self::$instance;
  }

  public static function run(string $sql, array $params = []): \PDOStatement
  {
    $stmt = self::getInstance()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
  }

  public static function lastInsertId(): string
  {
    return self::getInstance()->lastInsertId();
  }
}