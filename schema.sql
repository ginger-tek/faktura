CREATE TABLE IF NOT EXISTS orgs (
  id CHAR(36) PRIMARY KEY,
  org_code CHAR(36) NOT NULL UNIQUE,
  display_name VARCHAR(255) NOT NULL,
  logo TEXT,
  created_at BIGINT,
  updated_at BIGINT
);

CREATE TABLE IF NOT EXISTS roles (
  id CHAR(36) PRIMARY KEY,
  org_id CHAR(36) NOT NULL,
  role_name VARCHAR(255) NOT NULL,
  bit_value BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at BIGINT,
  updated_at BIGINT,
  created_by CHAR(36),
  updated_by CHAR(36),
  UNIQUE KEY uq_roles_org_role_name (org_id, role_name),
  CONSTRAINT fk_roles_org FOREIGN KEY (org_id) REFERENCES orgs (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS users (
  id CHAR(36) PRIMARY KEY,
  org_id CHAR(36) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  display_name VARCHAR(255) NOT NULL,
  username VARCHAR(191) NOT NULL UNIQUE,
  passhash VARCHAR(255) NOT NULL,
  role_id CHAR(36),
  created_at BIGINT,
  updated_at BIGINT,
  created_by CHAR(36),
  updated_by CHAR(36),
  CONSTRAINT fk_users_org FOREIGN KEY (org_id) REFERENCES orgs (id) ON DELETE CASCADE,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS org_settings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  org_id CHAR(36) NOT NULL,
  setting_key VARCHAR(191) NOT NULL,
  setting_value TEXT,
  created_at BIGINT,
  updated_at BIGINT,
  created_by CHAR(36),
  updated_by CHAR(36),
  PRIMARY KEY (id),
  UNIQUE KEY uq_org_settings_org_key (org_id, setting_key),
  CONSTRAINT fk_org_settings_org FOREIGN KEY (org_id) REFERENCES orgs (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS clients (
  id CHAR(36) PRIMARY KEY,
  org_id CHAR(36) NOT NULL,
  full_name VARCHAR(255) NOT NULL,
  contact_email VARCHAR(255) NOT NULL,
  contact_phone VARCHAR(64),
  contact_address TEXT,
  created_at BIGINT,
  updated_at BIGINT,
  created_by CHAR(36),
  updated_by CHAR(36),
  CONSTRAINT fk_clients_org FOREIGN KEY (org_id) REFERENCES orgs (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS invoices (
  id CHAR(13) PRIMARY KEY,
  org_id CHAR(36) NOT NULL,
  client_id CHAR(36) NOT NULL,
  summary VARCHAR(255) NOT NULL,
  details TEXT,
  labor_hours INT NOT NULL DEFAULT 1,
  labor_rate DOUBLE NOT NULL DEFAULT 0.00,
  due_date CHAR(10),
  paid_date CHAR(10),
  paid_amount DOUBLE NOT NULL DEFAULT 0.00,
  created_at BIGINT,
  updated_at BIGINT,
  created_by CHAR(36),
  updated_by CHAR(36),
  UNIQUE KEY uq_invoices_org_id (org_id, id),
  CONSTRAINT fk_invoices_org FOREIGN KEY (org_id) REFERENCES orgs (id) ON DELETE CASCADE,
  CONSTRAINT fk_invoices_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS expenses (
  id CHAR(36) PRIMARY KEY,
  org_id CHAR(36) NOT NULL,
  summary VARCHAR(255) NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  unit_price DOUBLE NOT NULL,
  purchase_date CHAR(10),
  created_at BIGINT,
  updated_at BIGINT,
  created_by CHAR(36),
  updated_by CHAR(36),
  CONSTRAINT fk_expenses_org FOREIGN KEY (org_id) REFERENCES orgs (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS invoice_expenses (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  org_id CHAR(36) NOT NULL,
  invoice_id CHAR(13) NOT NULL,
  expense_id CHAR(36) NOT NULL,
  created_at BIGINT,
  updated_at BIGINT,
  created_by CHAR(36),
  updated_by CHAR(36),
  PRIMARY KEY (id),
  UNIQUE KEY uq_invoice_expenses_org_invoice_expense (org_id, invoice_id, expense_id),
  CONSTRAINT fk_invoice_expenses_invoice FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE CASCADE,
  CONSTRAINT fk_invoice_expenses_expense FOREIGN KEY (expense_id) REFERENCES expenses (id) ON DELETE CASCADE,
  CONSTRAINT fk_invoice_expenses_org FOREIGN KEY (org_id) REFERENCES orgs (id) ON DELETE CASCADE
);

DROP VIEW IF EXISTS v_org_settings;

CREATE VIEW v_org_settings AS
SELECT
  os.id,
  os.org_id,
  os.setting_key,
  os.setting_value,
  os.created_at,
  CONCAT(cu.display_name, '|', cu.username) AS created_by,
  os.updated_at,
  CONCAT(uu.display_name, '|', uu.username) AS updated_by
FROM org_settings os
LEFT JOIN users cu ON os.created_by = cu.id
LEFT JOIN users uu ON os.updated_by = uu.id;

DROP VIEW IF EXISTS v_org_settings_list;

CREATE VIEW v_org_settings_list AS
SELECT
  os.id,
  os.org_id,
  os.setting_key,
  CASE
    WHEN LENGTH(os.setting_value) > 15 THEN CONCAT(SUBSTRING(os.setting_value, 1, 15), '...')
    ELSE os.setting_value
  END AS setting_value,
  os.created_at,
  CONCAT(cu.display_name, '|', cu.username) AS created_by,
  os.updated_at,
  CONCAT(uu.display_name, '|', uu.username) AS updated_by
FROM org_settings os
LEFT JOIN users cu ON os.created_by = cu.id
LEFT JOIN users uu ON os.updated_by = uu.id;

DROP VIEW IF EXISTS v_users;

CREATE VIEW v_users AS
SELECT
  u.id,
  u.org_id,
  o.display_name AS org_display_name,
  u.display_name,
  u.username,
  u.active,
  u.role_id,
  r.role_name,
  r.bit_value AS role_bit_value,
  u.created_at,
  CONCAT(cu.display_name, '|', cu.username) AS created_by,
  u.updated_at,
  CONCAT(uu.display_name, '|', uu.username) AS updated_by
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
LEFT JOIN orgs o ON u.org_id = o.id
LEFT JOIN users cu ON u.created_by = cu.id
LEFT JOIN users uu ON u.updated_by = uu.id;

DROP VIEW IF EXISTS v_users_full;

CREATE VIEW v_users_full AS
SELECT
  u.id,
  u.org_id,
  o.display_name AS org_display_name,
  u.display_name,
  u.username,
  u.passhash,
  u.role_id,
  r.role_name,
  r.bit_value AS role_bit_value,
  u.created_at,
  CONCAT(cu.display_name, '|', cu.username) AS created_by,
  u.updated_at,
  CONCAT(uu.display_name, '|', uu.username) AS updated_by
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
LEFT JOIN orgs o ON u.org_id = o.id
LEFT JOIN users cu ON u.created_by = cu.id
LEFT JOIN users uu ON u.updated_by = uu.id;

DROP VIEW IF EXISTS v_expenses;

CREATE VIEW v_expenses AS
SELECT
  e.id,
  e.org_id,
  e.summary,
  e.quantity,
  e.unit_price,
  (e.quantity * e.unit_price) AS total_amount,
  e.purchase_date,
  e.created_at,
  CONCAT(cu.display_name, '|', cu.username) AS created_by,
  e.updated_at,
  CONCAT(uu.display_name, '|', uu.username) AS updated_by
FROM expenses e
LEFT JOIN users cu ON e.created_by = cu.id
LEFT JOIN users uu ON e.updated_by = uu.id;

DROP VIEW IF EXISTS v_invoice_itemizations;

CREATE VIEW v_invoice_itemizations AS
SELECT
  ie.id,
  ie.org_id,
  ie.invoice_id,
  e.id AS expense_id,
  e.summary,
  e.quantity,
  e.unit_price,
  e.total_amount,
  e.purchase_date,
  e.created_at AS created_at,
  CONCAT(cu.display_name, '|', cu.username) AS created_by,
  e.updated_at AS updated_at,
  CONCAT(uu.display_name, '|', uu.username) AS updated_by
FROM invoice_expenses ie
JOIN v_expenses e ON ie.expense_id = e.id
LEFT JOIN users cu ON e.created_by = cu.id
LEFT JOIN users uu ON e.updated_by = uu.id;

DROP VIEW IF EXISTS v_invoices;

CREATE VIEW v_invoices AS
SELECT
  i.id,
  i.org_id,
  i.client_id,
  c.full_name AS client_full_name,
  i.summary,
  i.details,
  i.labor_hours,
  i.labor_rate,
  (i.labor_hours * i.labor_rate) AS labor_amount,
  SUM(COALESCE(ie.total_amount, 0)) AS expense_amount,
  ((i.labor_hours * i.labor_rate) + SUM(COALESCE(ie.total_amount, 0))) AS total_amount,
  i.paid_amount,
  i.due_date,
  i.paid_date,
  i.created_at,
  CONCAT(cu.display_name, '|', cu.username) AS created_by,
  i.updated_at,
  CONCAT(uu.display_name, '|', uu.username) AS updated_by
FROM invoices i
JOIN clients c ON i.client_id = c.id
LEFT JOIN v_invoice_itemizations ie ON i.id = ie.invoice_id
LEFT JOIN users cu ON i.created_by = cu.id
LEFT JOIN users uu ON i.updated_by = uu.id
GROUP BY
  i.id,
  i.org_id,
  i.client_id,
  c.full_name,
  i.summary,
  i.details,
  i.labor_hours,
  i.labor_rate,
  i.due_date,
  i.paid_date,
  i.paid_amount,
  i.created_at,
  cu.display_name,
  cu.username,
  i.updated_at,
  uu.display_name,
  uu.username;

DROP VIEW IF EXISTS v_list_invoices;

CREATE VIEW v_list_invoices AS
SELECT
  i.id,
  i.org_id,
  i.client_id,
  c.full_name AS client_full_name,
  i.summary,
  (i.labor_hours * i.labor_rate) AS labor_amount,
  SUM(COALESCE(ie.total_amount, 0)) AS expense_amount,
  ((i.labor_hours * i.labor_rate) + SUM(COALESCE(ie.total_amount, 0))) AS total_amount,
  i.due_date,
  i.paid_date,
  i.paid_amount,
  i.created_at,
  CONCAT(cu.display_name, '|', cu.username) AS created_by,
  i.updated_at,
  CONCAT(uu.display_name, '|', uu.username) AS updated_by
FROM invoices i
JOIN clients c ON i.client_id = c.id
LEFT JOIN v_invoice_itemizations ie ON i.id = ie.invoice_id
LEFT JOIN users cu ON i.created_by = cu.id
LEFT JOIN users uu ON i.updated_by = uu.id
GROUP BY
  i.id,
  i.org_id,
  i.client_id,
  c.full_name,
  i.summary,
  i.labor_hours,
  i.labor_rate,
  i.due_date,
  i.paid_date,
  i.paid_amount,
  i.created_at,
  cu.display_name,
  cu.username,
  i.updated_at,
  uu.display_name,
  uu.username;

DROP VIEW IF EXISTS v_clients;

CREATE VIEW v_clients AS
SELECT
  c.id,
  c.org_id,
  c.full_name,
  c.contact_email,
  c.contact_phone,
  c.contact_address,
  c.created_at,
  CONCAT(cu.display_name, '|', cu.username) AS created_by,
  c.updated_at,
  CONCAT(uu.display_name, '|', uu.username) AS updated_by
FROM clients c
LEFT JOIN users cu ON c.created_by = cu.id
LEFT JOIN users uu ON c.updated_by = uu.id;

DROP VIEW IF EXISTS v_roles;

CREATE VIEW v_roles AS
SELECT
  r.id,
  r.org_id,
  r.role_name,
  r.bit_value,
  r.created_at,
  CONCAT(cu.display_name, '|', cu.username) AS created_by,
  r.updated_at,
  CONCAT(uu.display_name, '|', uu.username) AS updated_by
FROM roles r
LEFT JOIN users cu ON r.created_by = cu.id
LEFT JOIN users uu ON r.updated_by = uu.id;