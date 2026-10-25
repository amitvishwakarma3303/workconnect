<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/auth_middleware.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    header("Location: " . dashboardUrlByRole($_SESSION['role']));
    exit();
}

// Fetch categories for worker registration
$categories = [];
try {
    $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $catStmt->fetchAll();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Connect – Worker Job Portal | Login & Register</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css" />
    <style>
        :root {
            --primary: #f97316;
            --primary-dark: #ea580c;
            --primary-light: #fff7ed;
            --secondary: #0d9488;
            --dark: #0c1a2e;
            --dark-2: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { overflow-x: hidden; max-width: 100%; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 1.5rem 1rem;
            background: linear-gradient(135deg, #0c1a2e 0%, #1e3a5f 45%, #0d4f4a 100%);
            overflow-x: hidden; max-width: 100%;
        }
        body::before {
            content: ''; position: fixed; top: -30%; right: -10%; width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(249,115,22,0.12) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }

        .card {
            max-width: 900px; width: 100%;
            display: flex; border-radius: 28px; overflow: hidden;
            box-shadow: 0 40px 80px rgba(0,0,0,0.4);
            position: relative; z-index: 1;
        }

        /* LEFT PANEL */
        .left-panel {
            width: 42%; background: linear-gradient(160deg, #1e3a5f 0%, #0d4f4a 100%);
            padding: 3rem 2.5rem; color: white; display: flex; flex-direction: column; justify-content: space-between;
            position: relative; overflow: hidden;
        }
        .left-panel::after {
            content: ''; position: absolute; bottom: -3rem; right: -3rem;
            width: 180px; height: 180px; border-radius: 50%;
            background: rgba(249,115,22,0.1);
        }
        .logo-area { display: flex; align-items: center; gap: 12px; margin-bottom: 2.5rem; }
        .logo-icon {
            width: 42px; height: 42px; border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;
        }
        .logo-text { font-size: 1.3rem; font-weight: 800; }
        .logo-sub { font-size: 0.68rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; }
        .back-link { display: inline-flex; align-items: center; gap: 6px; color: #94a3b8; font-size: 0.8rem; font-weight: 600; margin-bottom: 1.5rem; transition: color 0.2s; text-decoration: none; }
        .back-link:hover { color: white; }
        .left-title { font-size: 1.4rem; font-weight: 800; line-height: 1.3; margin-bottom: 0.7rem; }
        .left-desc { font-size: 0.85rem; color: #94a3b8; line-height: 1.65; margin-bottom: 2rem; }
        .features { display: flex; flex-direction: column; gap: 12px; }
        .feat-item { display: flex; align-items: flex-start; gap: 12px; background: rgba(255,255,255,0.07); border-radius: 14px; padding: 12px 14px; }
        .feat-icon { width: 34px; height: 34px; border-radius: 9px; background: rgba(249,115,22,0.2); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
        .feat-title { font-size: 0.82rem; font-weight: 700; color: white; }
        .feat-desc { font-size: 0.72rem; color: #94a3b8; margin-top: 1px; }
        .left-footer { font-size: 0.72rem; color: #64748b; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 1rem; margin-top: 2rem; }

        /* RIGHT PANEL */
        .right-panel { width: 58%; background: white; padding: 3rem 2.5rem; overflow-y: auto; max-height: 100vh; }
        .form-section { display: none; }
        .form-section.active { display: block; }

        .form-head { margin-bottom: 1.8rem; }
        .form-head h2 { font-size: 1.6rem; font-weight: 800; color: var(--dark); }
        .form-head p { font-size: 0.85rem; color: var(--text-muted); margin-top: 4px; }

        .alert {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 14px; border-radius: 12px; margin-bottom: 1.2rem; font-size: 0.83rem; font-weight: 600;
        }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }

        .field-group { margin-bottom: 1rem; }
        .field-label { display: block; font-size: 0.78rem; font-weight: 700; color: var(--dark-2); margin-bottom: 6px; }
        .field-input {
            width: 100%; padding: 11px 14px; border: 1.5px solid var(--border); border-radius: 11px;
            font-family: inherit; font-size: 0.875rem; color: var(--dark-2); outline: none;
            background: #f8fafc; transition: all 0.2s;
        }
        .field-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(249,115,22,0.1); background: white; }
        .field-input-icon { position: relative; }
        .field-input-icon i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 13px; }
        .field-input-icon .field-input { padding-left: 38px; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        /* ROLE SELECTOR */
        .role-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 6px; }
        .role-option { border: 2px solid var(--border); border-radius: 12px; padding: 12px 14px; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: all 0.2s; }
        .role-option:has(input:checked) { border-color: var(--primary); background: var(--primary-light); }
        .role-option input { accent-color: var(--primary); }
        .role-option-icon { width: 32px; height: 32px; border-radius: 8px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 13px; color: var(--text-muted); flex-shrink: 0; }
        .role-option:has(input:checked) .role-option-icon { background: var(--primary); color: white; }
        .role-option-text { font-size: 0.78rem; font-weight: 700; color: var(--dark-2); }

        /* WORKER FIELDS */
        .worker-fields {
            background: #f8fafc; border: 1.5px solid var(--border); border-radius: 14px;
            padding: 1.2rem; margin-top: 0.5rem; display: none;
        }
        .worker-fields.visible { display: block; }
        .worker-fields-title { font-size: 0.72rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.8rem; }

        /* SUBMIT BTN */
        .submit-btn {
            width: 100%; padding: 13px; border-radius: 12px; border: none; cursor: pointer;
            font-family: inherit; font-size: 0.9rem; font-weight: 700; color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 8px 20px rgba(249,115,22,0.3); transition: all 0.2s; margin-top: 0.5rem;
        }
        .submit-btn:hover { transform: translateY(-1px); box-shadow: 0 14px 28px rgba(249,115,22,0.42); }

        .toggle-link { font-size: 0.82rem; color: var(--text-muted); text-align: center; margin-top: 1rem; }
        .toggle-link a { color: var(--primary); font-weight: 700; cursor: pointer; text-decoration: none; }
        .toggle-link a:hover { text-decoration: underline; }

        select.field-input { appearance: none; }

        @media (max-width: 768px) {
            /* Prevent horizontal shift and clipping from AOS on mobile/tablet */
            [data-aos="fade-left"],
            [data-aos="fade-right"] {
                transform: translate3d(0, 16px, 0) !important;
            }
            [data-aos="fade-up"],
            [data-aos="zoom-in"] {
                transform: translate3d(0, 16px, 0) !important;
            }
            [data-aos].aos-animate {
                transform: translate3d(0, 0, 0) !important;
                opacity: 1 !important;
            }
        }

        @media (max-width: 700px) {
            body { padding: 1rem 0.75rem; }
            .card { flex-direction: column; border-radius: 20px; }
            .left-panel { width: 100%; padding: 2rem 1.25rem 1.5rem; }
            .features { display: none; }
            .right-panel { width: 100%; padding: 2rem 1.25rem; max-height: none; }
            .grid-2 { grid-template-columns: 1fr; }
            .left-desc { margin-bottom: 0.8rem; }
            .left-footer { margin-top: 1rem; }
        }

        @media (max-width: 480px) {
            .role-grid { grid-template-columns: 1fr; }
            .form-head h2 { font-size: 1.4rem; }
        }
    </style>
</head>
<body>
<div class="card" data-aos="fade-up" data-aos-duration="600">
    <!-- LEFT PANEL -->
    <div class="left-panel">
        <div>
            <a href="home.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
            <div class="logo-area">
                <img src="assets/logo.png" alt="Work Connect Logo" style="height:54px;width:54px;object-fit:contain;border-radius:12px;" />
                <div>
                    <div class="logo-text">Work Connect</div>
                    <div class="logo-sub">Worker Job Portal</div>
                </div>
            </div>
            <div class="left-title">Connect with Skilled Workers Instantly</div>
            <div class="left-desc">The smarter way to find reliable workers or find new work opportunities — fast, local, and trustworthy.</div>
            <div class="features">
                <div class="feat-item">
                    <div class="feat-icon"><i class="fas fa-search"></i></div>
                    <div>
                        <div class="feat-title">Search by Category & Location</div>
                        <div class="feat-desc">Find plumbers, electricians, carpenters, painters & more near you.</div>
                    </div>
                </div>
                <div class="feat-item">
                    <div class="feat-icon"><i class="fas fa-id-badge"></i></div>
                    <div>
                        <div class="feat-title">Detailed Worker Profiles</div>
                        <div class="feat-desc">Compare skills, experience, ratings, and real-time availability.</div>
                    </div>
                </div>
                <div class="feat-item">
                    <div class="feat-icon"><i class="fas fa-paper-plane"></i></div>
                    <div>
                        <div class="feat-title">Direct Requests & Chat</div>
                        <div class="feat-desc">Send hiring requests and communicate seamlessly.</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="left-footer">People Who Need Workers ↔ Workers Who Need Work</div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">

        <!-- LOGIN FORM -->
        <div class="form-section active" id="login-form">
            <div class="form-head">
                <h2>Welcome Back</h2>
                <p>Sign in to your Work Connect account</p>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><i class="fas fa-circle-check"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <div class="field-group">
                    <label class="field-label">Username</label>
                    <div class="field-input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" class="field-input" placeholder="Enter your username" required>
                    </div>
                </div>
                <div class="field-group">
                    <label class="field-label">Password</label>
                    <div class="field-input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="field-input" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="submit-btn">Sign In</button>
            </form>
            <p class="toggle-link">Don't have an account? <a onclick="toggleForm()">Register here</a></p>
        </div>

        <!-- REGISTER FORM -->
        <div class="form-section" id="register-form">
            <div class="form-head">
                <h2>Create Account</h2>
                <p>Join as a client or register as a skilled worker</p>
            </div>

            <form action="register.php" method="POST">
                <div class="grid-2">
                    <div class="field-group">
                        <label class="field-label">Username *</label>
                        <input type="text" name="username" class="field-input" placeholder="e.g. rahul_sharma" required>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Email Address *</label>
                        <input type="email" name="email" class="field-input" placeholder="name@example.com" required>
                    </div>
                </div>
                <div class="field-group">
                    <label class="field-label">Password *</label>
                    <div class="field-input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="field-input" placeholder="At least 6 characters" required>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">I am registering as: *</label>
                    <div class="role-grid">
                        <label class="role-option">
                            <input type="radio" name="role" value="user" checked onchange="toggleWorkerFields(false)">
                            <div class="role-option-icon"><i class="fas fa-user-tie"></i></div>
                            <div class="role-option-text">Person Needing a Worker</div>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="worker" onchange="toggleWorkerFields(true)">
                            <div class="role-option-icon"><i class="fas fa-hard-hat"></i></div>
                            <div class="role-option-text">I am a Skilled Worker</div>
                        </label>
                    </div>
                </div>

                <!-- WORKER FIELDS -->
                <div class="worker-fields" id="worker-fields">
                    <div class="worker-fields-title"><i class="fas fa-hard-hat" style="margin-right:6px;"></i> Worker Details</div>
                    <div class="grid-2">
                        <div class="field-group">
                            <label class="field-label">Work Category *</label>
                            <select name="category_id" class="field-input">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Location / City *</label>
                            <input type="text" name="location" class="field-input" placeholder="e.g. Nallasopara, Mumbai">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="field-group">
                            <label class="field-label">Phone Number</label>
                            <input type="tel" name="phone" class="field-input" placeholder="+91 9876543210">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Skills (comma separated)</label>
                            <input type="text" name="skills" class="field-input" placeholder="e.g. Pipe fitting, Leak repair">
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn" style="margin-top:1rem;">Create Account</button>
            </form>
            <p class="toggle-link">Already have an account? <a onclick="toggleForm()">Sign In</a></p>
        </div>

    </div>
</div>

<script>
    function toggleForm() {
        const login = document.getElementById('login-form');
        const register = document.getElementById('register-form');
        login.classList.toggle('active');
        register.classList.toggle('active');
    }

    function toggleWorkerFields(show) {
        const fields = document.getElementById('worker-fields');
        if (show) {
            fields.classList.add('visible');
        } else {
            fields.classList.remove('visible');
        }
    }
</script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init({ duration: 600, once: true, offset: 30, easing: 'ease-out-cubic' });
</script>
</body>
</html>
