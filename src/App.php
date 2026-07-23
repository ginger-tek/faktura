<?php

require __DIR__ . '/Bootstrap.php';

try {
  $app = new \GingerTek\Routy([
    'render' => function ($view, $ctx, $app) {
      $ctx['branding'] = join(' | ', array_filter(['Faktura', getenv('BRANDING') ?: '']));
      $ctx['view'] = ROOT . "/views/$view.php";
      $ctx['title'] ??= $ctx['message'] ?? ucfirst($view);
      $ctx['permissions'] = $app->getCtx('user')->permissions_bit ? array_values(array_filter(
        \App\Permissions::list(),
        fn($bit) => \App\Permissions::has($app->getCtx('user')->permissions_bit, $bit),
      )) : [];
      ob_start();
      extract($ctx);
      include ROOT . "/views/_layout.php";
      return ob_get_clean();
    }
  ]);
  $app->get('/login', \App\Controller::viewLogin(...));
  $app->post('/login', \App\Controller::submitLogin(...));
  $app->use(\App\Middleware::id(...));
  $app->get('/', fn() => $app->redirect('/dashboard'));
  $app->post('/logout', \App\Controller::submitLogout(...));
  $app->get('/dashboard', \App\Controller::viewDashboard(...));
  $app->get('/reports/month', \App\Controller::viewMonthReport(...));
  $app->get('/reports/annual', \App\Controller::viewAnnualReport(...));
  $app->post('/clients', \App\Middleware::can(\App\Permissions::CREATE_CLIENT)(...), \App\Controller::createClient(...));
  $app->get('/clients', \App\Middleware::can(\App\Permissions::LIST_CLIENTS)(...), \App\Controller::listClients(...));
  $app->get('/clients/:id', \App\Middleware::can(\App\Permissions::VIEW_CLIENT)(...), \App\Controller::viewClient(...));
  $app->post('/clients/:id', \App\Middleware::can(\App\Permissions::EDIT_CLIENT)(...), \App\Controller::putClient(...));
  $app->post('/clients/:id/delete', \App\Middleware::can(\App\Permissions::DELETE_CLIENT)(...), \App\Controller::deleteClient(...));
  $app->post('/invoices', \App\Middleware::can(\App\Permissions::CREATE_INVOICE)(...), \App\Controller::createInvoice(...));
  $app->get('/invoices', \App\Middleware::can(\App\Permissions::LIST_INVOICES)(...), \App\Controller::listInvoices(...));
  $app->get('/invoices/:id', \App\Middleware::can(\App\Permissions::VIEW_INVOICE)(...), \App\Controller::viewInvoice(...));
  $app->post('/invoices/:id', \App\Middleware::can(\App\Permissions::EDIT_INVOICE)(...), \App\Controller::putInvoice(...));
  $app->get('/invoices/:id/print', \App\Middleware::can(\App\Permissions::VIEW_INVOICE)(...), \App\Controller::printInvoice(...));
  $app->post('/invoices/:id/delete', \App\Middleware::can(\App\Permissions::DELETE_INVOICE)(...), \App\Controller::deleteInvoice(...));
  $app->get('/expenses', \App\Middleware::can(\App\Permissions::LIST_EXPENSES)(...), \App\Controller::listExpenses(...));
  $app->get('/settings', \App\Middleware::can(\App\Permissions::VIEW_SETTINGS)(...), \App\Controller::viewSettings(...));
  $app->post('/settings', \App\Middleware::can(\App\Permissions::EDIT_SETTINGS)(...), \App\Controller::updateSettings(...));
  $app->post('/users/new', \App\Middleware::can(\App\Permissions::CREATE_USER)(...), \App\Controller::createUser(...));
  $app->get('/users', \App\Middleware::can(\App\Permissions::VIEW_USERS)(...), \App\Controller::viewUsers(...));
  $app->post('/users', \App\Middleware::can(\App\Permissions::EDIT_USERS)(...), \App\Controller::updateUsers(...));
  $app->post('/users/logout', \App\Middleware::can(\App\Permissions::EDIT_USERS)(...), \App\Controller::submitUserLogout(...));
  $app->post('/users/delete', \App\Middleware::can(\App\Permissions::DELETE_USER)(...), \App\Controller::deleteUser(...));
  $app->get('/account', \App\Controller::viewAccount(...));
  $app->post('/account', \App\Controller::updateAccountPassword(...));
  $app->fallback(fn() => $app->render('notFound'));
} catch (\Exception $e) {
  error_log($e->getMessage());
  $app->render('error', ['message' => 'An error occurred!']);
}