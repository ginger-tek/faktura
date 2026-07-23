<h2 class="mb-1">Dashboard</h2>
<div class="flex stack spread stack-stretch" data-gap="1">
  <div>
    <div class="flex spaced align-center mb-1">
      <h3 class="m-0"><?= $data['year']['date']->format('Y') ?> Year-to-Date</h3>
      <a href="/reports/annual">Annual Report</a>
    </div>
    <table>
      <tbody>
        <tr>
          <th>Paid Income</th>
          <td><b><?= \App\Utils::to_currency($data['year']['total_income']) ?></b></td>
        </tr>
        <tr>
          <th>Expenses</th>
          <td><b><?= \App\Utils::to_currency($data['year']['total_expenses']) ?></b></td>
        </tr>
        <tr>
          <th>Upcoming Income</th>
          <td><b><?= \App\Utils::to_currency($data['year']['upcoming_income']) ?></b></td>
        </tr>
      </tbody>
    </table>
  </div>
  <div>
    <div class="flex spaced align-center mb-1">
      <h3 class="m-0"><?= $data['month']['date']->format('F Y') ?></h3>
      <a href="/reports/month">Monthly Report</a>
    </div>
    <table>
      <tbody>
        <tr>
          <th>Paid Income</th>
          <td><b><?= \App\Utils::to_currency($data['month']['total_income']) ?></b></td>
        </tr>
        <tr>
          <th>Expenses</th>
          <td><b><?= \App\Utils::to_currency($data['month']['total_expenses']) ?></b></td>
        </tr>
        <tr>
          <th>Upcoming Income</th>
          <td><b><?= \App\Utils::to_currency($data['month']['upcoming_income']) ?></b></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>