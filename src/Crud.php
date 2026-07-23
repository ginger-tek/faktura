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
    $sql = <<<SQL
    create table if not exists settings (
      id integer primary key,
      [key] text not null unique,
      [value] text not null,
      created_at integer,
      updated_at integer
    );
    create table if not exists users (
      id integer primary key,
      username text not null unique,
      passhash text not null,
      tokens text,
      is_active integer default 1,
      permissions_bit integer default 0,
      created_at integer,
      updated_at integer
    );
    create table if not exists clients (
      id integer primary key,
      [name] text not null unique,
      email text not null,
      phone text,
      [address] text,
      created_at integer,
      updated_at integer
    );
    create table if not exists invoices (
      id integer primary key,
      [number] text not null,
      client_id integer not null,
      summary text not null,
      details text,
      labor_amount real default 0,
      due_date text,
      paid_date text,
      paid_amount real default 0,
      created_at integer,
      updated_at integer
    );
    create table if not exists invoice_items (
      id integer primary key,
      invoice_id integer not null,
      summary text not null,
      quantity integer default 1,
      unit_price real not null,
      is_expense integer default 0,
      created_at integer,
      updated_at integer
    );
    insert or ignore into settings ([key], [value], created_at, updated_at) values
    ('logo', '', strftime('%s','now'), strftime('%s','now')),
    ('company', 'Company, LLC.', strftime('%s','now'), strftime('%s','now')),
    ('address', '123 Main St, City, Country', strftime('%s','now'), strftime('%s','now')),
    ('email', 'info@company.com', strftime('%s','now'), strftime('%s','now')),
    ('phone', '123-456-7890', strftime('%s','now'), strftime('%s','now')),
    ('website', 'www.company.com', strftime('%s','now'), strftime('%s','now')),
    ('invoice_template', '{{ invoice.summary }}', strftime('%s','now'), strftime('%s','now'));
    drop view if exists v_invoices;
    create view if not exists v_invoices as
    select
      i.*,
      case
        when i.due_date and not i.paid_date and date('now') > i.due_date then 'Overdue'
        when i.paid_date and i.due_date and i.paid_date > i.due_date then 'Paid Late'
        when i.paid_date and i.due_date and i.paid_date <= i.due_date then 'Paid'
        else 'Pending'
      end as status,
      c.[name] as client_name,
      i.labor_amount + coalesce(sum(ii.quantity * ii.unit_price), 0) as total_amount,
      coalesce(sum(ii.is_expense * ii.quantity * ii.unit_price), 0) as total_expenses
    from invoices i
    join clients c on i.client_id = c.id
    left join invoice_items ii on ii.invoice_id = i.id
    group by i.id;
    drop view if exists v_invoice_items;
    create view if not exists v_invoice_items as
    select
      ii.*,
      i.id as invoice_id,
      i.[number] as invoice_number,
      i.client_id,
      i.client_name as invoice_client_name,
      i.summary as invoice_summary,
      ii.quantity * ii.unit_price as total_amount
    from invoice_items ii
    join v_invoices i on ii.invoice_id = i.id;
    SQL;
    self::getInstance()->exec($sql);
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