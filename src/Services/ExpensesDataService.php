<?php

namespace Faktura\Services;

use Faktura\Data\Db;
use Ramsey\Uuid\Uuid;

class ExpensesDataService
{
  public static function create(array $data): object
  {
    $id = Uuid::uuid4()->toString();
    $data['purchase_date'] ??= date('Y-m-d');
    Db::run("insert into expenses (id, org_id, summary, unit_price, quantity, purchase_date, created_by)
    values (?, ?, ?, ?, ?, ?, ?)", [
      $id,
      $data['org_id'],
      $data['summary'],
      $data['unit_price'],
      $data['quantity'],
      $data['purchase_date'],
      $data['created_by'],
    ]);
    return self::getById($id, $data['org_id']);
  }

  public static function getById(string $id, string $org_id): ?object
  {
    return Db::run("select * from v_expenses
    where id = ? and org_id = ?", [$id, $org_id])->fetch() ?: null;
  }

  public static function list(string $org_id): array
  {
    return Db::run("select * from v_expenses
    where org_id = ?", [$org_id])->fetchAll();
  }

  public static function listByInvoiceId(string $org_id, string $invoice_id): array
  {
    return Db::run("select * from v_invoice_itemizations
    where org_id = ? and invoice_id = ?", [$org_id, $invoice_id])->fetchAll();
  }

  public static function addToInvoice(string $org_id, string $invoice_id, string $expense_id): object
  {
    Db::run("insert into invoice_expenses (org_id, invoice_id, expense_id)
    values (?, ?, ?)", [$org_id, $invoice_id, $expense_id]);
    return Db::run("select * from v_invoice_itemizations
    where org_id = ? and invoice_id = ? and expense_id = ?", [$org_id, $invoice_id, $expense_id])->fetch();
  }

  public static function isExpenseInvoiced(string $expense_id, string $org_id): bool
  {
    $result = Db::run("select count(*) as count from invoice_expenses
    where org_id = ? and expense_id = ?", [$org_id, $expense_id])->fetch();
    return $result->count > 0;
  }

  public static function removeFromInvoice(string $org_id, string $invoice_id, string $expense_id): void
  {
    Db::run("delete from invoice_expenses
    where org_id = ? and invoice_id = ? and expense_id = ?", [$org_id, $invoice_id, $expense_id]);
  }

  public static function update(object $expense): ?object
  {
    Db::run("update expenses set
      summary = ?,
      quantity = ?,
      unit_price = ?,
      purchase_date = ?
    where id = ? and org_id = ?", [
      $expense->summary,
      $expense->quantity,
      $expense->unit_price,
      $expense->purchase_date,
      $expense->id,
      $expense->org_id
    ]);
    return self::getById($expense->id, $expense->org_id);
  }

  public static function delete(object $expense): void
  {
    Db::run("delete from expenses
    where id = ? and org_id = ?", [$expense->id, $expense->org_id]);
  }
}