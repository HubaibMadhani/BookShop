# BookShop (XAMPP-ready)

A lightweight PHP book shop demo that's ready to run with XAMPP.

Setup:
1. Start Apache + MySQL (XAMPP).
2. Import `database/my_website.sql` into your MySQL server (e.g., phpMyAdmin).
3. Place this project in your `htdocs` folder or open it via XAMPP.
4. Update DB credentials in `includes/config.php` if needed.
5. Visit `http://localhost/<folder>/` to open the site.

Features:
- Browse books, book detail pages
- Cart and checkout (orders stored in DB)
- Admin area: manage books (requires login)

Notes:
- This is a minimal demo; add validation, image upload sanitization, CSRF protection and user management for production use.
- To create an admin account: register normally, then promote the user in the database: `UPDATE users SET role='admin' WHERE email='you@example.com';`