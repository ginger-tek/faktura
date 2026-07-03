<?php

namespace Faktura\Data;

class Crud
{
  private static function buildClauses(array $conditions, string $join = ' AND '): array
  {
    $clauses = [];
    $params = [];
    foreach ($conditions as $column => $value) {
      if (is_array($value)) {
        if ($value[0] === 'between') {
          $params['start'] = $value[1][0];
          $params['end'] = $value[1][1];
          $clauses[] = "$column BETWEEN :start AND :end";
        } elseif ($value[0] === 'in') {
          foreach ($value[1] as $i => $v)
            $params["$column$i"] = $v;
          $clauses[] = "$column IN (" . join(", ", array_map(fn($i) => ":$column$i", array_keys($value[1]))) . ")";
        } elseif ($value[0] === 'or') {
          foreach ($value[1] as $i => $v)
            $params["$column$i"] = $v;
          $clauses[] = "(" . join(" OR ", array_map(fn($i) => "$column = :$column$i", array_keys($value[1]))) . ")";
        }
      } else {
        $clauses[] = "$column = :$column";
        $params[$column] = $value;
      }
    }
    return [join($join, $clauses), $params];
  }

  public static function create(string $table, array $data): string|int
  {
    $columns = join(", ", array_keys($data));
    $placeholders = ":" . join(", :", array_keys($data));
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    Database::run($sql, $data);
    return $data['id'] ?? Database::lastInsertId();
  }

  public static function read(string $table, array $conditions = []): ?object
  {
    $sql = "SELECT * FROM $table";
    if (!empty($conditions)) {
      [$clauses, $params] = self::buildClauses($conditions);
      $sql .= " WHERE $clauses";
    }
    return Database::run($sql, $params ?? $conditions)->fetch() ?: null;
  }

  public static function readAll(string $table, array $conditions = []): array
  {
    $sql = "SELECT * FROM $table";
    if (!empty($conditions)) {
      [$clauses, $params] = self::buildClauses($conditions);
      $sql .= " WHERE $clauses";
    }
    return Database::run($sql, $params ?? $conditions)->fetchAll();
  }

  public static function query(string $sql, array $params = []): \PDOStatement
  {
    return Database::run($sql, $params);
  }

  public static function update(string $table, array $data, array $conditions): bool
  {
    $sets = [];
    $clauses = [];
    foreach ($data as $column => $value)
      $sets[] = "$column = :$column";
    foreach ($conditions as $column => $value)
      $clauses[] = "$column = :cond_$column";
    $sql = "UPDATE $table SET " . join(", ", $sets) . " WHERE " . join(" AND ", $clauses);
    return Database::run($sql, [
      ...$data,
      ...array_combine(
        array_map(fn($col) => "cond_$col", array_keys($conditions)),
        $conditions
      )
    ])->rowCount() >= 1;
  }

  public static function delete(string $table, array $conditions): bool
  {
    $sql = "DELETE FROM $table";
    if (!empty($conditions)) {
      [$clauses, $params] = self::buildClauses($conditions);
      $sql .= " WHERE $clauses";
    }
    return Database::run($sql, $params ?? $conditions)->rowCount() >= 1;
  }
}