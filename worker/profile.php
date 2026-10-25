<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['worker']);

$worker_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Handle Profile Update Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $error_msg = "Invalid security token. Please refresh and try again.";
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $skills = trim($_POST['skills'] ?? '');
        $experience_years = isset($_POST['experience_years']) ? max(0, (int)$_POST['experience_years']) : 0;
        $experience_details = trim($_POST['experience_details'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $availability = in_array($_POST['availability'] ?? '', ['available', 'busy', 'unavailable']) ? $_POST['availability'] : 'available';
        $daily_rate = trim($_POST['daily_rate'] ?? '');
        $hourly_rate = trim($_POST['hourly_rate'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        try {
            $stmt = $pdo->prepare("
                INSERT INTO worker_profiles 
                    (user_id, full_name, category_id, skills, experience_years, experience_details, location, availability, daily_rate, hourly_rate, phone, bio)
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    full_name = VALUES(full_name),
                    category_id = VALUES(category_id),
                    skills = VALUES(skills),
                    experience_years = VALUES(experience_years),
                    experience_details = VALUES(experience_details),
                    location = VALUES(location),
                    availability = VALUES(availability),
                    daily_rate = VALUES(daily_rate),
                    hourly_rate = VALUES(hourly_rate),
                    phone = VALUES(phone),
                    bio = VALUES(bio)
            ");
            $stmt->execute([
                $worker_id, $full_name, $category_id, $skills, $experience_years, $experience_details, 
                $location, $availability, $daily_rate, $hourly_rate, $phone, $bio
            ]);
            $success_msg = "Worker profile updated successfully!";
        } catch (PDOException $e) {
            $error_msg = "Failed to update profile: " . $e->getMessage();
        }
    }
}

// Fetch current worker profile
$stmt = $pdo->prepare("
    SELECT wp.*, u.username, u.email 
    FROM users u
    LEFT JOIN worker_profiles wp ON u.id = wp.user_id 
    WHERE u.id = ?
");
$stmt->execute([$worker_id]);
$profile = $stmt->fetch();

// Fetch categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Worker Profile - Work Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 font-sans">

<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <?php include '../includes/worker_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-y-auto">
        <!-- Topbar -->
        <header class="bg-white border-b border-slate-200/80 p-4 md:px-8 flex justify-between items-center sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-700 text-xl focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h2 class="text-lg md:text-xl font-bold text-slate-800">Worker Profile Settings</h2>
                    <p class="text-xs text-slate-400">Manage your trade details, skills, location, and availability</p>
                </div>
            </div>
        </header>

        <!-- Body Content -->
        <div class="p-4 md:p-8 space-y-6 max-w-4xl mx-auto w-full">
            
            <?php if (!empty($success_msg)): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold flex items-center">
                    <i class="fas fa-circle-check text-base mr-2"></i> <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="p-4 rounded-2xl bg-red-50 text-red-700 border border-red-200 text-xs font-semibold flex items-center">
                    <i class="fas fa-circle-exclamation text-base mr-2"></i> <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <!-- Banner Graphic Header -->
                <div class="bg-gradient-to-r from-slate-900 to-emerald-950 p-6 md:p-8 text-white flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-600 text-white font-extrabold flex items-center justify-center text-2xl shadow-lg shadow-emerald-600/30">
                        <?php echo strtoupper(substr($_SESSION['username'] ?? 'W', 0, 1)); ?>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold"><?php echo htmlspecialchars($profile['full_name'] ?: $_SESSION['username']); ?></h3>
                        <p class="text-xs text-emerald-300">Registered Worker &bull; <?php echo htmlspecialchars($profile['email']); ?></p>
                    </div>
                </div>

                <!-- Form Section -->
                <form action="" method="POST" class="p-6 md:p-8 space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Full Name -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Full Name</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>" placeholder="e.g. Ramesh Kumar"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>

                        <!-- Work Category -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Primary Work Category *</label>
                            <select name="category_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                                <option value="">-- Select Trade Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo (($profile['category_id'] ?? 0) == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Location -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">City / Location *</label>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($profile['location'] ?? ''); ?>" placeholder="e.g. Nallasopara, Mumbai" required
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number *</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" placeholder="+91 9876543210" required
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>

                        <!-- Availability Status -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Availability</label>
                            <select name="availability" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                                <option value="available" <?php echo (($profile['availability'] ?? '') === 'available') ? 'selected' : ''; ?>>Available for Work</option>
                                <option value="busy" <?php echo (($profile['availability'] ?? '') === 'busy') ? 'selected' : ''; ?>>Currently Busy</option>
                                <option value="unavailable" <?php echo (($profile['availability'] ?? '') === 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Experience in years -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Experience (Years) *</label>
                            <input type="number" min="0" max="60" name="experience_years" value="<?php echo (int)($profile['experience_years'] ?? 1); ?>" required
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>

                        <!-- Daily Rate -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Expected Daily Rate (₹)</label>
                            <input type="text" name="daily_rate" value="<?php echo htmlspecialchars($profile['daily_rate'] ?? ''); ?>" placeholder="e.g. 800"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>

                        <!-- Hourly Rate -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Hourly Rate (₹)</label>
                            <input type="text" name="hourly_rate" value="<?php echo htmlspecialchars($profile['hourly_rate'] ?? ''); ?>" placeholder="e.g. 150"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                    </div>

                    <!-- Skills -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Skills & Specializations</label>
                        <input type="text" name="skills" value="<?php echo htmlspecialchars($profile['skills'] ?? ''); ?>" placeholder="e.g. Pipe fitting, leak repair, sanitary installation, motor repair"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        <p class="text-[10px] text-slate-400 mt-1">Separate individual skills with commas so users can search for them.</p>
                    </div>

                    <!-- Experience Details -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Experience Background & Work History</label>
                        <textarea name="experience_details" rows="3" placeholder="Describe the types of buildings, homes, or jobs you have worked on..."
                                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none"><?php echo htmlspecialchars($profile['experience_details'] ?? ''); ?></textarea>
                    </div>

                    <!-- Bio -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Bio & Introduction</label>
                        <textarea name="bio" rows="3" placeholder="A brief note about your reliability, working hours, and tools..."
                                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-2xl text-xs font-bold transition-all shadow-md shadow-emerald-600/20">
                            Save Worker Profile
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

</body>
</html>
