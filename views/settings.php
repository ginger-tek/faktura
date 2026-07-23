<div class="flex spaced mb-1">
  <h3 class="m-0">Settings</h3>
  <?php if (in_array(\App\Permissions::EDIT_SETTINGS, $permissions)): ?>
    <button type="submit" form="settings-form" class="success">Save All</button>
  <?php endif; ?>
</div>
<form id="settings-form" method="POST" action="/settings">
  <?php foreach ($settings as $key => $value): ?>
    <?php if ($key == 'logo'): ?>
      <div>
        <label>Logo</label>
        <input type="hidden" name="settings[logo]" value="<?= htmlspecialchars($value) ?>">
        <div class="flex spread">
          <label title="Choose image">
            <input type="file" accept="image/*" onchange="convertImgToBase64(this, 'logo')" style="display:none">
            <div class="file-img-box">
              <img id="logo" src="<?= htmlspecialchars($value) ?>" alt="Logo"
                style="display:inline-block;height:5rem;width:auto">
            </div>
          </label>
        </div>
      </div>
    <?php else: ?>
      <label><?= str_replace('_', ' ', ucfirst($key)) ?>
        <?php if ($key == 'invoice_template'): ?>
          <details>
            <summary><small>Help</small></summary>
            <section>
              <p>The invoice template is a markup template that defines how invoices are displayed when printed or exported.
                You
                can customize the layout, styling, and content of the invoice by modifying this template.</p>
              <p>The template supports <a href="https://www.markdownguide.org/cheat-sheet/" target="_blank">Markdown</a>
                syntax for formatting text, and you can also include dynamic placeholders to insert invoice-specific data.</p>
              <p>To reference a value, use the syntax <code>{{ item.property }}</code>. For example, to display the invoice
                number, you can use <code>{{ invoice.number }}</code>.</p>
              <p>Predefined variables available in the template:</p>
              <ul>
                <li><code>{{ current_date }}</code> will insert the current date in the format M/D/YYYY</li>
                <li><code>{{ invoice_items }}</code> will insert the list of invoice items as pre-formatted table</li>
              </ul>
              <p>Predefined modifiers available in the template:</p>
              <ul>
                <li><code>{{ item.property|upper }}</code> will convert the value to uppercase</li>
                <li><code>{{ item.property|lower }}</code> will convert the value to lowercase</li>
                <li><code>{{ item.property|date }}</code> will format the date value as M/D/YYYY</li>
                <li><code>{{ item.property|currency }}</code> will format the value as currency</li>
              </ul>
              <p>For more advanced customization, you can use HTML and CSS within the template. Make sure to test your changes
                to ensure that the invoice displays correctly.</p>
            </section>
          </details>
        <?php endif; ?>
        <textarea name="settings[<?= $key ?>]" rows="<?= substr_count($value, "\n") + 1 ?>"
          style="width:100%;max-height:50dvh" <?= $key == 'invoice_template' ? 'data-code' : '' ?>
          oninput="this.rows = this.value.split('\n').length"><?= htmlspecialchars($value) ?></textarea>
      </label>
    <?php endif; ?>
  <?php endforeach; ?>
</form>
<script>
  function convertImgToBase64(input, hiddenInputName) {
    const file = input.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        document.querySelector(`input[name="settings[${hiddenInputName}]"]`).value = e.target.result;
        document.querySelector(`#${hiddenInputName}`).src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  }
</script>