
# Student Web App

Simple PHP student management app — configured for deployment to InfinityFree (shared PHP + MySQL hosting).

## Contents
- PHP source files for student CRUD and authentication
- Database schema: `student_app.sql`

## Prerequisites
- An InfinityFree account (or any PHP + MySQL shared hosting)
- FTP client or the InfinityFree File Manager
- phpMyAdmin access (provided in the hosting control panel)

## Deployment on InfinityFree
1. Create an account and add a website (domain or subdomain) in InfinityFree.
2. In the InfinityFree control panel create a MySQL database and user. Note the **database host** (often `sqlXXXX.epizy.com`), **database name**, **username**, and **password**.
3. Import the database schema using phpMyAdmin (Control Panel → MySQL → phpMyAdmin) by importing `student_app.sql`.
4. Upload the project files to your account's `htdocs` directory using the File Manager or an FTP client (InfinityFree credentials are in the control panel). Keep the repository structure intact.
5. Update `db.php` with the database host, database name, username, and password you created on InfinityFree.
6. Open your site URL (e.g. `https://yourdomain/`) and verify the app.

Notes:
- Use the hosting control panel to create the database and import `student_app.sql` — you cannot run `mysql` on the hosting shell.
- InfinityFree may restrict some outbound services (SMTP, external APIs) — check their docs if you rely on external services.

## Local development (optional)
If you still want to run locally during development, you can use XAMPP or similar:

1. Place the project in your web root (e.g. `C:/xampp/htdocs/Student_web_app`).
2. Import `student_app.sql` into your local MySQL and update `db.php` with local credentials.
3. Start Apache and MySQL and open `http://localhost/Student_web_app/`.

## Security and configuration
- Update `db.php` with production credentials and remove any local test accounts before going public.
- Consider restricting access to any installation or test scripts and avoid committing sensitive credentials to the repository.

## License
This repo has no license specified — add one if you plan to share publicly.
