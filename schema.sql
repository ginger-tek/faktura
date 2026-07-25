create table
  if not exists settings (
    id integer primary key,
    key text not null unique,
    value text not null,
    created_at integer,
    updated_at integer
  );

create table
  if not exists users (
    id integer primary key,
    username text not null unique,
    passhash text not null,
    tokens text,
    is_active integer default 1,
    permissions_bit integer default 0,
    created_at integer,
    updated_at integer
  );

create table
  if not exists clients (
    id integer primary key,
    name text not null unique,
    email text not null,
    phone text,
    address text,
    created_at integer,
    updated_at integer
  );

create table
  if not exists invoices (
    id integer primary key,
    number text not null,
    client_id integer not null,
    summary text not null,
    details text,
    labor_amount real default 0,
    due_date text,
    paid_date text,
    paid_amount real default 0,
    created_at integer,
    updated_at integer
  );

create table
  if not exists invoice_items (
    id integer primary key,
    invoice_id integer not null,
    summary text not null,
    quantity integer default 1,
    unit_price real not null,
    is_expense integer default 0,
    created_at integer,
    updated_at integer,
    foreign key (invoice_id) references invoices (id) on delete cascade
  );

create index if not exists idx_settings_key on settings (`key`);

create index if not exists idx_clients_email on clients (email);

create index if not exists idx_invoices_client_id on invoices (client_id);

create index if not exists idx_invoices_due_date on invoices (due_date);

create index if not exists idx_invoices_paid_date on invoices (paid_date);

create index if not exists idx_invoices_created_at on invoices (created_at);

create index if not exists idx_invoice_items_invoice_id on invoice_items (invoice_id);

create index if not exists idx_invoice_items_is_expense on invoice_items (is_expense);

create index if not exists idx_invoice_items_created_at on invoice_items (created_at);

create index if not exists idx_settings_key on settings (key);

drop view if exists v_invoices;

create view
  if not exists v_invoices as
select
  i.*,
  case
    when i.due_date
    and not i.paid_date
    and date ('now') > i.due_date then 'Overdue'
    when i.paid_date
    and i.due_date
    and i.paid_date > i.due_date then 'Paid Late'
    when i.paid_date
    and i.due_date
    and i.paid_date <= i.due_date then 'Paid'
    else 'Pending'
  end as status,
  c.name as client_name,
  i.labor_amount + coalesce(sum(ii.quantity * ii.unit_price), 0) as total_amount,
  coalesce(
    sum(ii.is_expense * ii.quantity * ii.unit_price),
    0
  ) as total_expenses
from
  invoices i
  join clients c on i.client_id = c.id
  left join invoice_items ii on ii.invoice_id = i.id
group by
  i.id;

drop view if exists v_invoice_items;

create view
  if not exists v_invoice_items as
select
  ii.*,
  i.id as invoice_id,
  i.number as invoice_number,
  i.client_id,
  i.client_name as invoice_client_name,
  i.summary as invoice_summary,
  ii.quantity * ii.unit_price as total_amount
from
  invoice_items ii
  join v_invoices i on ii.invoice_id = i.id;