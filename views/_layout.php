<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($branding) ?> - <?= $title ?? '' ?></title>
  <link rel="stylesheet" href="/assets/styles.css" />
</head>

<body>
  <header class="mb-1">
    <nav class="flex spaced">
      <div>
        <b><a href="/"><?= htmlspecialchars($branding) ?></a></b>
      </div>
      <div class="flex">
        <?php if ($app->getCtx('user')): ?>
          <details class="dropdown" id="menu">
            <summary>Menu</summary>
            <ul>
              <li><b><?= $app->getCtx('user')->username ?? '' ?></b></li>
              <li><a href="/dashboard">Dashboard</a></li>
              <li><a href="/reports/annual">Annual Report</a></li>
              <li><a href="/reports/month">Monthly Report</a></li>
              <?php if (in_array(\App\Permissions::LIST_CLIENTS, $permissions)): ?>
                <li><a href="/clients">Clients</a></li>
              <?php endif; ?>
              <?php if (in_array(\App\Permissions::LIST_INVOICES, $permissions)): ?>
                <li><a href="/invoices">Invoices</a></li>
              <?php endif; ?>
              <?php if (in_array(\App\Permissions::LIST_EXPENSES, $permissions)): ?>
                <li><a href="/expenses">Expenses</a></li>
              <?php endif; ?>
              <?php if (in_array(\App\Permissions::VIEW_SETTINGS, $permissions)): ?>
                <li><a href="/settings">Settings</a></li>
              <?php endif; ?>
              <?php if (in_array(\App\Permissions::VIEW_USERS, $permissions)): ?>
                <li><a href="/users">Users</a></li>
              <?php endif; ?>
              <li><a href="/account">My Account</a></li>
              <li><a href="#" onclick="event.preventDefault(); logoutModal.showModal()">Logout</a></li>
            </ul>
          </details>
          <dialog id="logoutModal">
            <header class="mb-sm"><b>Logout</b></header>
            <form action="/logout" method="POST">
              <label class="mb-1">
                <input type="checkbox" name="all"> Logout from everywhere?
              </label>
              <div class="flex spread">
                <button type="submit" class="success">Logout</button>
                <button type="button" onclick="logoutModal.close()">Cancel</button>
              </div>
            </form>
          </dialog>
        <?php else: ?>
          <a href="/login">Login</a>
        <?php endif; ?>
      </div>
    </nav>
  </header>
  <main>
    <?php include $view; ?>
  </main>
  <script>
    typeof menu !== 'undefined' && document.addEventListener('click', (ev) => !menu.contains(ev.target) && menu.removeAttribute('open'));
  </script>
</body>

</html>