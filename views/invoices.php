<div class="flex spaced stack mb-1">
  <h2 class="m-0">Invoices</h2>
  <form method="GET" class="flex">
    <select name="status" onchange="this.form.submit()">
      <option value="" hidden>Filter By Status</option>
      <?php foreach ($status_filters as $value): ?>
        <option value="<?= $value ?>" <?= $status === $value ? 'selected' : '' ?>><?= $value ?></option>
      <?php endforeach; ?>
    </select>
    <select name="sort" onchange="this.form.submit()">
      <option value="" hidden>Sort By</option>
      <?php foreach ($sort_options as $label => $value): ?>
        <option value="<?= $value ?>" <?= $sort === $value ? 'selected' : '' ?>><?= $label ?></option>
      <?php endforeach; ?>
    </select>
    <?php if ($sort || $status): ?>
      <button type="button" onclick="window.location.href = '/invoices'" class="info">Reset</button>
    <?php endif; ?>
    <?php if (in_array(\App\Permissions::CREATE_INVOICE, $permissions)): ?>
        <button type="button" onclick="newInvoiceModal.showModal()" class="success">New Invoice</button>
    <?php endif; ?>
  </form>
</div>
<div class="overflow-auto">
  <table>
    <thead>
      <tr>
        <th>Status</th>
        <th>Summary</th>
        <th>Client</th>
        <th>Total Amount</th>
        <th>Paid Amount</th>
        <th>Due Date</th>
        <th>Paid Date</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($invoices as $invoice): ?>
          <tr>
            <td><b class="color-<?= $status_colors[$invoice->status] ?>"><?= $invoice->status ?></b></td>
            <td><a href="/invoices/<?= $invoice->id ?>"><?= htmlspecialchars($invoice->summary) ?></a></td>
            <td><a href="/clients/<?= $invoice->client_id ?>"><?= htmlspecialchars($invoice->client_name) ?></a></td>
            <td data-align="right"><?= \App\Utils::to_currency($invoice->total_amount) ?></td>
            <td data-align="right"><?= \App\Utils::to_currency($invoice->paid_amount) ?></td>
            <td><?= $invoice->due_date ? date('n/j/Y', strtotime($invoice->due_date)) : '--' ?></td>
            <td><?= $invoice->paid_date ? date('n/j/Y', strtotime($invoice->paid_date)) : '--' ?></td>
          </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php if (in_array(\App\Permissions::CREATE_INVOICE, $permissions)): ?>
    <dialog id="newInvoiceModal">
      <header class="mb-sm"><b>New Invoice</b></header>
      <form method="POST" action="/invoices">
        <label>Summary
          <input type="text" name="summary" required>
        </label>
        <label>Client
          <select name="client_id" required>
            <option value="" hidden>Select a client</option>
            <?php if (in_array(\App\Permissions::LIST_CLIENTS, $permissions)):
              $clients ??= [];
              foreach ($clients as $client): ?>
                    <option value="<?= $client->id ?>"><?= htmlspecialchars($client->name) ?></option>
                <?php endforeach;
            endif; ?>
          </select>
        </label>
        <div class="flex spread">
          <button type="button" onclick="this.closest('form').reset(); newInvoiceModal.close()">Cancel</button>
          <button type="submit" class="success">Create</button>
        </div>
      </form>
    </dialog>
<?php endif; ?>