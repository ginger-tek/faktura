<div class="flex spaced mb-1">
  <h3 class="m-0">Users</h3>
  <div>
    <?php if (in_array(\App\Permissions::EDIT_USERS, $permissions)): ?>
      <button type="submit" form="users" class="success">Save All</button>
    <?php endif; ?>
    <?php if (in_array(\App\Permissions::CREATE_USER, $permissions)): ?>
      <button type="button" class="success" onclick="newUserModal.showModal()">New User</button>
    <?php endif; ?>
  </div>
</div>
<form id="users" method="POST" action="/users">
  <div class="overflow-auto">
    <table>
      <thead>
        <tr>
          <th>Username</th>
          <th>Active?</th>
          <th>Permissions</th>
          <th>Created At</th>
          <th>Updated At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $key => $user): ?>
          <tr>
            <td>
              <input type="hidden" name="users[<?= $key ?>][id]" value="<?= $user->id ?>">
              <input type="text" style="width:100%" name="users[<?= $key ?>][username]" value="<?= $user->username ?>"
                required>
            </td>
            <td data-align="center">
              <?php if ($user->id == $app->getCtx('user')->id): ?>
                <label title="You cannot deactivate your own account">
                  <input type="hidden" name="users[<?= $key ?>][is_active]" value="1">
                  <input type="checkbox" checked disabled>
                </label>
              <?php else: ?>
                <label>
                  <input type="hidden" name="users[<?= $key ?>][is_active]" value="<?= $user->is_active ? 1 : 0 ?>">
                  <input type="checkbox" onchange="this.previousElementSibling.value = this.checked ? 1 : 0"
                    <?= $user->is_active ? 'checked' : '' ?>>
                </label>
              <?php endif; ?>
            </td>
            <td>
              <input type="hidden" name="users[<?= $key ?>][permissions_bit]" value="<?= $user->permissions_bit ?>">
              <button type="button" onclick="loadPermissions('<?= $key ?>'); permissionsModal.showModal()">
                Permissions
              </button>
            </td>
            <td><?= $user->created_at ? date('n/j/Y g:i A', $user->created_at) : '--' ?></td>
            <td><?= $user->updated_at ? date('n/j/Y g:i A', $user->updated_at) : '--' ?></td>
            <td>
              <?php if (in_array(\App\Permissions::EDIT_USERS, $permissions)): ?>
                <button type="button" class="info"
                  onclick="logoutUserForm.user_id.value = '<?= $user->id ?>'; logoutUserModal.showModal()">Logout</button>
              <?php endif; ?>
              <?php if ($user->id != $app->getCtx('user')->id && in_array(\App\Permissions::DELETE_USER, $permissions)): ?>
                <button type="button" class="danger"
                  onclick="deleteUserForm.user_id.value = '<?= $user->id ?>'; deleteUserModal.showModal()">Delete</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</form>
<?php if (in_array(\App\Permissions::CREATE_USER, $permissions)): ?>
  <dialog id="newUserModal">
    <header class="mb-sm"><b>New User</b></header>
    <form method="POST" action="/users/new">
      <label>Username
        <input type="text" name="username" required>
      </label>
      <label>Password
        <input type="password" name="password" required>
      </label>
      <div class="flex spread">
        <button type="submit" value="new" class="success">Create User</button>
        <button type="button" onclick="newUserModal.close()">Cancel</button>
      </div>
    </form>
  </dialog>
<?php endif; ?>
<?php if (in_array(\App\Permissions::EDIT_USERS, $permissions)): ?>
  <dialog id="logoutUserModal">
    <header class="mb-sm"><b>Logout User</b></header>
    <form action="/users/logout" method="POST" id="logoutUserForm">
      <input type="hidden" name="user_id">
      <p>This will logout this user from everywhere. Do you want to continue?</p>
      <div class="flex spread">
        <button type="submit" class="success">Logout This User</button>
        <button type="button" onclick="logoutUserModal.close()">Cancel</button>
      </div>
    </form>
  </dialog>
<?php endif; ?>
<?php if (in_array(\App\Permissions::DELETE_USER, $permissions)): ?>
  <dialog id="deleteUserModal">
    <header class="mb-sm"><b>Delete User</b></header>
    <form method="POST" action="/users/delete" id="deleteUserForm">
      <input type="hidden" name="user_id">
      <p>Are you sure you want to delete this user?</p>
      <div class="flex spread">
        <button type="submit" class="danger">Delete</button>
        <button type="button" onclick="deleteUserModal.close()">Cancel</button>
      </div>
    </form>
  </dialog>
<?php endif; ?>
<dialog id="permissionsModal">
  <header class="mb-sm"><b>Permissions</b></header>
  <form method="dialog">
    <input type="hidden" name="index">
    <ul columns="2">
      <?php foreach (\App\Permissions::list() as $name => $bit): ?>
        <li>
          <label style="width:200px">
            <input type="checkbox" name="permissions[]" value="<?= $bit ?>">
            <?= $name ?>
          </label>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="flex spread">
      <button type="submit" value="set" class="success">Set</button>
      <button type="button" onclick="permissionsModal.close()">Cancel</button>
    </div>
  </form>
</dialog>
<script>
  function loadPermissions(index) {
    const form = permissionsModal.querySelector('form');
    form.index.value = index;
    const permissionsBit = parseInt(document.querySelector(`input[name="users[${index}][permissions_bit]"]`)?.value ?? 0);
    const checkboxes = form.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
      checkbox.checked = (permissionsBit & parseInt(checkbox.value)) === parseInt(checkbox.value);
    });
  }

  permissionsModal?.addEventListener('close', () => {
    if (permissionsModal.returnValue !== 'set')
      return;
    const form = permissionsModal.querySelector('form');
    const checkboxes = form.querySelectorAll('input[type="checkbox"]');
    let permissionsBit = 1;
    checkboxes.forEach(checkbox => {
      if (checkbox.checked)
        permissionsBit |= parseInt(checkbox.value);
    });
    document.querySelector(`input[name="users[${form.index.value}][permissions_bit]"]`).value = permissionsBit;
  });
</script>