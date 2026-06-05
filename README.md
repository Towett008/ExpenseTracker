# 💰 Personal Expense Tracker

A simple web application to track your daily income and expenses, built with **PHP 8**, **MySQL**, **Bootstrap 5**, and **Vanilla JavaScript**.

---

## 📁 File Structure

```
expense_tracker/
├── index.php                  ← Login & Register page
├── expenses_tracker.sql       ← Database schema + sample data
├── README.md                  ← This file
│
├── css/
│   └── style.css              ← Custom styles
│
├── js/
│   └── app.js                 ← Client-side JS (validation, live preview, search)
│
├── includes/
│   ├── config.php             ← Database connection
│   └── auth.php               ← Session helpers (login check, sanitize)
│
└── pages/
    ├── dashboard.php          ← Summary dashboard (totals, monthly table, chart)
    ├── expenses.php           ← Full CRUD for income/expense entries
    └── logout.php             ← Destroys session, redirects to login
```

---

## ⚙️ How to Run (XAMPP / WAMP)

### Step 1 – Install XAMPP
Download and install XAMPP from https://www.apachefriends.org  
Start the **Apache** and **MySQL** modules in the XAMPP Control Panel.

### Step 2 – Copy the project files
Place the `expense_tracker` folder inside XAMPP's web root:
```
C:\xampp\htdocs\expense_tracker\       (Windows)
/opt/lampp/htdocs/expense_tracker/     (Linux/Mac)
```

### Step 3 – Create the database
1. Open your browser and go to `http://localhost/phpmyadmin`
2. Click **New** → type `expenses_tracker` → click **Create**
3. Click the new database → click the **Import** tab
4. Click **Choose File**, select `expenses_tracker.sql`, then click **Go**

### Step 4 – Configure the database connection (if needed)
Open `includes/config.php` and update these lines if your MySQL settings differ:
```php
define('DB_USER', 'root');  // your MySQL username
define('DB_PASS', '');      // your MySQL password (blank by default in XAMPP)
```

### Step 5 – Open the app
Visit: **http://localhost/expense_tracker/**

---

## 🔑 Demo Login Credentials

| Field    | Value                |
|----------|----------------------|
| Email    | demo@example.com     |
| Password | password             |

---

## ✅ Features

- User registration & login (passwords hashed with `password_hash()`)
- Add, edit, delete income & expense entries
- Filter by type, category, and date range
- Live search (no page reload)
- Summary dashboard with monthly table and category breakdown
- Mobile-responsive (Bootstrap 5)
- Color-coded expense categories

---

## 🛠️ Tech Stack

| Layer      | Technology       |
|------------|-----------------|
| Backend    | PHP 8+           |
| Database   | MySQL 5.7+       |
| Frontend   | HTML5 + CSS3     |
| UI Kit     | Bootstrap 5.3    |
| Scripting  | Vanilla JS (ES6) |
