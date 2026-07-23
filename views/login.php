<div style="max-inline-size:250px;margin-inline:auto">
  <?php if (!empty($error)): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
  <form method="POST" action="/login<?= $redirect ? "?redirect=$redirect" : '' ?>">
    <label>Username
      <input type="text" name="username" autocapitalize="off" required>
    </label>
    <label>Password
      <input type="password" name="password" required>
    </label>
    <button type="submit" style="width:100%">Login</button>
  </form>
</div>