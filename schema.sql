create table
  if not exists orgs (
    id text primary key,
    org_code text not null unique,
    display_name text not null,
    logo text,
    created_at integer default (unixepoch()),
    updated_at integer default (unixepoch())
  );

create table
  if not exists org_settings (
    id integer primary key,
    org_id text not null,
    setting_key text not null,
    setting_value text,
    created_at integer default (unixepoch()),
    updated_at integer default (unixepoch()),
    created_by text,
    updated_by text,
    unique (org_id, setting_key),
    foreign key (org_id) references orgs(id) on delete cascade
  );

create table
  if not exists users (
    id text primary key,
    org_id text not null,
    active integer default 1,
    display_name text not null,
    username text not null unique,
    passhash text not null,
    role_id text not null,
    created_at integer default (unixepoch()),
    updated_at integer default (unixepoch()),
    created_by text,
    updated_by text,
    foreign key (org_id) references orgs(id) on delete cascade,
    foreign key (role_id) references roles(id) on delete set null
  );

create table
  if not exists roles (
    id text primary key,
    org_id text not null,
    role_name text not null,
    bit_value integer default 0,
    created_at integer default (unixepoch()),
    updated_at integer default (unixepoch()),
    created_by text,
    updated_by text,
    unique (org_id, bit_value),
    foreign key (org_id) references orgs(id) on delete cascade
  );

create table
  if not exists clients (
    id text primary key,
    org_id text not null,
    full_name text not null,
    contact_email text not null,
    contact_phone text,
    contact_address text,
    created_at integer default (unixepoch()),
    updated_at integer default (unixepoch()),
    created_by text,
    updated_by text,
    foreign key (org_id) references orgs(id) on delete cascade
  );

create table
  if not exists invoices (
    id text primary key,
    org_id text not null,
    client_id text not null,
    summary text not null,
    details text,
    labor_hours int default 1,
    labor_rate decimal default 0.00,
    due_date integer,
    paid_date integer,
    created_at integer default (unixepoch()),
    updated_at integer default (unixepoch()),
    created_by text,
    updated_by text,
    unique (org_id, id),
    foreign key (org_id) references orgs(id) on delete cascade
  );

create table
  if not exists invoice_expenses (
    id int primary key,
    org_id text not null,
    invoice_id text not null,
    expense_id text not null,
    created_at integer default (unixepoch()),
    updated_at integer default (unixepoch()),
    created_by text,
    updated_by text,
    unique (org_id, invoice_id, expense_id),
    foreign key (invoice_id) references invoices(id) on delete cascade,
    foreign key (expense_id) references expenses(id) on delete cascade,
    foreign key (org_id) references orgs(id) on delete cascade
  );

create table
  if not exists expenses (
    id text primary key,
    org_id text not null,
    summary text not null,
    quantity integer default 1,
    unit_price decimal not null,
    purchase_date integer default (unixepoch()),
    created_at integer default (unixepoch()),
    updated_at integer default (unixepoch()),
    created_by text,
    updated_by text,
    foreign key (org_id) references orgs(id) on delete cascade
  );

drop view if exists v_org_settings;
create view v_org_settings as
select
  id,
  org_id,
  setting_key,
  case when length(setting_value) > 15
    then substr(setting_value, 1, 15) || '...'
    else setting_value end
  as setting_value,
  created_at,
  updated_at,
  created_by,
  updated_by
from org_settings;

drop view if exists v_users;
create view v_users as
select
  u.id,
  u.org_id,
  o.display_name as org_display_name,
  u.display_name,
  u.username,
  u.role_id,
  r.role_name,
  r.bit_value as role_bit_value,
  u.created_at,
  u.updated_at
from users u
left join roles r on u.role_id = r.id
left join orgs o on u.org_id = o.id
group by u.id;

drop view if exists v_invoices;
create view v_invoices as
select
  i.id,
  i.org_id,
  i.client_id,
  c.full_name as client_full_name,
  i.summary,
  i.details,
  i.labor_hours,
  i.labor_rate,
  (i.labor_hours * i.labor_rate) as labor_amount,
  sum(coalesce(ie.total_amount, 0)) as expense_amount,
  ((i.labor_hours * i.labor_rate) + sum(coalesce(ie.total_amount, 0))) as total_amount,
  i.due_date,
  i.paid_date,
  i.created_at,
  i.updated_at
from invoices i
join clients c on i.client_id = c.id
left join v_invoice_itemizations ie on i.id = ie.invoice_id
group by i.id;

drop view if exists v_list_invoices;
create view v_list_invoices as
select
  i.id,
  i.org_id,
  i.client_id,
  c.full_name as client_full_name,
  i.summary,
  (i.labor_hours * i.labor_rate) as labor_amount,
  sum(coalesce(ie.total_amount, 0)) as expense_amount,
  ((i.labor_hours * i.labor_rate) + sum(coalesce(ie.total_amount, 0))) as total_amount,
  i.due_date,
  i.paid_date,
  i.created_at,
  i.updated_at
from invoices i
join clients c on i.client_id = c.id
left join v_invoice_itemizations ie on i.id = ie.invoice_id
group by i.id;

drop view if exists v_expenses;
create view v_expenses as
select
  e.id,
  e.org_id,
  e.summary,
  e.quantity,
  e.unit_price,
  (e.quantity * e.unit_price) as total_amount,
  e.purchase_date,
  e.created_at,
  e.updated_at
from expenses e;

drop view if exists v_invoice_itemizations;
create view v_invoice_itemizations as
select
  ie.id,
  ie.org_id,
  ie.invoice_id,
  e.id as expense_id,
  e.summary,
  e.quantity,
  e.unit_price,
  e.total_amount,
  e.purchase_date,
  e.created_at as created_at,
  e.updated_at as updated_at
from invoice_expenses ie
join v_expenses e on ie.expense_id = e.id;

-- Seed initial data
insert or ignore into orgs (id, org_code, display_name) values
('bfa77ccf-8e42-4120-b500-715980343adc', 'gingerteksolutions', 'GingerTek Solutions');
insert or ignore into org_settings (org_id, setting_key, setting_value) values
('bfa77ccf-8e42-4120-b500-715980343adc', 'invoice_template', '{{ invoice.items }}'),
('bfa77ccf-8e42-4120-b500-715980343adc', 'default_labor_rate', '75.00'),
('bfa77ccf-8e42-4120-b500-715980343adc', 'contact_website', ''),
('bfa77ccf-8e42-4120-b500-715980343adc', 'contact_email', ''),
('bfa77ccf-8e42-4120-b500-715980343adc', 'contact_phone', ''),
('bfa77ccf-8e42-4120-b500-715980343adc', 'contact_address', '');
insert or ignore into roles (id, org_id, role_name, bit_value) values
('8b6205ad-c012-4675-9d44-968c6d410732', 'bfa77ccf-8e42-4120-b500-715980343adc', 'admin', 4294967295),
('39114570-fd65-4153-8ea2-56e4b8338eed', 'bfa77ccf-8e42-4120-b500-715980343adc', 'invoice_manager', 30);
insert or ignore into users (id, org_id, display_name, username, passhash, role_id) values
('d290f1ee-6c54-4b01-90e6-d701748f0851', 'bfa77ccf-8e42-4120-b500-715980343adc', 'Jeremy M', 'jmoormann', '$2y$12$wSJcOQItXyQJQ0Sp2lOr/eeCwHidIrDzRVlZctKBbMS9gamCLB03u', '8b6205ad-c012-4675-9d44-968c6d410732'),
('4b28f715-105f-48d3-993f-1f33b35fafd0', 'bfa77ccf-8e42-4120-b500-715980343adc', 'Guest', 'guest', '$2y$12$wSJcOQItXyQJQ0Sp2lOr/eeCwHidIrDzRVlZctKBbMS9gamCLB03u', '39114570-fd65-4153-8ea2-56e4b8338eed');
insert or ignore into clients (id, org_id, full_name, contact_email, contact_phone, contact_address) values
('489ed9db-5e97-4671-ae47-7c0b611deada', 'bfa77ccf-8e42-4120-b500-715980343adc', 'Acme Corporation', 'info@acme.com', '123-456-7890', '123 Main St, Anytown, USA');