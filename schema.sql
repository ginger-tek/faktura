create table
  if not exists orgs (
    id text primary key,
    org_code text not null unique,
    display_name text not null,
    logo text,
    created_at integer default (unixepoch ()),
    updated_at integer default (unixepoch ())
  );

create table
  if not exists org_settings (
    id integer primary key,
    org_id text not null,
    setting_key text not null,
    setting_value text,
    created_at integer default (unixepoch ()),
    updated_at integer default (unixepoch ()),
    created_by text,
    updated_by text,
    unique (org_id, setting_key),
    foreign key (org_id) references orgs (id) on delete cascade
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
    created_at integer default (unixepoch ()),
    updated_at integer default (unixepoch ()),
    created_by text,
    updated_by text,
    foreign key (org_id) references orgs (id) on delete cascade,
    foreign key (role_id) references roles (id) on delete set null
  );

create table
  if not exists roles (
    id text primary key,
    org_id text not null,
    role_name text not null,
    bit_value integer default 0,
    created_at integer default (unixepoch ()),
    updated_at integer default (unixepoch ()),
    created_by text,
    updated_by text,
    unique (org_id, bit_value),
    foreign key (org_id) references orgs (id) on delete cascade
  );

create table
  if not exists clients (
    id text primary key,
    org_id text not null,
    full_name text not null,
    contact_email text not null,
    contact_phone text,
    contact_address text,
    created_at integer default (unixepoch ()),
    updated_at integer default (unixepoch ()),
    created_by text,
    updated_by text,
    foreign key (org_id) references orgs (id) on delete cascade
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
    created_at integer default (unixepoch ()),
    updated_at integer default (unixepoch ()),
    created_by text,
    updated_by text,
    unique (org_id, id),
    foreign key (org_id) references orgs (id) on delete cascade
  );

create table
  if not exists expenses (
    id text primary key,
    org_id text not null,
    summary text not null,
    quantity integer default 1,
    unit_price decimal not null,
    purchase_date integer default (unixepoch ()),
    created_at integer default (unixepoch ()),
    updated_at integer default (unixepoch ()),
    created_by text,
    updated_by text,
    foreign key (org_id) references orgs (id) on delete cascade
  );

create table
  if not exists invoice_expenses (
    id int primary key,
    org_id text not null,
    invoice_id text not null,
    expense_id text not null,
    created_at integer default (unixepoch ()),
    updated_at integer default (unixepoch ()),
    created_by text,
    updated_by text,
    unique (org_id, invoice_id, expense_id),
    foreign key (invoice_id) references invoices (id) on delete cascade,
    foreign key (expense_id) references expenses (id) on delete cascade,
    foreign key (org_id) references orgs (id) on delete cascade
  );

drop view if exists v_org_settings;

create view
  v_org_settings as
select
  os.id,
  os.org_id,
  os.setting_key,
  case
    when length (os.setting_value) > 15 then substr (os.setting_value, 1, 15) || '...'
    else os.setting_value
  end as setting_value,
  os.created_at,
  cu.username as created_by,
  os.updated_at,
  uu.username as updated_by
from
  org_settings os
  left join users cu on os.created_by = cu.id
  left join users uu on os.updated_by = uu.id;

drop view if exists v_users;

create view
  v_users as
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
  cu.display_name || '|' || cu.username as created_by,
  u.updated_at,
  uu.display_name || '|' || uu.username as updated_by
from
  users u
  left join roles r on u.role_id = r.id
  left join orgs o on u.org_id = o.id
  left join users cu on u.created_by = cu.id
  left join users uu on u.updated_by = uu.id
group by
  u.id;

drop view if exists v_users_full;

create view
  v_users_full as
select
  u.id,
  u.org_id,
  o.display_name as org_display_name,
  u.display_name,
  u.username,
  u.passhash,
  u.role_id,
  r.role_name,
  r.bit_value as role_bit_value,
  u.created_at,
  cu.display_name || '|' || cu.username as created_by,
  u.updated_at,
  uu.display_name || '|' || uu.username as updated_by
from
  users u
  left join roles r on u.role_id = r.id
  left join orgs o on u.org_id = o.id
  left join users cu on u.created_by = cu.id
  left join users uu on u.updated_by = uu.id
group by
  u.id;

drop view if exists v_invoices;

create view
  v_invoices as
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
  (
    (i.labor_hours * i.labor_rate) + sum(coalesce(ie.total_amount, 0))
  ) as total_amount,
  i.due_date,
  i.paid_date,
  i.created_at,
  cu.display_name || '|' || cu.username as created_by,
  i.updated_at,
  uu.display_name || '|' || uu.username as updated_by
from
  invoices i
  join clients c on i.client_id = c.id
  left join v_invoice_itemizations ie on i.id = ie.invoice_id
  left join users cu on i.created_by = cu.id
  left join users uu on i.updated_by = uu.id
group by
  i.id;

drop view if exists v_list_invoices;

create view
  v_list_invoices as
select
  i.id,
  i.org_id,
  i.client_id,
  c.full_name as client_full_name,
  i.summary,
  (i.labor_hours * i.labor_rate) as labor_amount,
  sum(coalesce(ie.total_amount, 0)) as expense_amount,
  (
    (i.labor_hours * i.labor_rate) + sum(coalesce(ie.total_amount, 0))
  ) as total_amount,
  i.due_date,
  i.paid_date,
  i.created_at,
  cu.display_name || '|' || cu.username as created_by,
  i.updated_at,
  uu.display_name || '|' || uu.username as updated_by
from
  invoices i
  join clients c on i.client_id = c.id
  left join v_invoice_itemizations ie on i.id = ie.invoice_id
  left join users cu on i.created_by = cu.id
  left join users uu on i.updated_by = uu.id
group by
  i.id;

drop view if exists v_expenses;

create view
  v_expenses as
select
  e.id,
  e.org_id,
  e.summary,
  e.quantity,
  e.unit_price,
  (e.quantity * e.unit_price) as total_amount,
  e.purchase_date,
  e.created_at,
  cu.display_name || '|' || cu.username as created_by,
  e.updated_at,
  uu.display_name || '|' || uu.username as updated_by
from
  expenses e
  left join users cu on e.created_by = cu.id
  left join users uu on e.updated_by = uu.id;

drop view if exists v_invoice_itemizations;

create view
  v_invoice_itemizations as
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
  cu.display_name || '|' || cu.username as created_by,
  e.updated_at as updated_at,
  uu.display_name || '|' || uu.username as updated_by
from
  invoice_expenses ie
  join v_expenses e on ie.expense_id = e.id
  left join users cu on e.created_by = cu.id
  left join users uu on e.updated_by = uu.id;