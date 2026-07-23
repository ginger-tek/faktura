<div class="flex stack align-center spaced mb-1">
  <h2 class="m-0">Annual Report</h2>
  <form method="GET" class="flex">
    <button type="button" class="info"
      onclick="this.nextElementSibling.value = '<?= $previous_year ?>'; this.form.submit()">Prev.</button>
    <input style="width:150px" type="number" step="1" name="filter_year" value="<?= $filter_year ?? date('Y') ?>"
      onchange="this.form.submit()">
    <button type="button" class="info"
      onclick="this.previousElementSibling.value = '<?= $next_year ?>'; this.form.submit()">Next</button>
    <?php if ($filter_year != date('Y')): ?>
      <button type="button" onclick="window.location.href='/reports/annual'" class="info">Reset</button>
    <?php endif; ?>
  </form>
</div>
<legend data-align="center">
  <label
    onclick="document.querySelectorAll('.revenue').forEach(el => el.checked = !el.checked); this.classList.toggle('inactive')"><span
      class="legend-square bg-blue"></span> Revenue</label>
  <label
    onclick="document.querySelectorAll('.expenses').forEach(el => el.checked = !el.checked); this.classList.toggle('inactive')"><span
      class="legend-square bg-red"></span> Expenses</label>
  <label
    onclick="document.querySelectorAll('.expected_income').forEach(el => el.checked = !el.checked); this.classList.toggle('inactive')"><span
      class="legend-square bg-yellow"></span> Expected Income</label>
  <label
    onclick="document.querySelectorAll('.paid_income').forEach(el => el.checked = !el.checked); this.classList.toggle('inactive')"><span
      class="legend-square bg-green"></span> Paid Income</label>
</legend>
<div class="chart-wrap">
  <div class="chart">
    <div class="scale">
      <?php for ($i = 0; $i <= $chart['max']; $i += $chart['max'] / 5): ?>
        <small class="scale-label"><?= \App\Utils::to_currency($i, $i > 1 ? true : false) ?></small>
      <?php endfor; ?>
    </div>
    <?php foreach ($chart['months'] as $slot): ?>
      <div class="slot <?= $slot['date']->format('Y-m') === date('Y-m') ? 'today' : '' ?>"
        title="<?= $slot['date']->format('F Y') . ' Income: ' . \App\Utils::to_currency($slot['paid_income']) ?>">
        <div class="bars">
          <input type="checkbox" class="revenue" hidden checked>
          <div class="bar bg-blue" style="height:<?= $slot['revenue'] / $chart['max'] * 100 ?>%"
            title="Revenue: <?= \App\Utils::to_currency($slot['revenue']) ?>"></div>
          <input type="checkbox" class="expenses" hidden checked>
          <div class="bar bg-red" style="height:<?= $slot['expenses'] / $chart['max'] * 100 ?>%"
            title="Expenses: <?= \App\Utils::to_currency($slot['expenses']) ?>"></div>
          <input type="checkbox" class="expected_income" hidden checked>
          <div class="bar bg-yellow" style="height:<?= $slot['expected_income'] / $chart['max'] * 100 ?>%"
            title="Expected Income: <?= \App\Utils::to_currency($slot['expected_income']) ?>"></div>
          <input type="checkbox" class="paid_income" hidden checked>
          <div class="bar bg-green" style="height:<?= $slot['paid_income'] / $chart['max'] * 100 ?>%"
            title="Paid Income: <?= \App\Utils::to_currency($slot['paid_income']) ?>"></div>
        </div>
        <div class="label"><?= $slot['date']->format('M') ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<h3>Summary</h3>
<table>
  <tbody>
    <tr>
      <th>Number of Invoices</th>
      <td data-align="right">
        <b><?= $summary['number_of_invoices'] ?></b>
      </td>
    </tr>
    <tr>
      <th>Revenue</th>
      <td data-align="right">
        <b><?= \App\Utils::to_currency($summary['total_revenue']) ?></b>
      </td>
    </tr>
    <tr>
      <th>Expenses</th>
      <td data-align="right">
        <b><?= \App\Utils::to_currency($summary['total_expenses']) ?></b>
      </td>
    </tr>
    <tr>
      <th>Expected Income</th>
      <td data-align="right">
        <b><?= \App\Utils::to_currency($summary['total_expected_income']) ?></b>
      </td>
    </tr>
    <tr>
      <th>Paid Income</th>
      <td data-align="right">
        <b><?= \App\Utils::to_currency($summary['total_paid_income']) ?></b>
      </td>
    </tr>
  </tbody>
</table>
<script>
  window.innerWidth < 700 && [...document.querySelectorAll('.bar')].find(el => el.style.height != "0%")?.scrollIntoView({
    inline: "center",
    block: "nearest",
    behavior: "smooth"
  });
</script>