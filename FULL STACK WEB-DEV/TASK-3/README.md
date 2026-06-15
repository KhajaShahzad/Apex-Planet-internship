# UserHub — User Management System
### Apex Planet Internship · Task 3 · Backend Development & Database Integration (Days 25–36)

---

## 📌 Overview
A full-stack **User Management System** built with **PHP 8+** and **MySQL**, implementing:
- 🗄️ **Database Design** — Normalised schema (3NF), ER relationships
- ⚙️ **CRUD Operations** — Create, Read, Update, Delete users
- 🔐 **Authentication System** — Sessions, bcrypt, role-based access
- 🛡️ **Security** — Prepared statements, server-side validation, XSS prevention
- 🖼️ **Profile Management** — Picture upload with type & size validation

---

## 🗂️ Project Structure
```
TASK-3/
├── assets/
│   ├── css/style.css          # Premium dark theme
│   ├── js/main.js             # Client-side logic
│   └── uploads/               # User profile pictures (auto-created)
├── config/
│   └── db.php                 # MySQLi connection
├── includes/
│   ├── auth.php               # Session helpers, role guards, flash messages
│   ├── header.php             # Common navbar
│   └── footer.php             # Common footer
├── auth/
│   ├── register.php           # User registration
│   ├── login.php              # Login with session creation
│   └── logout.php             # Session destruction
├── admin/
│   ├── dashboard.php          # CRUD table (admin only)
│   ├── add_user.php           # Add user form
│   ├── edit_user.php          # Edit user form
│   └── delete_user.php        # Delete handler (POST)
├── user/
│   ├── dashboard.php          # User personal dashboard
│   └── profile.php            # Edit profile + picture upload
├── database/
│   └── schema.sql             # Full DB schema
└── index.php                  # Landing page / role redirect
```

---

## 🚀 Setup Instructions

### Prerequisites
- **XAMPP** (or Laragon / any PHP+MySQL stack)
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.4+

### Steps

1. **Copy project to XAMPP**
   ```
   Copy the TASK-3 folder → C:\xampp\htdocs\task3\
   ```

2. **Import database schema**
   - Open **phpMyAdmin** → `http://localhost/phpmyadmin`
   - Click **Import** → choose `database/schema.sql`
   - This creates the `task3_usermgmt` database with tables and a default admin user

3. **Configure DB connection** *(if needed)*
   - Open `config/db.php`
   - Update `DB_USER` and `DB_PASS` to match your MySQL credentials
   - Default: `root` / *(empty password)* — works out-of-the-box with XAMPP

4. **Run the app**
   - Start Apache + MySQL in XAMPP Control Panel
   - Visit: `http://localhost/task3/`

---

## 🔑 Default Credentials

| Role  | Email                  | Password     |
|-------|------------------------|--------------|
| Admin | admin@apexplanet.com   | Admin@1234   |

> ⚠️ Change the admin password after first login!

---

## ✨ Features

### 1. Database Design
- **`roles`** table: `id`, `name`, `created_at`
- **`users`** table: `id`, `role_id (FK)`, `username`, `email`, `password_hash`, `profile_picture`, `bio`, `created_at`, `updated_at`
- Normalised to **3NF**: no transitive dependencies; role data isolated in its own table
- **Foreign key** constraint: `users.role_id → roles.id` (CASCADE UPDATE, RESTRICT DELETE)

### 2. CRUD Operations
- Admin dashboard lists all users in an HTML table with search & pagination
- **Create**: validated form → prepared `INSERT`
- **Read**: prepared `SELECT` with JOINs
- **Update**: pre-filled form → prepared `UPDATE`, optional password reset
- **Delete**: JS confirmation popup → POST → prepared `DELETE` + file cleanup

### 3. Authentication System
- Registration with `password_hash(PASSWORD_BCRYPT, ['cost'=>12])`
- Login with `password_verify()` + `session_regenerate_id(true)` against session fixation
- Role-based redirect: Admin → `/admin/dashboard.php`, User → `/user/dashboard.php`
- Logout destroys session and expires cookie

### 4. Security
- ✅ **All queries use `mysqli_prepare()`** — no raw string interpolation
- ✅ Server-side validation on every form field
- ✅ Output escaped with `htmlspecialchars()` (`e()` helper)
- ✅ Passwords stored as bcrypt hashes (never plain-text)
- ✅ Session fixation prevention on login
- ✅ Admin cannot delete their own account
- ✅ Role guards redirect unauthorised access with HTTP 403

### 5. Profile Management
- Edit username, email, bio, password
- Upload profile picture:
  - **MIME validation** via PHP `finfo` (not just file extension)
  - **Size limit**: max 2 MB
  - **Allowed types**: JPG, PNG, GIF, WEBP
  - Old picture deleted on replacement
- Live client-side preview before upload

---

## 📐 ER Diagram (Logical)
```
roles                    users
─────────────────        ─────────────────────────────────
id     (PK)   ◄──────── role_id  (FK)
name                     id          (PK)
created_at               username    (UNIQUE)
                         email       (UNIQUE)
                         password_hash
                         profile_picture
                         bio
                         created_at
                         updated_at
```

---

## 🛠️ Tech Stack

| Layer      | Technology                          |
|------------|-------------------------------------|
| Frontend   | HTML5, Vanilla CSS (dark theme), JS |
| Backend    | PHP 8+                              |
| Database   | MySQL / MariaDB (MySQLi)            |
| Security   | Prepared Statements, bcrypt, finfo  |
| Fonts      | Google Fonts (Inter, Outfit)        |

---

## 📄 License
MIT — For educational purposes as part of the Apex Planet Full Stack Web Development Internship.
