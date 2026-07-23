<?php

namespace App;

use GingerTek\Routy;

class Controller
{
  // Auth
  public static function viewLogin(Routy $app)
  {
    $app->render('login', ['title' => 'Login', 'redirect' => $app->getQuery('redirect')]);
  }

  public static function submitLogin(Routy $app)
  {
    $data = $app->getBody();
    $redirect = $app->getQuery('redirect');
    $user = Crud::read('users', ['username' => $data->username, 'is_active' => 1]);
    if (!$user || !password_verify($data->password, $user->passhash))
      return $app->render('login', ['title' => 'Login', 'error' => 'Invalid credentials', 'redirect' => $redirect]);
    if ($user->tokens ?? false) {
      $tokens = explode(',', $user->tokens);
      foreach ($tokens as $token) {
        $tokenData = Tokens::decode($token);
        if (!$tokenData || $tokenData->exp < time())
          $user->tokens = str_replace($token, '', $user->tokens);
      }
    }
    ['token' => $token, 'expires' => $expires] = Tokens::encode(['user_id' => $user->id]);
    Crud::update('users', ['tokens' => ($user->tokens ?? '') . $token], ['id' => $user->id]);
    setcookie('token', $token, [
      'expires' => $expires,
      'httponly' => true,
      'samesite' => 'Strict'
    ]);
    $app->redirect($redirect ?: '/dashboard');
  }

  public static function submitLogout(Routy $app)
  {
    $user = $app->getCtx('user');
    $token = $app->getCtx('token');
    $user->tokens = $app->getBody()?->all ? '' : str_replace($token, '', $user->tokens);
    Crud::update('users', ['tokens' => $user->tokens], ['id' => $app->getCtx('user')->id]);
    setcookie('token', '', [
      'expires' => time() - 3600,
      'httponly' => true,
      'samesite' => 'Strict'
    ]);
    $app->redirect('/login');
  }

  // Dashboard
  public static function viewDashboard(Routy $app)
  {
    $year = new \DateTimeImmutable('first day of January this year');
    $month = new \DateTimeImmutable('first day of this month');
    $data = [
      'year' => [
        'date' => $year,
        'total_income' => Crud::run(
          'select sum(paid_amount) as total
          from v_invoices
          where paid_date like :year and status = "Paid"',
          ['year' => $year->format('Y') . '%']
        )->fetch()->total,
        'total_expenses' => Crud::run(
          'select sum(quantity * unit_price) as total
          from v_invoice_items
          where is_expense = 1 and created_at like :created_at',
          ['created_at' => $year->format('Y') . '%']
        )->fetch()->total,
        'upcoming_income' => Crud::run(
          'select sum(paid_amount) as total
          from v_invoices
          where due_date like :year and due_date > :after_today
          and status = "Pending"',
          [
            'year' => $year->format('Y') . '%',
            'after_today' => date('Y-m-d')
          ]
        )->fetch()->total,
      ],
      'month' => [
        'date' => $month,
        'total_income' => Crud::run(
          'select sum(paid_amount) as total
          from v_invoices
          where paid_date like :month and status = "Paid"',
          ['month' => $month->format('Y-m') . '%']
        )->fetch()->total,
        'total_expenses' => Crud::run(
          'select sum(quantity * unit_price) as total
          from v_invoice_items
          where is_expense = 1 and created_at like :created_at',
          ['created_at' => $month->format('Y-m') . '%']
        )->fetch()->total,
        'upcoming_income' => Crud::run(
          'select sum(paid_amount) as total
          from v_invoices
          where due_date like :month and due_date > :after_today
          and status = "Pending"',
          [
            'month' => $month->format('Y-m') . '%',
            'after_today' => date('Y-m-d')
          ]
        )->fetch()->total,
      ]
    ];
    $app->render('dashboard', [
      'title' => 'Dashboard',
      'data' => $data
    ]);
  }

  // Reports
  public static function viewMonthReport(Routy $app)
  {
    $filter_month = $app->getQuery('filter_month') ?: date('Y-m');
    if ($filter_month && !preg_match('/^\d{4}-\d{2}$/', $filter_month))
      return $app->render('error', ['message' => 'Invalid month format']);
    $invoices = Crud::list('v_invoices', [
      'due_date' => ['like', "$filter_month%"],
    ]);
    $dateObj = new \DateTimeImmutable("$filter_month-01");
    $month = ['days' => [], 'max' => 1];
    $invoices_by_day = [];
    foreach ($invoices as $invoice)
      $invoices_by_day[$invoice->paid_date ?: $invoice->due_date][] = $invoice;
    while ($dateObj->format('Y-m') === $filter_month) {
      $ymd = $dateObj->format('Y-m-d');
      $revenue = array_reduce($invoices_by_day[$ymd] ?? [], fn($c, $i) => $c + $i->total_amount, 0);
      $expenses = array_reduce($invoices_by_day[$ymd] ?? [], fn($c, $i) => $c + $i->total_expenses, 0);
      $expected_income = array_reduce($invoices_by_day[$ymd] ?? [], fn($c, $i) => $c + ($i->total_amount - $i->total_expenses), 0);
      $paid_income = array_reduce($invoices_by_day[$ymd] ?? [], fn($c, $i) => $c + ($i->paid_amount - $i->total_expenses), 0);
      $data = [
        'date' => $dateObj,
        'revenue' => $revenue,
        'expenses' => $expenses,
        'expected_income' => $expected_income,
        'paid_income' => $paid_income
      ];
      $month['max'] = max($month['max'], $revenue, $expenses, $expected_income, $paid_income);
      $month['days'][] = $data;
      $dateObj = $dateObj->modify('+1 day');
    }
    $app->render('month-report', [
      'title' => 'Monthly Report',
      'filter_month' => $filter_month,
      'previous_month' => $dateObj->modify('-2 month')->format('Y-m'),
      'chart' => $month,
      'next_month' => $dateObj->format('Y-m'),
      'summary' => [
        'number_of_invoices' => count($invoices),
        'total_revenue' => array_reduce($month['days'], fn($c, $d) => $c + $d['revenue'], 0),
        'total_expenses' => array_reduce($month['days'], fn($c, $d) => $c + $d['expenses'], 0),
        'total_expected_income' => array_reduce($month['days'], fn($c, $d) => $c + $d['expected_income'], 0),
        'total_paid_income' => array_reduce($month['days'], fn($c, $d) => $c + $d['paid_income'], 0),
      ]
    ]);
  }

  public static function viewAnnualReport(Routy $app)
  {
    $filter_year = $app->getQuery('filter_year') ?: date('Y');
    if ($filter_year && !preg_match('/^\d{4}$/', $filter_year))
      return $app->render('error', ['message' => 'Invalid year format']);
    $invoices = Crud::list('v_invoices', [
      'due_date' => ['like', "$filter_year%"],
    ]);
    $dateObj = new \DateTimeImmutable("$filter_year-01-01");
    $year = ['months' => [], 'max' => 1];
    $invoices_by_month = [];
    foreach ($invoices as $invoice)
      $invoices_by_month[date('Y-m', strtotime($invoice->paid_date ?: $invoice->due_date))][] = $invoice;
    while ($dateObj->format('Y') === $filter_year) {
      $ym = $dateObj->format('Y-m');
      $revenue = array_reduce($invoices_by_month[$ym] ?? [], fn($c, $i) => $c + $i->total_amount, 0);
      $expenses = array_reduce($invoices_by_month[$ym] ?? [], fn($c, $i) => $c + $i->total_expenses, 0);
      $expected_income = array_reduce($invoices_by_month[$ym] ?? [], fn($c, $i) => $c + ($i->total_amount - $i->total_expenses), 0);
      $paid_income = array_reduce($invoices_by_month[$ym] ?? [], fn($c, $i) => $c + ($i->paid_amount - $i->total_expenses), 0);
      $data = [
        'date' => $dateObj,
        'revenue' => $revenue,
        'expenses' => $expenses,
        'expected_income' => $expected_income,
        'paid_income' => $paid_income
      ];
      $year['max'] = max($year['max'], $revenue, $expenses, $expected_income, $paid_income);
      $year['months'][] = $data;
      $dateObj = $dateObj->modify('+1 month');
    }
    $app->render('annual-report', [
      'title' => 'Annual Report',
      'filter_year' => $filter_year,
      'previous_year' => $dateObj->modify('-2 year')->format('Y'),
      'chart' => $year,
      'next_year' => $dateObj->format('Y'),
      'summary' => [
        'number_of_invoices' => count($invoices),
        'total_revenue' => array_reduce($year['months'], fn($c, $d) => $c + $d['revenue'], 0),
        'total_expenses' => array_reduce($year['months'], fn($c, $d) => $c + $d['expenses'], 0),
        'total_expected_income' => array_reduce($year['months'], fn($c, $d) => $c + $d['expected_income'], 0),
        'total_paid_income' => array_reduce($year['months'], fn($c, $d) => $c + $d['paid_income'], 0),
      ]
    ]);
  }

  // Clients
  public static function createClient(Routy $app)
  {
    $data = $app->getBody();
    $client = Crud::create('clients', [
      'name' => $data->name,
      'email' => $data->email,
      'phone' => $data->phone,
      'created_at' => time()
    ]);
    $app->redirect("/clients/{$client->id}");
  }

  public static function listClients(Routy $app)
  {
    $sort = $app->getQuery('sort') ?: null;
    $clients = Crud::list('clients', $sort ? ['order_by' => $sort] : []);
    $app->render('clients', [
      'title' => 'Clients',
      'clients' => $clients,
      'sort_options' => [
        'Name' => 'name',
        'Email' => 'email',
        'Phone' => 'phone',
        'Created At' => 'created_at',
        'Updated At' => 'updated_at'
      ],
      'sort' => $sort
    ]);
  }

  public static function viewClient(Routy $app)
  {
    $client = Crud::read('clients', ['id' => $app->getParam('id')]);
    if (!$client)
      return $app->render('notFound', ['message' => 'Client not found']);
    $app->render('client', ['title' => "Client: {$client->name}", 'client' => $client]);
  }

  public static function putClient(Routy $app)
  {
    $client = Crud::read('clients', ['id' => $app->getParam('id')]);
    if (!$client)
      return $app->render('notFound', ['message' => 'Client not found']);
    $data = $app->getBody();
    Crud::update('clients', [
      'name' => $data->name,
      'email' => $data->email,
      'phone' => $data->phone,
      'address' => $data->address,
      'updated_at' => time()
    ], ['id' => $client->id]);
    $app->redirect("/clients/{$client->id}");
  }

  public static function deleteClient(Routy $app)
  {
    $client = Crud::read('clients', ['id' => $app->getParam('id')]);
    if (!$client)
      return $app->render('notFound', ['message' => 'Client not found']);
    $isAssigned = Crud::read('invoices', ['client_id' => $client->id]);
    if ($isAssigned)
      return $app->render('error', ['message' => 'Cannot delete client with assigned invoices']);
    Crud::delete('clients', ['id' => $client->id]);
    $app->redirect('/clients');
  }

  // Invoices
  public static function createInvoice(Routy $app)
  {
    $data = $app->getBody();
    $invoice = Crud::create('invoices', [
      'number' => strtoupper(uniqid()),
      'summary' => $data->summary,
      'client_id' => $data->client_id,
      'created_at' => time()
    ]);
    $app->redirect("/invoices/{$invoice->id}");
  }

  public static function listInvoices(Routy $app)
  {
    $status = $app->getQuery('status') ?: null;
    $sort = $app->getQuery('sort') ?: null;
    if ($status && !in_array($status, ['Pending', 'Overdue', 'Paid', 'Late']))
      return $app->render('error', ['message' => 'Invalid status filter']);
    if ($sort && !in_array($sort, ['status', 'summary', 'client_name', 'total_amount', 'paid_amount', 'due_date', 'paid_date']))
      return $app->render('error', ['message' => 'Invalid sort parameter']);
    $invoices = Crud::list('v_invoices', ($sort ? ['order_by' => $sort] : []) + ($status ? ['status' => $status] : []));
    $clients = Crud::list('clients');
    $app->render('invoices', [
      'title' => 'Invoices',
      'invoices' => $invoices,
      'clients' => $clients,
      'status_colors' => [
        'Pending' => 'teal',
        'Paid' => 'green',
        'Paid Late' => 'yellow',
        'Overdue' => 'red'
      ],
      'status_filters' => [
        'Pending',
        'Overdue',
        'Paid',
        'Paid Late'
      ],
      'sort_options' => [
        'Status' => 'status',
        'Summary' => 'summary',
        'Client' => 'client_name',
        'Total Amount' => 'total_amount',
        'Paid Amount' => 'paid_amount',
        'Due Date' => 'due_date',
        'Paid Date' => 'paid_date'
      ],
      'status' => $status,
      'sort' => $sort
    ]);
  }

  public static function viewInvoice(Routy $app)
  {
    $invoice = Crud::read('v_invoices', [
      'id' => $app->getParam('id'),
      'number' => $app->getParam('id'),
      'join' => 'or'
    ]);
    if (!$invoice)
      return $app->render('notFound', ['message' => 'Invoice not found']);
    $clients = Crud::list('clients');
    $invoice->items = Crud::list('v_invoice_items', ['invoice_id' => $invoice->id]);
    $app->render('invoice', [
      'title' => "Invoice: {$invoice->summary}",
      'invoice' => $invoice,
      'clients' => $clients
    ]);
  }

  public static function putInvoice(Routy $app)
  {
    $invoice = Crud::read('invoices', ['id' => $app->getParam('id')]);
    if (!$invoice)
      return $app->render('notFound', ['message' => 'Invoice not found']);
    $data = $app->getBody();
    Crud::update('invoices', [
      'summary' => $data->summary,
      'client_id' => $data->client_id,
      'details' => $data->details,
      'labor_amount' => $data->labor_amount,
      'due_date' => $data->due_date,
      'paid_date' => $data->paid_date,
      'paid_amount' => $data->paid_amount,
      'updated_at' => time()
    ], ['id' => $invoice->id]);
    $items = Crud::list('invoice_items', ['invoice_id' => $invoice->id]);
    $existingItemIds = array_column($items, 'id');
    if ($data->items ?? false) {
      foreach ($data->items as $item) {
        if ($existingItemIds && in_array((int) $item['id'], $existingItemIds)) {
          Crud::update('invoice_items', [
            'summary' => $item['summary'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'is_expense' => $item['is_expense'] ?? 0,
            'updated_at' => time()
          ], ['id' => $item['id']]);
        } else {
          Crud::create('invoice_items', [
            'invoice_id' => $invoice->id,
            'summary' => $item['summary'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'is_expense' => $item['is_expense'] ?? 0,
            'created_at' => time()
          ]);
        }
      }
    }
    foreach ($existingItemIds as $existingItemId)
      if (!in_array($existingItemId, array_column($data->items ?? [], 'id')))
        Crud::delete('invoice_items', ['id' => $existingItemId]);
    $app->redirect("/invoices/{$invoice->id}");
  }

  public static function printInvoice(Routy $app)
  {
    $invoice = Crud::read('v_invoices', ['id' => $app->getParam('id')]);
    if (!$invoice)
      return $app->render('notFound', ['message' => 'Invoice not found']);
    $client = Crud::read('clients', ['id' => $invoice->client_id]);
    $settings = array_column(Crud::list('settings'), 'value', 'key');
    $template = Crud::read('settings', ['key' => 'invoice_template'])->value;
    $tokenized = Utils::render_invoice($template, [
      'invoice' => $invoice,
      'client' => $client,
      'settings' => $settings
    ]);
    header('Content-Type: text/html');
    $parser = new \Parsedown();
    $parser->setUrlsLinked(true);
    $parser->setBreaksEnabled(true);
    $parsed = $parser->text($tokenized);
    $app->sendData(<<<HTML
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{$invoice->number} - {$invoice->summary} - {$client->name}</title>
    $parsed
    HTML);
  }

  public static function deleteInvoice(Routy $app)
  {
    $invoice = Crud::read('v_invoices', ['id' => $app->getParam('id')]);
    if (!$invoice)
      return $app->render('notFound', ['message' => 'Invoice not found']);
    Crud::delete('invoices', ['id' => $invoice->id]);
    $app->redirect('/invoices');
  }

  // Expenses
  public static function listExpenses(Routy $app)
  {
    $sort = $app->getQuery('sort') ?: '';
    if ($sort && !in_array($sort, ['summary', 'total_amount', 'created_at', 'updated_at']))
      return $app->render('error', ['message' => 'Invalid sort parameter']);
    $expenses = Crud::list('v_invoice_items', ['is_expense' => 1] + ($sort ? ['order_by' => $sort] : []));
    $app->render('expenses', [
      'title' => 'Expenses',
      'expenses' => $expenses,
      'sort_options' => [
        'Summary' => 'summary',
        'Total Amount' => 'total_amount',
        'Created At' => 'created_at',
        'Updated At' => 'updated_at'
      ],
      'sort' => $sort
    ]);
  }

  // Settings
  public static function viewSettings(Routy $app)
  {
    $settings = array_column(Crud::list('settings'), 'value', 'key');
    $app->render('settings', ['title' => 'Settings', 'settings' => $settings]);
  }

  public static function updateSettings(Routy $app)
  {
    $data = $app->getBody();
    foreach ($data->settings ?? [] as $key => $value)
      Crud::update('settings', ['value' => $value], ['key' => $key]);
    $app->redirect('/settings');
  }

  // Users
  public static function createUser(Routy $app)
  {
    $data = $app->getBody();
    if (!isset($data->username) || !isset($data->password))
      return $app->render('error', ['message' => 'Username and password are required']);
    $existingUser = Crud::read('users', ['username' => $data->username]);
    if ($existingUser)
      return $app->render('error', ['message' => 'Username already exists']);
    Crud::create('users', [
      'username' => $data->username,
      'passhash' => password_hash($data->password, PASSWORD_BCRYPT),
      'permissions_bit' => 0,
      'created_at' => time()
    ]);
    $app->redirect('/users');
  }

  public static function viewUsers(Routy $app)
  {
    $users = Crud::list('users');
    $app->render('users', ['title' => 'Users', 'users' => $users]);
  }

  public static function viewAccount(Routy $app)
  {
    $user = $app->getCtx('user');
    $app->render('account', ['title' => 'Account', 'user' => $user]);
  }

  public static function updateAccountPassword(Routy $app)
  {
    $user = $app->getCtx('user');
    $data = $app->getBody();
    if (!isset($data->current_password) || !isset($data->new_password) || !isset($data->confirm_password))
      return $app->render('error', ['message' => 'All password fields are required']);
    if (!password_verify($data->current_password, $user->passhash))
      return $app->render('error', ['message' => 'Current password is incorrect']);
    if ($data->new_password !== $data->confirm_password)
      return $app->render('error', ['message' => 'New passwords do not match']);
    Crud::update('users', ['passhash' => password_hash($data->new_password, PASSWORD_BCRYPT), 'tokens' => ''], ['id' => $user->id]);
    $app->redirect('/login');
  }

  public static function updateUsers(Routy $app)
  {
    $data = $app->getBody();
    if (!isset($data->users))
      return $app->render('error', ['message' => 'Users array is required']);
    foreach ($data->users as $userData) {
      if ($userData['id'] == $app->getCtx('user')->id && !($userData['is_active'] ?? 0))
        return $app->render('error', ['message' => 'You cannot deactivate your own account']);
      $user = Crud::read('users', ['id' => $userData['id'] ?? null]);
      if (!$user)
        return $app->render('error', ['message' => 'User not found']);
      Crud::update('users', [
        'username' => $userData['username'],
        'is_active' => $userData['is_active'] ?? 0,
        'permissions_bit' => $userData['permissions_bit'] ?? 0,
        'updated_at' => time()
      ], ['id' => $user->id]);
    }
    $app->redirect('/users');
  }

  public static function submitUserLogout(Routy $app)
  {
    $data = $app->getBody();
    $user = Crud::read('users', ['id' => $data->user_id]);
    if (!$user)
      return $app->render('error', ['message' => 'User not found']);
    Crud::update('users', ['tokens' => ''], ['id' => $user->id]);
    $app->redirect('/users');
  }

  public static function deleteUser(Routy $app)
  {
    $data = $app->getBody();
    if (!isset($data->user_id))
      return $app->render('error', ['message' => 'User ID is required']);
    if ($data->user_id == $app->getCtx('user')->id)
      return $app->render('error', ['message' => 'You cannot delete your own account']);
    $user = Crud::read('users', ['id' => $data->user_id]);
    if (!$user)
      return $app->render('error', ['message' => 'User not found']);
    Crud::delete('users', ['id' => $user->id]);
    $app->redirect('/users');
  }
}