<?php

namespace Faktura\Services;

use Faktura\Data\Crud;

class InvoicesDataService
{
  public static function create(array $data): object
  {
    $id = strtoupper(uniqid());
    $data['labor_rate'] ??= OrgSettingsDataService::getValueByKey($data['org_id'], 'default_labor_rate');
    Crud::create('invoices', [
      'id' => $id,
      'org_id' => $data['org_id'],
      'client_id' => $data['client_id'],
      'summary' => $data['summary'],
      'labor_rate' => (double) $data['labor_rate'],
      'created_by' => $data['created_by'],
      'created_at' => time()
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
    return Crud::read('v_invoices', ['id' => $id, 'org_id' => $org_id]);
  }

  public static function list(string $org_id, array $filters = []): array
  {
    $conditions = ['org_id' => $org_id];
    if (isset($filters['start'], $filters['end']))
      $conditions['due_date'] = [
        'between',
        [
          $filters['start'] . ' 00:00:00',
          $filters['end'] . ' 23:59:59'
        ]
      ];
    return Crud::readAll('v_list_invoices', $conditions);
  }

  public static function listItemizations(string $org_id, string $invoice_id): array
  {
    return Crud::readAll('v_invoice_itemizations', ['org_id' => $org_id, 'invoice_id' => $invoice_id]);
  }

  public static function update(object $invoice): ?object
  {
    if (
      Crud::update('invoices', [
        'client_id' => $invoice->client_id,
        'summary' => $invoice->summary,
        'details' => $invoice->details,
        'labor_hours' => $invoice->labor_hours,
        'labor_rate' => $invoice->labor_rate,
        'due_date' => $invoice->due_date,
        'paid_date' => $invoice->paid_date,
        'paid_amount' => $invoice->paid_amount,
        'updated_by' => $invoice->updated_by,
        'updated_at' => time()
      ], [
        'id' => $invoice->id,
        'org_id' => $invoice->org_id
      ])
    )
      return self::getById($invoice->id, $invoice->org_id);
    return null;
  }

  public static function delete(object $invoice): bool
  {
    return Crud::delete('invoices', ['id' => $invoice->id, 'org_id' => $invoice->org_id]);
  }
}