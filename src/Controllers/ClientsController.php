<?php

namespace Faktura\Controllers;

use GingerTek\Routy;
use Faktura\Services\ClientsDataService;

class ClientsController
{
  public static function submitCreate(Routy $app)
  {
    $user = $app->getCtx('user');
    $data = $app->getBody();
    if (!isset($data->full_name, $data->contact_email) || !is_string($data->full_name) || !is_string($data->contact_email))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $client = ClientsDataService::create([
      'org_id' => $user->org_id,
      'full_name' => $data->full_name,
      'contact_email' => $data->contact_email,
      'created_by' => $user->id,
    ]);
    return $app->status(201)->sendJson($client);
  }

  public static function list(Routy $app)
  {
    $user = $app->getCtx('user');
    $clients = ClientsDataService::list($user->org_id);
    return $app->sendJson($clients);
  }

  public static function get(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $client = ClientsDataService::getById($id, $user->org_id);
    if (!$client)
      return $app->status(404)->sendJson(['error' => 'Client not found']);
    return $app->sendJson($client);
  }

  public static function update(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $client = ClientsDataService::getById($id, $user->org_id);
    if (!$client)
      return $app->status(404)->sendJson(['error' => 'Client not found']);
    $data = $app->getBody();
    if (!isset($data->full_name, $data->contact_email) || !is_string($data->full_name) || !is_string($data->contact_email))
      return $app->status(400)->sendJson(['error' => 'Invalid request']);
    $client->full_name = $data->full_name;
    $client->contact_email = $data->contact_email;
    $client->contact_phone = $data->contact_phone ?? null;
    $client->contact_address = $data->contact_address ?? null;
    $client = ClientsDataService::update($client);
    return $app->sendJson($client);
  }

  public static function delete(Routy $app)
  {
    $user = $app->getCtx('user');
    $id = $app->getParam('id');
    $client = ClientsDataService::getById($id, $user->org_id);
    if (!$client)
      return $app->status(404)->sendJson(['error' => 'Client not found']);
    if (ClientsDataService::isClientInvoiced($client->id, $user->org_id))
      return $app->status(400)->sendJson(['error' => 'Cannot delete a client that has invoices']);
    ClientsDataService::delete($client);
    return $app->sendJson(['message' => 'Client deleted']);
  }
}