<div class="flex spaced mb-1">
  <h2 class="m-0">Settings</h2>
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
      <label><?= ucwords(str_replace('_', ' ', $key)) ?>
        <?php if ($key == 'invoice_template'): ?>
          <details>
            <summary><small><b>Help</b></small></summary>
            <section>
              <p>The invoice template defines how invoices are displayed when printed or exported using HTML or <a
                  href="https://www.markdownguide.org/cheat-sheet/" target="_blank">Markdown</a> to customize the
                layout, styling, and content of the invoice.</p>
              <p><b>Placeholders</b> can be used to reference data using the syntax <code>{{ record.property }}</code>,
                i.e. <code>{{ invoice.number }}</code> displays the invoice number.
              </p>
              <p><b>Modifiers</b> can be applied to placeholders to transform their values:</p>
              <ul>
                <li><code>{{ record.property|upper }}</code> will convert the value to uppercase</li>
                <li><code>{{ record.property|lower }}</code> will convert the value to lowercase</li>
                <li><code>{{ record.property|date }}</code> will format the date value as M/D/YYYY</li>
                <li><code>{{ record.property|currency }}</code> will format the value as currency</li>
                <li><code>{{ record.property|ucwords }}</code> will capitalize the first letter of each word</li>
              </ul>
              <p>There are also some <b>pre-defined placeholders</b> available:</p>
              <ul>
                <li><code>{{ current_date }}</code> will insert the current date in the format M/D/YYYY</li>
                <li><code>{{ invoice_items }}</code> will insert the list of invoice items as a pre-formatted table</li>
              </ul>
              <p>For more advanced layouts, you can use any other modern HTML and CSS syntax.</p>
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