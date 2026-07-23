<div class="flex spaced mb-1">
  <h2 class="m-0">Clients</h2>
  <form method="GET" class="flex">
    <select name="sort" onchange="this.form.submit()">
      <option value="" hidden>Sort By</option>
      <?php foreach ($sort_options as $label => $value): ?>
        <option value="<?= $value ?>" <?= $sort === $value ? 'selected' : '' ?>>
          <?= $label ?>
        </option>
      <?php endforeach; ?>
    </select>
    <?php if ($sort): ?>
      <button type="button" onclick="window.location.href = '/clients'" class="info">Reset</button>
    <?php endif; ?>
    <?php if (in_array(\App\Permissions::CREATE_CLIENT, $permissions)): ?>
      <button type="button" onclick="newClientModal.showModal()" class="success">New Client</button>
    <?php endif; ?>
  </form>
</div>
<div class="overflow-auto">
  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Created At</th>
        <th>Updated At</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($clients as $client): ?>
        <tr>
          <td><a href="/clients/<?= $client->id ?>"><?= htmlspecialchars($client->name) ?></a></td>
          <td><a href="mailto:<?= htmlspecialchars($client->email) ?>"><?= htmlspecialchars($client->email) ?></a></td>
          <td>
            <?= $client->phone ? '<a href="tel:' . htmlspecialchars($client->phone) . '">' . htmlspecialchars($client->phone) . '</a>' : '--' ?>
          </td>
          <td><?= $client->created_at ? date('n/j/Y', $client->created_at) : '--' ?></td>
          <td><?= $client->updated_at ? date('n/j/Y', $client->updated_at) : '--' ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php if (in_array(\App\Permissions::CREATE_CLIENT, $permissions)): ?>
  <dialog id="newClientModal">
    <header class="mb-sm"><b>New Client</b></header>
    <form method="POST" action="/clients">
      <label>Name
        <input type="text" name="name" required>
      </label>
      <label>Email
        <input type="email" name="email" required>
      </label>
      <label>Phone
        <input type="tel" name="phone">
      </label>
      <div class="flex spread">
        <button type="button" onclick="this.closest('form').reset(); newClientModal.close()">Cancel</button>
        <button type="submit" class="success">Create</button>
      </div>
    </form>
  </dialog>
<?php endif; ?>