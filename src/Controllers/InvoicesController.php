<?php

namespace Faktura\Controllers;

use GingerTek\Routy;
use Faktura\Services\InvoicesDataService;
use Faktura\Services\OrgsDataService;
use Faktura\Services\OrgSettingsDataService;
use Faktura\Services\ClientsDataService;
use Faktura\Services\ExpensesDataService;
use Faktura\Services\UtilsService;
use Parsedown;

class InvoicesController
{
  public static function submitCreate(Routy $app)
  {
    $user = $app->getCtx('user');
    $data = $app->getBody();
    if (!isset($data->client_id, $data->summary) || !is_string($data->client_id) || !is_string($data->summary))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $invoice = InvoicesDataService::create([
      'org_id' => $user->org_id,
      'client_id' => $data->client_id,
      'summary' => $data->summary,
      'created_by' => $user->id
    ]);
    return $app->status(201)->sendJson($invoice);
  }

  public static function submitClone(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $invoice = InvoicesDataService::getById($id, $user->org_id);
    if (!$invoice)
      return $app->status(404)->sendJson(['error' => 'Invoice not found']);
    $cloned_invoice = InvoicesDataService::clone($invoice, "Copy of {$invoice->summary}", $user->id);
    return $app->status(201)->sendJson($cloned_invoice);
  }

  public static function list(Routy $app)
  {
    $user = $app->getCtx('user');
    $invoices = InvoicesDataService::list($user->org_id);
    return $app->sendJson($invoices);
  }

  public static function get(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $invoice = InvoicesDataService::getById($id, $user->org_id);
    if (!$invoice)
      return $app->status(404)->sendJson(['error' => 'Invoice not found']);
    return $app->sendJson($invoice);
  }

  public static function listItemizations(Routy $app)
  {
    $user = $app->getCtx('user');
    $invoice_id = $app->getParam('id');
    $itemizations = InvoicesDataService::listItemizations($user->org_id, $invoice_id);
    return $app->sendJson($itemizations);
  }

  public static function addExpense(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $invoice = InvoicesDataService::getById($id, $user->org_id);
    if (!$invoice)
      return $app->status(404)->sendJson(['error' => 'Invoice not found']);
    $data = $app->getBody();
    if (!isset($data->expense_id) || !is_string($data->expense_id))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $itemization = ExpensesDataService::addToInvoice($user->org_id, $invoice->id, $data->expense_id);
    return $app->status(201)->sendJson($itemization);
  }

  public static function removeExpense(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $invoice = InvoicesDataService::getById($id, $user->org_id);
    if (!$invoice)
      return $app->status(404)->sendJson(['error' => 'Invoice not found']);
    $data = $app->getBody();
    if (!isset($data->expense_id) || !is_string($data->expense_id))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    ExpensesDataService::removeFromInvoice($user->org_id, $invoice->id, $data->expense_id);
    return $app->sendJson(['message' => 'Expense removed from invoice']);
  }

  public static function renderPrint(Routy $app)
  {
    $user = $app->getCtx('user');
    if (!PermissionsService::hasPermission($user->role_bit_value, PermissionsService::INVOICE_READ))
      return $app->status(403)->sendJson(['error' => 'Forbidden']);
    $id = $app->getParam('id');
    $invoice = InvoicesDataService::getById($id, $user->org_id);
    if (!$invoice)
      return $app->status(404)->sendJson(['error' => 'Invoice not found']);
    $client = ClientsDataService::getById($invoice->client_id, $user->org_id);
    $org = OrgsDataService::getById($user->org_id);
    $invoice_template = OrgSettingsDataService::getValueByKey($user->org_id, 'invoice_template');
    $markdown = preg_replace_callback('#{{\s*((?<obj>\w+)\.)?(?<field>\w+)\s*(?:\|\s*(?<mod>\w+)\s*)?}}#', function ($matches) use (&$invoice, &$client, &$org) {
      [$obj, $field, $mod] = [$matches['obj'] ?? null, $matches['field'], $matches['mod'] ?? null];
      $val = match (true) {
        $field == 'current_date' => date('n/j/Y'),
        $obj == 'org' && !property_exists($org, $field) => OrgSettingsDataService::getValueByKey($org->id, $field),
        "$obj.$field" == 'invoice.items' => join("\r\n", [
          "|Summary|Unit Price|Quantity|Total Amount|",
          "|:---|---:|---:|---:|",
          ...array_map(
            fn($item) => "|{$item->summary}|" . UtilsService::toUSD($item->unit_price) . "|{$item->quantity}|" . UtilsService::toUSD($item->total_amount) . "|",
            InvoicesDataService::listItemizations($invoice->org_id, $invoice->id)
          ),
          "| | |**Labor**|" . UtilsService::toUSD($invoice->labor_amount) . "|",
          "| | |**Total**|**" . UtilsService::toUSD($invoice->total_amount) . "**|"
        ]),
        default => ${$obj}->$field ?? ''
      };
      if ($mod) {
        $val = match ($mod) {
          'date' => date('n/j/Y', strtotime($val)),
          'currency' => UtilsService::toUSD($val),
          default => $val
        };
      }
      return $val;
    }, $invoice_template);
    header('Content-Type: text/html');
    $parser = new Parsedown();
    $parser->setUrlsLinked(true);
    $parser->setBreaksEnabled(true);
    exit($parser->text($markdown));
  }

  public static function update(Routy $app)
  {
    $user = $app->getCtx('user');
    if (!PermissionsService::hasPermission($user->role_bit_value, PermissionsService::INVOICE_UPDATE))
      return $app->status(403)->sendJson(['error' => 'Forbidden']);
    $org_id = $user->org_id;
    $id = $app->getParam('id');
    $invoice = InvoicesDataService::getById($id, $org_id);
    if (!$invoice)
      return $app->status(404)->sendJson(['error' => 'Invoice not found']);
    $data = $app->getBody();
    if (!isset($data->client_id, $data->summary) || !is_string($data->client_id) || !is_string($data->summary))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $invoice->client_id = $data->client_id;
    $invoice->summary = $data->summary;
    $invoice->details = $data->details ?? $invoice->details;
    $invoice->due_date = $data->due_date ?? $invoice->due_date;
    $invoice->paid_date = $data->paid_date ?? $invoice->paid_date;
    $invoice->labor_hours = $data->labor_hours ?? $invoice->labor_hours;
    $invoice->labor_rate = $data->labor_rate ?? $invoice->labor_rate;
    $invoice->updated_by = $user->id;
    $invoice = InvoicesDataService::update($invoice);
    return $app->sendJson($invoice);
  }

  public static function delete(Routy $app)
  {
    $user = $app->getCtx('user');
    if (!PermissionsService::hasPermission($user->role_bit_value, PermissionsService::INVOICE_DELETE))
      return $app->status(403)->sendJson(['error' => 'Forbidden']);
    $org_id = $user->org_id;
    $id = $app->getParam('id');
    $invoice = InvoicesDataService::getById($id, $org_id);
    if (!$invoice)
      return $app->status(404)->sendJson(['error' => 'Invoice not found']);
    InvoicesDataService::delete($invoice);
    return $app->sendJson(['message' => 'Invoice deleted']);
  }
}