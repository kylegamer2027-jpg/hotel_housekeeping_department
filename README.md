# Hearth & Haven Housekeeping

A PHP and MySQL housekeeping workspace for room operations, daily tasks, stock, lost property, maintenance, staff, and reports. The interface uses a Dusty Pink, Khaki, and Cream palette.

## Run with XAMPP

1. Place the project folder at `C:\xampp\htdocs\hotel_housekeeping_department`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Import `database/housekeeping.sql` using phpMyAdmin. It creates the `housekeeping` database and tables.
4. If your local MySQL credentials differ from the XAMPP defaults, set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` in your PHP environment. The defaults are `127.0.0.1`, `housekeeping`, `root`, and an empty password.
5. Visit `http://localhost/hotel_housekeeping_department/`. The first visit prompts you to create the first administrator; after that, sign in with that account.
6. Add rooms, then use Employees to create team member accounts and Tasks to assign work.

Passwords are stored as PHP password hashes. The first-account setup is only available while the users table is empty. The app expects the project to be served from the `/hotel_housekeeping_department` URL path.
