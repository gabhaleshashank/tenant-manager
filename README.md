## Tenant Management System

The **Tenant Management System** is a web-based application for managing tenants, rental units, and basic payment records. It is built with **PHP** (backend), **MySQL** (database), and **HTML/CSS/JavaScript** (frontend).

### Features

- **User authentication**
  - User registration with hashed passwords
  - Secure login and logout
  - Session-based access control for protected pages

- **Tenant management**
  - Add new tenants (name, contact, property, rent amount, deposit)
  - Upload tenant documents: agreement (PDF), passport photo, Aadhar card, PAN card (PDF or image)
  - View and download documents (opens in new tab)
  - View a list of all tenants
  - Edit tenant details
  - Delete tenant records

- **Optional payment tracking**
  - Record payments for each tenant
  - View payment history per tenant

### Project Structure

```text
tenant-manager/
  assets/
    css/
      style.css
    js/
      validation.js
  config/
    db.php
  database/
    schema.sql
  includes/
    header.php
    footer.php
    nav.php
    auth.php
  index.php        (login)
  register.php     (user registration)
  dashboard.php    (protected dashboard)
  tenants.php      (list tenants)
  tenant_form.php  (add / edit tenant)
  tenant_delete.php (delete tenant)
  composer.json
```

### Requirements

- PHP 8.x or later
- MySQL 5.7+ or MariaDB
- A web server such as Apache or Nginx (or the built-in PHP development server)

### Installation

1. **Clone or copy the project** into your web server directory (e.g. `htdocs` or `wwwroot`).

2. **Create the database**:
   - Create a new MySQL database, e.g. `tenant_manager`.
   - Import the schema file:
     - Using phpMyAdmin: import `database/schema.sql`.
     - Or via CLI:
       ```bash
       mysql -u your_user -p tenant_manager < database/schema.sql
       ```
   - **If you already have the database** and are adding document upload support, run:
     ```sql
     ALTER TABLE tenants
       ADD COLUMN agreement_document VARCHAR(255) DEFAULT NULL AFTER status,
       ADD COLUMN passport_photo VARCHAR(255) DEFAULT NULL AFTER agreement_document,
       ADD COLUMN aadhar_card VARCHAR(255) DEFAULT NULL AFTER passport_photo,
       ADD COLUMN pan_card VARCHAR(255) DEFAULT NULL AFTER aadhar_card;
     ```
   - Ensure the `uploads` directory exists and is writable (uploads are stored under `uploads/tenants/<id>/`).

3. **Configure database connection**:
   - Open `config/db.php`.
   - Set your database host, name, username, and password.

4. **Start the server**:
   - Using PHP built-in server (from project root):
     ```bash
     php -S localhost:8000
     ```
   - Then open `http://localhost:8000/index.php` in your browser.

5. **Register a user and log in**:
   - Visit `register.php` to create an account.
   - Use the new account to log in via `index.php`.

### Security Notes

- Passwords are stored using PHP's `password_hash()` and verified with `password_verify()`.
- All database operations use prepared statements to help prevent SQL injection.
- Protected pages check for an active PHP session before allowing access.

### Next Steps / Customization

- Add roles (e.g. admin vs staff).
- Extend payment tracking and reporting.
- Add search and filtering on the tenant list.
- Improve the UI styling further or adapt it to your branding.

