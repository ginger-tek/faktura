<div class="flex spaced mb-1">
  <h3 class="m-0">Client: <?= $client->name ?></h3>
  <div class="flex">
    <?php if (in_array(\App\Permissions::EDIT_CLIENT, $permissions)): ?>
      <button type="submit" form="client" class="success">Save</button>
    <?php endif; ?>
    <?php if (in_array(\App\Permissions::DELETE_CLIENT, $permissions)): ?>
      <button type="button" class="danger delete" onclick="deleteModal.showModal()">Delete</button>
    <?php endif; ?>
  </div>
</div>
<form id="client" method="POST" action="/clients/<?= $client->id ?>">
  <div class="flex spread">
    <label>Name
      <input type="text" name="name" value="<?= htmlspecialchars($client->name) ?>" required>
    </label>
    <label>Email
      <input type="email" name="email" value="<?= htmlspecialchars($client->email) ?>" required>
    </label>
    <label>Phone
      <input type="tel" name="phone" value="<?= htmlspecialchars($client->phone) ?>">
    </label>
  </div>
  <label>Address
    <textarea name="address" rows="<?= substr_count($client->address ?? '', "\n") + 1 ?>"
      style="width:100%;max-height:50dvh"
      oninput="this.rows = this.value.split('\n').length"><?= htmlspecialchars($client->address ?? '') ?></textarea>
  </label>
</form>
<?php if (in_array(\App\Permissions::DELETE_CLIENT, $permissions)): ?>
  <dialog id="deleteModal">
    <header class="mb-sm"><b>Delete Client</b></header>
    <form method="POST" action="/clients/<?= $client->id ?>/delete">
      <p>Are you sure you want to delete this client?</p>
      <div class="flex spread">
        <button type="submit" class="danger">Delete</button>
        <button type="button" onclick="deleteModal.close()">Cancel</button>
      </div>
    </form>
  </dialog>
<?php endif; ?>