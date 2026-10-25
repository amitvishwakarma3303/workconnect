# Work Connect - Smart Labouring System

Work Connect is a modern, premium web application designed to connect skilled labourers with employers. Built with a robust PHP backend and a stunning Tailwind CSS frontend, it provides a seamless experience for finding and managing work.

## ✨ Features

- **Multi-Role Support**: Tailored experiences for Admins, Employers, and Employees.
- **Premium UI/UX**: Modern design system using Tailwind CSS with glassmorphism, vibrant gradients, and responsive layouts.
- **Secure Authentication**: Role-based access control with hashed passwords and session management.
- **Admin Dashboard**: System-wide statistics and user management overview.
- **Employer Hub**: Tools for posting jobs, managing applications, and tracking hired workforce.
- **Employee Portal**: Job search engine, application tracking, and earning summaries.

## 🛠️ Tech Stack

- **Backend**: PHP 8.x
- **Database**: MySQL
- **Frontend**: Tailwind CSS (via CDN), Font Awesome, Google Fonts (Outfit)
- **Authentication**: Custom Session-based Auth

## 🚀 Getting Started

### Prerequisites

- A local server environment like **XAMPP**, **WAMP**, or **MAMP**.
- MySQL database server.

### Installation

1. **Clone/Download** the repository into your local server's root directory (e.g., `htdocs/TYIT-79`).
2. **Database Setup**:
   - Open PHPMyAdmin (usually `localhost/phpmyadmin`).
   - Create a new database named `work_connect`.
   - Import the `db.sql` file provided in the root directory.
3. **Configuration**:
   - Open `includes/db_connect.php`.
   - Update the `$username` and `$password` fields with your MySQL credentials.
4. **Run the App**:
   - Navigate to `http://localhost/TYIT-79/login.php` in your browser.

## 📂 Project Structure

- `admin/`: Admin-specific pages and dashboards.
- `employer/`: Employer-specific pages and dashboards.
- `employee/`: Employee-specific pages and dashboards.
- `includes/`: Reusable scripts (DB connection, Auth middleware).
- `assets/`: UI assets (CSS, JS, Images).
- `db.sql`: Database schema export.
- `login.php`: Unified login/registration portal.
- `register.php`: Registration logic.
- `login_process.php`: Authentication and routing logic.
- `logout.php`: Session termination.

## 🔒 Security

- Passwords are encrypted using `password_hash()` (Bcrypt).
- Pages are protected by `auth_middleware.php` to ensure only authorized roles can access specific dashboards.

---
Developed as a Smart Labouring System for TYIT-79.
