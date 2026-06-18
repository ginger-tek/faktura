<?php

namespace Faktura\Controllers;

use GingerTek\Routy;
use Faktura\Services\ExpensesDataService;
use Faktura\Services\PermissionsService;

class ExpensesController
{
  public static function submitCreate(Routy $app)
  {
    $user = $app->getCtx('user');
    $data = $app->getBody();
    if (
      !isset($data->summary, $data->unit_price, $data->quantity, $data->purchase_date)
      || !is_string($data->summary) || !is_numeric($data->unit_price) || !is_numeric($data->quantity) || !is_string($data->purchase_date)
    )
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $expense = ExpensesDataService::create(
      $user->org_id,
      $data->summary,
      $data->unit_price,
      $data->quantity,
      $data->purchase_date
    );
    return $app->status(201)->sendJson($expense);
  }

  public static function list(Routy $app)
  {
    $user = $app->getCtx('user');
    $expenses = ExpensesDataService::list($user->org_id);
    return $app->sendJson($expenses);
  }

  public static function get(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $expense = ExpensesDataService::getById($id, $user->org_id);
    if (!$expense)
      return $app->status(404)->sendJson(['error' => 'Expense not found']);
    return $app->sendJson($expense);
  }

  public static function update(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $expense = ExpensesDataService::getById($id, $user->org_id);
    if (!$expense)
      return $app->status(404)->sendJson(['error' => 'Expense not found']);
    $data = $app->getBody();
    if (
      !isset($data->summary, $data->quantity, $data->unit_price, $data->purchase_date)
      || !is_string($data->summary) || !is_numeric($data->quantity) || !is_numeric($data->unit_price) || !is_string($data->purchase_date)
    )
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $expense->summary = $data->summary;
    $expense->quantity = $data->quantity;
    $expense->unit_price = $data->unit_price;
    $expense->purchase_date = $data->purchase_date;
    $expense = ExpensesDataService::update($expense);
    return $app->sendJson($expense);
  }

  public static function delete(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $expense = ExpensesDataService::getById($id, $user->org_id);
    if (!$expense)
      return $app->status(404)->sendJson(['error' => 'Expense not found']);
    if (ExpensesDataService::isExpenseInvoiced($expense->id, $user->org_id))
      return $app->status(400)->sendJson(['error' => 'Cannot delete an expense that is invoiced']);
    ExpensesDataService::delete($expense);
    return $app->sendJson(['message' => 'Expense deleted']);
  }
}