# Hearth & Haven Housekeeping

A PHP and MySQL housekeeping workspace for room operations, daily tasks, stock, lost property, maintenance, staff, and reports. The interface uses a Dusty Pink, Khaki, and Cream palette.

Forms include optional task templates with reusable checklists, suggested room and inventory values, balanced task-assignee recommendations, shift presets, and maintenance and lost-item suggestions. Suggested values can be edited; custom room types, item names, and descriptions remain supported.

## Run with XAMPP

1. Place the project folder at `C:\xampp\htdocs\hotel_housekeeping_department`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Import `database/housekeeping.sql` using phpMyAdmin. It creates the `housekeeping` database and tables.
4. Configure the database in `.env`. The included local file uses XAMPP defaults. For another server, copy `.env.example` to `.env` and set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS`. Environment variables already configured by your hosting provider take precedence over values in `.env`.
5. Visit `http://localhost/hotel_housekeeping_department/`. The first visit prompts you to create the first administrator; after that, sign in with that account.
6. Add rooms, then use Employees to create team member accounts and Tasks to assign work.

Passwords are stored as PHP password hashes. The first-account setup is only available while the users table is empty. The app expects the project to be served from the `/hotel_housekeeping_department` URL path.

## Deployment

1. Deploy the project to a PHP host with PHP 8.1+ and the PDO MySQL extension enabled.
2. Create a MySQL database and a dedicated database user with access to it. Import `database/housekeeping.sql` into that database.
3. Copy `.env.example` to `.env` on the server and enter the database host, port, name, username, and password. Keep `.env` private; it is excluded from Git.
4. Serve the app at `/hotel_housekeeping_department` as expected by its current links, or update the hard-coded app paths when deploying it at another URL.
5. Visit the site and create the first administrator account. Use HTTPS in production.

The application loads `.env` without third-party packages and does not replace environment variables supplied by the hosting platform. Apache deployments use `.htaccess` to deny direct access to dotfiles; on Nginx or another server, add an equivalent rule or provide secrets through the platform's environment variables instead of a web-root `.env` file.
