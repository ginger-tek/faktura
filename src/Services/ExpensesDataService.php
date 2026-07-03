<?php

namespace Faktura\Services;

use Ramsey\Uuid\Uuid;
use Faktura\Data\Crud;

class ExpensesDataService
{
  public static function create(array $data): object
  {
    $id = Uuid::uuid4()->toString();
    $data['purchase_date'] ??= date('Y-m-d');
    Crud::create('expenses', [
      'id' => $id,
      'org_id' => $data['org_id'],
      'summary' => $data['summary'],
      'unit_price' => (double) $data['unit_price'],
      'quantity' => (int) $data['quantity'],
      'purchase_date' => $data['purchase_date'],
      'created_by' => $data['created_by'],
      'created_at' => time()
    ]);
    return self::getById($id, $data['org_id']);
  }

  public static function getById(string $id, string $org_id): ?object
  {
    return Crud::read('v_expenses', ['id' => $id, 'org_id' => $org_id]);
  }

  public static function list(string $org_id): array
  {
    return Crud::readAll('v_expenses', ['org_id' => $org_id]);
  }

  public static function listByInvoiceId(string $org_id, string $invoice_id): array
  {
    return Crud::readAll('v_invoice_itemizations', ['org_id' => $org_id, 'invoice_id' => $invoice_id]);
  }

  public static function addToInvoice(string $org_id, string $invoice_id, string $expense_id): object
  {
    Crud::create('invoice_expenses', [
      'org_id' => $org_id,
      'invoice_id' => $invoice_id,
      'expense_id' => $expense_id
    ]);
    return Crud::read('v_invoice_itemizations', ['org_id' => $org_id, 'invoice_id' => $invoice_id, 'expense_id' => $expense_id]);
  }

  public static function isExpenseInvoiced(string $expense_id, string $org_id): bool
  {
    $result = Crud::query("select count(*) as count from invoice_expenses
    where org_id = ? and expense_id = ?", [$org_id, $expense_id])->fetch();
    return $result->count > 0;
  }

  public static function removeFromInvoice(string $org_id, string $invoice_id, string $expense_id): void
  {
    Crud::delete('invoice_expenses', ['org_id' => $org_id, 'invoice_id' => $invoice_id, 'expense_id' => $expense_id]);
  }

  public static function update(object $expense): ?object
  {
    if (
      Crud::update('expenses', [
        'summary' => $expense->summary,
        'quantity' => (int) $expense->quantity,
        'unit_price' => (double) $expense->unit_price,
        'purchase_date' => $expense->purchase_date,
        'updated_by' => $expense->updated_by,
        'updated_at' => time()
      ], [
        'id' => $expense->id,
        'org_id' => $expense->org_id
      ])
    )
      return self::getById($expense->id, $expense->org_id);
    return null;
  }

  public static function delete(object $expense): bool
  {
    return Crud::delete('expenses', ['id' => $expense->id, 'org_id' => $expense->org_id]);
  }
}