<div class="flex spaced mb-1">
  <h2 class="m-0">Invoice: <?= $invoice->number ?></h2>
  <div class="flex spread-md">
    <button type="button" class="info print"
      onclick="let nw = window.open('/invoices/<?= $invoice->id ?>/print', '_blank');nw.onload = nw.print;nw.onafterprint = nw.close;">Print</button>
    <?php if (in_array(\App\Permissions::EDIT_INVOICE, $permissions)): ?>
      <button type="submit" form="invoice" class="success">Save</button>
    <?php endif; ?>
    <?php if (in_array(\App\Permissions::DELETE_INVOICE, $permissions)): ?>
      <button type="button" class="danger delete" onclick="deleteModal.showModal()">Delete</button>
    <?php endif; ?>
  </div>
</div>
<form id="invoice" method="POST" action="/invoices/<?= $invoice->id ?>" class="mb-1">
  <div class="flex spread">
    <label>Summary
      <input type="text" name="summary" value="<?= htmlspecialchars($invoice->summary) ?>" required>
    </label>
    <label>Client
      <select name="client_id" required>
        <option value="" hidden>Select a client</option>
        <?php $clients ??= [];
        foreach ($clients as $client): ?>
          <option value="<?= $client->id ?>" <?= $invoice->client_id == $client->id ? 'selected' : '' ?>>
            <?= htmlspecialchars($client->name) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
  </div>
  <label>Details
    <textarea name="details" rows="<?= substr_count($invoice->details ?? '', "\n") + 1 ?>"
      style="width:100%;max-height:50dvh"
      oninput="this.rows = this.value.split('\n').length"><?= htmlspecialchars($invoice->details ?? '') ?></textarea>
  </label>
  <div class="flex spread">
    <label>Labor Amount
      <input type="number" step="0.01" name="labor_amount" value="<?= htmlspecialchars($invoice->labor_amount ?? 0) ?>">
    </label>
    <label>Due Date
      <input type="date" name="due_date" value="<?= htmlspecialchars($invoice->due_date ?? '') ?>">
    </label>
    <label>Paid Date
      <input type="date" name="paid_date" value="<?= htmlspecialchars($invoice->paid_date ?? '') ?>">
    </label>
    <label>Paid Amount <small role="link" onclick="this.nextElementSibling.value = <?= $invoice->total_amount ?>">Set to
        Total</small>
      <input type="number" step="0.01" name="paid_amount" value="<?= htmlspecialchars($invoice->paid_amount ?? 0) ?>">
    </label>
  </div>
  <div class="flex spaced mb-sm">
    <label>Itemizations</label>
    <?php if (in_array(\App\Permissions::EDIT_INVOICE, $permissions)): ?>
      <button type="button" onclick="addRow()" class="success">Add Item</button>
    <?php endif; ?>
  </div>
  <div class="overflow-auto">
    <table>
      <thead>
        <tr>
          <th>Item Summary</th>
          <th>Quantity</th>
          <th>Price</th>
          <th>Total</th>
          <th>Expense?</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($invoice->items ?? [] as $key => $item): ?>
          <tr id="<?= $item->id ?>">
            <td>
              <input type="hidden" name="items[<?= $key ?>][id]" value="<?= $item->id ?>">
              <input style="width:100%;min-width:100px" type="text" name="items[<?= $key ?>][summary]"
                value="<?= htmlspecialchars($item->summary) ?>" required>
            </td>
            <td>
              <input style="width:100%" type="number" step="1" name="items[<?= $key ?>][quantity]"
                value="<?= htmlspecialchars($item->quantity) ?>" required>
            </td>
            <td data-align="right">
              <input style="width:100%" type="number" step="0.01" name="items[<?= $key ?>][unit_price]"
                value="<?= htmlspecialchars($item->unit_price) ?>" required>
            </td>
            <td data-align="right">
              <?= \App\Utils::to_currency($item->total_amount) ?>
            </td>
            <td data-align="center">
              <input type="hidden" name="items[<?= $key ?>][is_expense]" value="<?= $item->is_expense ? 1 : 0 ?>">
              <input type="checkbox" onchange="this.previousElementSibling.value = this.checked ? 1 : 0"
                <?= $item->is_expense ? 'checked' : '' ?>>
            </td>
            <td>
              <?php if (in_array(\App\Permissions::EDIT_INVOICE, $permissions)): ?>
                <button type="button" class="danger" style="min-width:100%"
                  onclick="document.getElementById('<?= $item->id ?>').remove()">Remove</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</form>
<table>
  <tbody>
    <?php if ($invoice->total_expenses): ?>
      <tr>
        <th>Total Amount (Expected Revenue)</th>
        <td data-align="right">
          <b><?= \App\Utils::to_currency($invoice->total_amount ?? 0) ?></b>
        </td>
      </tr>
      <tr>
        <th>Total Expenses</th>
        <td data-align="right">
          <b><?= \App\Utils::to_currency($invoice->total_expenses ?? 0) ?></b>
        </td>
      </tr>
      <tr>
        <th>Expected Income</th>
        <td data-align="right">
          <b><?= \App\Utils::to_currency(($invoice->total_amount ?? 0) - ($invoice->total_expenses ?? 0)) ?></b>
        </td>
      </tr>
    <?php else: ?>
      <tr>
        <th>Total Amount (Expected Revenue)</th>
        <td data-align="right">
          <b><?= \App\Utils::to_currency($invoice->total_amount ?? 0) ?></b>
        </td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>
<?php if (in_array(\App\Permissions::DELETE_INVOICE, $permissions)): ?>
  <dialog id="deleteModal">
    <header class="mb-sm"><b>Delete Invoice</b></header>
    <form method="POST" action="/invoices/<?= $invoice->id ?>/delete">
      <p>Are you sure you want to delete this invoice?</p>
      <div class="flex spread">
        <button type="button" onclick="deleteModal.close()">Cancel</button>
        <button type="submit" class="danger">Delete</button>
      </div>
    </form>
  </dialog>
<?php endif; ?>
<script>
  function addRow() {
    const table = document.querySelector('table tbody');
    const rowCount = table.rows.length;
    const row = table.insertRow();
    row.innerHTML = `
      <td>
        <input type="hidden" name="items[${rowCount}][id]" value="">
        <input style="width:100%;min-width:100px" type="text" name="items[${rowCount}][summary]" value="" required>
      </td>
      <td>
        <input style="width:100%;min-width:100px" type="number" step="1" name="items[${rowCount}][quantity]" value="1" required>
      </td>
      <td data-align="right">
        <input style="width:100%;min-width:100px" type="number" step="0.01" name="items[${rowCount}][unit_price]" value="0.00" required>
      </td>
      <td data-align="right">
        --
      </td>
      <td data-align="center">
        <input type="hidden" name="items[${rowCount}][is_expense]" value="0">
        <input type="checkbox" onchange="this.previousElementSibling.value = this.checked ? 1 : 0"
          name="items[${rowCount}][is_expense]" value="1">
      </td>
      <td>
        <button type="button" class="danger" style="width:100%" onclick="this.closest('tr').remove()">Remove</button>
      </td>`;
  }
</script>