<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['employee']);

$user_id = $_SESSION['user_id'];
$message = '';
$error_message = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $error_message = "Invalid request token.";
    } else {
    $skills = $_POST['skills'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $bio = $_POST['bio'] ?? '';

    try {
        // Correct existence check: query the profile table directly.
        $stmt = $pdo->prepare("SELECT user_id FROM employee_profiles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $exists = (bool) $stmt->fetchColumn();

        try {
            if ($exists) {
                $stmt = $pdo->prepare("UPDATE employee_profiles SET skills = ?, phone = ?, bio = ? WHERE user_id = ?");
                $stmt->execute([$skills, $phone, $bio, $user_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO employee_profiles (user_id, skills, phone, bio) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $skills, $phone, $bio]);
            }
        } catch (PDOException $e) {
            // Fallback for older schemas where `bio` column may not exist.
            if ($exists) {
                $stmt = $pdo->prepare("UPDATE employee_profiles SET skills = ?, phone = ? WHERE user_id = ?");
                $stmt->execute([$skills, $phone, $user_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO employee_profiles (user_id, skills, phone) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $skills, $phone]);
            }
        }
        $message = "Profile updated successfully!";
    } catch (PDOException $e) {
        $error_message = "Failed to update profile.";
    }
    }
}

// Fetch current profile
$stmt = $pdo->prepare("SELECT * FROM employee_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Work Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 font-sans">

<div class="flex h-screen overflow-hidden">
    <?php include '../includes/employee_sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-y-auto">
        <header class="bg-white p-4 flex justify-between items-center shadow-sm">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="md:hidden text-xl text-indigo-900">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 class="text-xl font-semibold">Profile Settings</h2>
            </div>
        </header>

        <div class="p-4 md:p-8 max-w-4xl mx-auto w-full">
            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-200 text-green-700 px-6 py-4 rounded-2xl mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-200 text-red-700 px-6 py-4 rounded-2xl mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-indigo-600 h-32 md:h-48 relative">
                    <div class="absolute -bottom-12 left-8 md:bottom-[-40px] md:left-12">
                        <div class="w-24 h-24 md:w-32 md:h-32 bg-white rounded-full p-2">
                            <div class="w-full h-full bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 text-3xl md:text-5xl font-bold border-4 border-white">
                                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-8 pt-16 md:pt-20">
                    <div class="flex justify-between items-start mb-10">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($_SESSION['username']); ?></h1>
                            <p class="text-gray-500"><?php echo htmlspecialchars($_SESSION['email'] ?? 'Member since 2026'); ?></p>
                        </div>
                        <span class="bg-green-100 text-green-600 px-4 py-1.5 rounded-full text-xs font-bold uppercase">Employee</span>
                    </div>

                    <form action="" method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Professional Skills</label>
                                <input type="text" name="skills" value="<?php echo htmlspecialchars($profile['skills'] ?? ''); ?>" placeholder="e.g. Painting, Plumbing, Welding" 
                                       class="w-full px-4 py-3 rounded-xl bg-gray-50 border-gray-200 outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                                <p class="text-[11px] text-gray-400 mt-2">Separate skills with commas.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" placeholder="+91 00000 00000" 
                                       class="w-full px-4 py-3 rounded-xl bg-gray-50 border-gray-200 outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Short Bio</label>
                            <textarea name="bio" rows="4" placeholder="Tell employers a bit about your experience..." 
                                     class="w-full px-4 py-3 rounded-xl bg-gray-50 border-gray-200 outline-none focus:ring-2 focus:ring-indigo-500 transition-all"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="pt-6 border-t border-gray-50 flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all">
                                Save Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
