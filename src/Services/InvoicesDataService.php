<?php

namespace Faktura\Services;

use Faktura\Data\Db;

class InvoicesDataService
{
  public static function create(array $data): object
  {
    $id = strtoupper(uniqid());
    $data['labor_rate'] ??= OrgSettingsDataService::getValueByKey($data['org_id'], 'default_labor_rate');
    Db::run("insert into invoices (id, org_id, client_id, summary, labor_rate, created_by, created_at)
    values (?, ?, ?, ?, ?, ?, ?)", [
      $id,
      $data['org_id'],
      $data['client_id'],
      $data['summary'],
      (double) $data['labor_rate'],
      $data['created_by'],
      time()
    ]);
    return self::getById($id, $data['org_id']);
  }

  public static function clone(object $invoice, string $new_summary, string $user_id): object
  {
    $new_invoice = self::create([
      'org_id' => $invoice->org_id,
      'client_id' => $invoice->client_id,
      'summary' => $new_summary,
      'labor_rate' => (double) $invoice->labor_rate,
      'created_by' => $user_id
    ]);
    $new_invoice->details = $invoice->details;
    $new_invoice->labor_hours = $invoice->labor_hours;
    $new_invoice->created_by = $user_id;
    $new_invoice = self::update($new_invoice);
    $existingItems = ExpensesDataService::listByInvoiceId($invoice->org_id, $invoice->id);
    foreach ($existingItems as $item)
      ExpensesDataService::addToInvoice($new_invoice->org_id, $new_invoice->id, $item->expense_id);
    return $new_invoice;
  }

  public static function getById(string $id, string $org_id): ?object
  {
    return Db::run("select * from v_invoices
    where id = ? and org_id = ?", [$id, $org_id])->fetch() ?: null;
  }

  public static function list(string $org_id): array
  {
    return Db::run("select * from v_list_invoices
    where org_id = ?", [$org_id])->fetchAll();
  }

  public static function listItemizations(string $org_id, string $invoice_id): array
  {
    return Db::run("select * from v_invoice_itemizations
    where org_id = ? and invoice_id = ?", [$org_id, $invoice_id])->fetchAll();
  }

  public static function update(object $invoice): ?object
  {
    Db::run("update invoices set
      client_id = ?,
      summary = ?,
      details = ?,
      labor_hours = ?,
      labor_rate = ?,
      due_date = ?,
      paid_date = ?,
      paid_amount = ?,
      updated_by = ?,
      updated_at = ?
    where id = ? and org_id = ?", [
      $invoice->client_id,
      $invoice->summary,
      $invoice->details,
      $invoice->labor_hours,
      $invoice->labor_rate,
      $invoice->due_date,
      $invoice->paid_date,
      $invoice->paid_amount,
      $invoice->updated_by,
      time(),
      $invoice->id,
      $invoice->org_id
    ]);
    return self::getById($invoice->id, $invoice->org_id);
  }

  public static function delete(object $invoice): void
  {
    Db::run("delete from invoices
    where id = ? and org_id = ?", [$invoice->id, $invoice->org_id]);
  }
}