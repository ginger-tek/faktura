<h4>My Account</h4>
<p>Updating your password will log you out from all devices and require you to log back in with the new password.</p>
<form method="POST" action="/account">
  <label>Current Password
    <input type="password" name="current_password" required>
  </label>
  <label>New Password
    <input type="password" name="new_password" required>
  </label>
  <label>Confirm New Password
    <input type="password" name="confirm_password" required>
  </label>
  <div class="flex spread">
    <button type="submit" class="success">Update Password</button>
  </div>
</form>