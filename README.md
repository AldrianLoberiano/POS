# Coffee Shop POS - Login System

Simple PHP + MySQL login system for a Coffee Shop Point of Sale (POS).

Features:

- User registration
- Login with password hashing
- Session handling
- Role-based dashboard (admin, barista, cashier)

Requirements

- XAMPP (Apache + MySQL)
- PHP 7.4+ (mysqli extension)

Setup

1. Place the `POS` folder in your XAMPP `htdocs` (already assumed: `C:/xampp/htdocs/POS`).
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Create the database and tables: import `db/schema.sql` into phpMyAdmin or run the SQL file.
   - You can import via phpMyAdmin or run `mysql -u root -p < db/schema.sql`.
4. Edit `config.php` if your MySQL username/password differ from defaults.
5. Seed an admin user (optional but recommended): open `http://localhost/POS/seed_admin.php` in your browser. This creates an `admin` user with password `Admin@123` if it doesn't exist.
6. Open `http://localhost/POS/` to access the login page.

Files & locations

- `config.php` - DB config and helper
- `init.php` - session and auth helpers
- `index.php` - login form
- `login.php` - login handler
- `register_form.php` - registration form
- `register.php` - registration handler
- `logout.php` - logout
- `dashboard.php` - role router
- `dashboard_admin.php`, `dashboard_barista.php`, `dashboard_cashier.php` - role pages
- `db/schema.sql` - SQL schema
- `seed_admin.php` - creates admin user with hashed password

Security notes

- Passwords are hashed with PHP's `password_hash()` and verified with `password_verify()`.
- All DB queries use prepared statements (mysqli) to avoid SQL injection.

Next steps / improvements

- Add CSRF protection and input sanitization for production.
- Add email verification and password reset.
- Add logging and stronger role management (disable self-registration for admin).
