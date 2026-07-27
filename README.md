# Faktura

Simple, self-hosted, flat-file invoicing web app, with invoice templating and user access control.

## Requirements
- Caddy/Nginx/Apache etc.
- PHP 8+
- PHP Extensions:
  - mbstring
  - pdo_sqlite

## Get Started
1. Create new instance in current directory:
    ```bash
    composer create-project ginger-tek/faktura .
    ```
2. Run setup script (if not using OS env vars, local .env file will be generated):
    ```bash
    composer cli setup
    ```
4. Create first admin user (see [create new user](#create-new-user)):
3. Finally, serve app from `public/` using preferred web server

## CLI
### List Users
```bash
composer cli list-users
> [
  {
    "id": 1,
    ...
  },
  ...
]
```
### Create New User
If permission bit value is not provided, default permissions is set to only view/list invoices/clients/expenses.
```bash
composer cli new-user {username} {password} {?permissions_bit}
```
Example, create admin user with all permissions:
```bash
composer cli new-user "admin" "mypassword" 131070
> {
  "id": 1,
  "username": "admin",
  ...
}
```

### Permissions
Show key-value list of permissions
```bash
composer cli list-permissions
```
Get a bit-sum of permissions by regex filter to set on a user, i.e. all invoice-related permissions:
```bash
composer cli filter-sum-permissions 'invoice_.*'
> 62
```