<div class="flex spaced mb-1">
  <h3 class="m-0">Expenses</h3>
  <form method="GET">
    <select name="sort" onchange="this.form.submit()">
      <option value="" hidden>Sort By</option>
      <?php foreach ($sort_options as $label => $value): ?>
        <option value="<?= $value ?>" <?= $sort === $value ? 'selected' : '' ?>>
          <?= $label ?>
        </option>
      <?php endforeach; ?>
    </select>
    <?php if ($sort): ?>
      <button type="button" onclick="window.location.href = '/expenses'" class="info">Reset</button>
    <?php endif; ?>
  </form>
</div>
<div class="overflow-auto">
  <table>
    <thead>
      <tr>
        <th>Summary</th>
        <th>Invoice</th>
        <th>Total Amount</th>
        <th>Created At</th>
        <th>Updated At</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($expenses as $expense): ?>
        <tr>
          <td><?= $expense->summary ?></td>
          <td><a href="/invoices/<?= $expense->invoice_id ?>"><?= $expense->invoice_summary ?></a></td>
          <td data-align="right"><?= \App\Utils::to_currency($expense->total_amount) ?></td>
          <td><?= $expense->created_at ? date('n/j/Y', $expense->created_at) : '--' ?></td>
          <td><?= $expense->updated_at ? date('n/j/Y', $expense->updated_at) : '--' ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>