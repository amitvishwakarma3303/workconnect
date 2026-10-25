# Work Connect – Worker Job Portal

**Work Connect – Worker Job Portal** is a web-based platform designed to help any person find a suitable worker according to their work requirements. In daily life, people often need workers such as plumbers, electricians, carpenters, painters, cleaners, drivers, mechanics, construction workers, gardeners, cooks, and other skilled or unskilled workers.

---

## 🎯 The Main Concept

```
Person Who Needs a Worker ➔ Search Worker ➔ View Worker Profile ➔ Contact / Hire Worker
```

The system connects **People Who Need Workers ↔ Workers Who Need Work** making the process faster, easier, and more organized.

---

## 🚀 Key Modules (as per Project Synopsis)

### 1. User Module (Person Who Needs a Worker)
- **Registration & Authentication**: Sign up as a client/user needing worker services.
- **Worker Search Module**: Search workers by:
  - Work Category (Plumber, Electrician, Carpenter, Painter, etc.)
  - Location / City / Area
  - Skills and specializations
  - Years of Experience
  - Availability status (Available / Busy)
- **Worker Profile Module**: View detailed worker portfolio, experience background, daily/hourly rates, and contact details before hiring.
- **Contact / Hiring Module**: Send direct work requests specifying task requirements, location, scheduled date, and proposed budget.
- **Direct Messaging**: 1-on-1 real-time chat with workers.

### 2. Worker Module (Skilled Labourer)
- **Profile Management**: Maintain comprehensive trade profile including name, category, skills, years of experience, work history, location, availability, and pricing.
- **Live Availability Switcher**: Easily toggle between *Available for Work* and *Currently Busy*.
- **Work Request Management**: Review incoming work requests from clients with options to *Accept*, *Decline*, or *Mark as Completed*.
- **Client Communication**: Real-time messaging with clients to coordinate timing and specifics.

### 3. Admin Module
- **Dashboard**: System-wide statistics for registered clients, skilled workers, trade categories, and work requests.
- **Category Management**: Add, update, and organize trade categories (icons, names, descriptions).
- **User & Worker Management**: View, search, filter, and moderate user accounts and worker profiles.
- **Work Request Records**: Review and moderate all hiring transactions across the system.
- **Contact Inquiries**: Manage public contact form submissions.

---

## 🛠️ Work Categories Supported

- **Plumbers**: Water pipe fitting, leak repair, bathroom and sanitary installation.
- **Electricians**: Wiring, electrical repairs, appliance installations, switchboards.
- **Carpenters**: Furniture crafting, door/window framing, woodwork repair.
- **Painters**: Interior/exterior painting, wall finishing, polishing.
- **Construction Workers**: Masonry, bricklaying, cement work, structural labor.
- **Technicians**: Appliance servicing, AC, refrigerator, machine maintenance.
- **Cleaners**: Home and commercial cleaning, deep sanitation, housekeeping.
- **Drivers**: Personal, commercial, and transport driving.
- **Mechanics**: Two-wheeler and four-wheeler vehicle servicing.
- **Gardeners**: Lawn maintenance, planting, tree trimming.
- **Cooks**: Daily home cooking, event catering, food preparation.

---

## 💻 Tech Stack

- **Frontend**: HTML5, CSS3, Tailwind CSS (via CDN), Font Awesome 6.5, Google Fonts (Outfit, Manrope)
- **Backend**: PHP 8.x
- **Database**: MySQL / MariaDB (PDO with Prepared Statements)
- **Real-Time Communication**: Server-Sent Events (SSE) for live chat updates
- **Security**: Bcrypt password hashing (`password_hash`), Session-based authentication, CSRF tokens (`X-CSRF-Token` & form tokens)

---

## ⚙️ Installation & Setup

1. **Copy project into local server directory**:
   - For XAMPP: Place in `c:\xampp\htdocs\workconnect`
2. **Database Setup**:
   - Open PHPMyAdmin (`http://localhost/phpmyadmin`)
   - Create a database named `work_connect`
   - Import `db.sql` (or simply run the app; `includes/migrate_db.php` will auto-create missing tables and seed categories!)
3. **Database Configuration**:
   - Check `includes/db_connect.php` if your MySQL username or password differs from `root` with no password.
4. **Access the Application**:
   - Public Website: `http://localhost/workconnect/home.html` or `http://localhost/workconnect/`
   - Login / Portal: `http://localhost/workconnect/login.php`

---

## 🔑 Demo & Test Accounts

All demo accounts share the password: **`password123`**

| Role | Username | Email | Specialization / Focus |
|---|---|---|---|
| **Admin** | `admin` | `admin@gmail.com` | Full Administrative Access |
| **Client** | `rohit_sharma` | `rohit.client@gmail.com` | Needs plumber & AC technician |
| **Client** | `priya_patel` | `priya.patel@gmail.com` | Needs electrician for wiring |
| **Client** | `anita_deshmukh`| `anita.deshmukh@gmail.com` | Woodwork and door lock repair |
| **Client** | `vikram_singh` | `vikram.singh@gmail.com` | Painting & home renovations |
| **Worker** | `sunil_plumber` | `sunil.plumber@gmail.com` | **Plumber** (8 yrs exp, Nallasopara) |
| **Worker** | `rajesh_electrician` | `rajesh.electrician@gmail.com` | **Electrician** (6 yrs exp, Vasai) |
| **Worker** | `amit_carpenter` | `amit.carpenter@gmail.com` | **Carpenter** (10 yrs exp, Virar) |
| **Worker** | `mahesh_painter` | `mahesh.painter@gmail.com` | **Painter** (5 yrs exp, Naigaon) |
| **Worker** | `ramesh_builder` | `ramesh.mason@gmail.com` | **Construction Worker** (7 yrs exp, Nallasopara) |
| **Worker** | `vijay_technician` | `vijay.tech@gmail.com` | **Technician** (4 yrs exp, Borivali) |
| **Worker** | `deepak_cleaner` | `deepak.cleaner@gmail.com` | **Cleaner** (3 yrs exp, Andheri) |
| **Worker** | `sanjay_driver` | `sanjay.driver@gmail.com` | **Driver** (9 yrs exp, Thane) |
| **Worker** | `arun_mechanic` | `arun.mechanic@gmail.com` | **Mechanic** (6 yrs exp, Vasai) |
| **Worker** | `karan_gardener` | `karan.gardener@gmail.com` | **Gardener** (5 yrs exp, Naigaon) |
| **Worker** | `geeta_cook` | `geeta.cook@gmail.com` | **Cook** (8 yrs exp, Nallasopara) |

