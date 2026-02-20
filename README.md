# Student Web App

Simple PHP student management app for local XAMPP development.

## Contents
- PHP source files for student CRUD and authentication
- Database schema: `student_app.sql`

## Prerequisites
- XAMPP (Apache + PHP + MySQL)
- Git (for repository)

## Setup
1. Place the project in your web root (e.g. `C:/xampp/htdocs/Student_web_app`).
2. Import the database:

```bash
mysql -u root -p < student_app.sql
```

3. Start Apache and MySQL in XAMPP, then open:

http://localhost/Student_web_app/

## Notes
- Default DB credentials in `db.php` may need updating for your environment.
- `.gitignore` excludes logs, SQL dumps, and IDE folders.

## License
This repo has no license specified — add one if you plan to share publicly.
