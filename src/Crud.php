<?php

namespace App;

class Crud
{
  private static \PDO $instance;

  protected static function getInstance(): \PDO
  {
    if (!isset(self::$instance)) {
      $connection = [
        getenv('DB_DSN') ?: throw new \InvalidArgumentException('DB_DSN is not set'),
        getenv('DB_USER') ?: null,
        getenv('DB_PASSWORD') ?: null,
        [
          \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
          \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
        ]
      ];
      self::$instance = new \PDO(...$connection);
    }
    return self::$instance;
  }

  public static function initSchema(): void
  {
    self::getInstance()->exec(file_get_contents(ROOT . '/schema.sql'));
    $default_settings = [
      'logo' => '',
      'company' => 'Company, LLC.',
      'address' => '123 Main St, City, Country',
      'email' => 'info@company.com',
      'phone' => '123-456-7890',
      'website' => 'www.company.com',
      'invoice_template' => '{{ invoice.summary }}'
    ];
    foreach ($default_settings as $k => $v)
      self::run('insert or ignore into settings (key, value, created_at) values (:key, :value, :created_at)', [
        'key' => $k,
        'value' => $v,
        'created_at' => time()
      ]);
  }

  private static function buildWhere(array $conditions): array
  {
    $clauses = [];
    $values = [];
    $join = 'AND';
    foreach ($conditions as $key => $val) {
      if ($key == 'join') {
        $join = strtoupper($val);
        continue;
      }
      $values[$key] = is_array($val) ? $val[1] : $val;
      $clauses[] = is_array($val) ? "$key {$val[0]} :$key" : "$key = :$key";
    }
    return [join(" $join ", $clauses), $values];
  }

  public static function run(string $sql, ?array $params = []): \PDOStatement
  {
    $stmt = self::getInstance()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
  }

  public static function create(string $table, array $data): ?object
  {
    $columns = join(', ', array_keys($data));
    $placeholders = join(', ', array_map(fn($key) => ":$key", array_keys($data)));
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    self::run($sql, $data);
    return self::read($table, ['id' => $data['id'] ?? self::getInstance()->lastInsertId()]);
  }

  public static function read(string $table, ?array $conditions = []): ?object
  {
    [$where, $values] = self::buildWhere($conditions);
    $sql = "SELECT * FROM $table" . ($where ? " WHERE $where" : '');
    return self::run($sql, $values)->fetch() ?: null;
  }

  public static function list(string $table, ?array $conditions = []): array
  {
    $orderBy = '';
    if (isset($conditions['order_by'])) {
      $orderBy = $conditions['order_by'];
      unset($conditions['order_by']);
    }
    [$where, $values] = self::buildWhere($conditions);
    $sql = "SELECT * FROM $table" . ($where ? " WHERE $where" : '') . ($orderBy ? " ORDER BY $orderBy DESC" : '');
    return self::run($sql, $values)->fetchAll();
  }

  public static function update(string $table, array $data, array $conditions): bool
  {
    $set = join(', ', array_map(fn($key) => "$key = :$key", array_keys($data)));
    [$where, $values] = self::buildWhere($conditions);
    $sql = "UPDATE $table SET $set" . ($where ? " WHERE $where" : '');
    return self::run($sql, [...$data, ...$values])->rowCount() == 1;
  }

  public static function delete(string $table, array $conditions): bool
  {
    [$where, $values] = self::buildWhere($conditions);
    $sql = "DELETE FROM $table" . ($where ? " WHERE $where" : '');
    return self::run($sql, $values)->rowCount() == 1;
  }
}