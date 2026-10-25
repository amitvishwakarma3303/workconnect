<?php
// notifications.php
require_once 'includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
if ($role === 'admin') {
    header("Location: admin/dashboard.php");
    exit();
}

// Mark all as read when opening this page
$pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?")->execute([$user_id]);

// Fetch all notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Work Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden font-sans">
    <?php 
    if ($role === 'worker') {
        include 'includes/worker_sidebar.php';
    } else {
        include 'includes/user_sidebar.php';
    }
    ?>

    <main class="flex-grow flex flex-col overflow-y-auto bg-slate-50">
        <header class="bg-white border-b border-slate-200 p-4 md:px-8 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center space-x-4">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-800 text-xl">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 class="text-xl font-bold text-slate-800">Your Notifications</h2>
            </div>
        </header>

        <div class="p-4 md:p-8 max-w-4xl mx-auto w-full">
            <?php if (empty($notifications)): ?>
                <div class="bg-white p-12 rounded-3xl text-center border border-slate-200/80 shadow-sm">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                        <i class="fas fa-bell-slash text-2xl"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">No notifications yet</h3>
                    <p class="text-xs text-slate-400 mt-1">We'll alert you here when clients send work requests or update statuses.</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($notifications as $n): ?>
                        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-start space-x-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 
                                <?php 
                                if($n['type'] == 'hiring') echo 'bg-emerald-50 text-emerald-600';
                                elseif($n['type'] == 'application') echo 'bg-indigo-50 text-indigo-600';
                                elseif($n['type'] == 'rejection') echo 'bg-red-50 text-red-600';
                                else echo 'bg-slate-100 text-slate-600';
                                ?>">
                                <i class="fas <?php 
                                    if($n['type'] == 'hiring') echo 'fa-check';
                                    elseif($n['type'] == 'application') echo 'fa-paper-plane';
                                    elseif($n['type'] == 'rejection') echo 'fa-times';
                                    else echo 'fa-bell';
                                ?>"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs md:text-sm text-slate-800 leading-relaxed"><?php echo htmlspecialchars($n['message']); ?></p>
                                <p class="text-[10px] text-slate-400 mt-1.5"><?php echo date('M d, Y &bull; h:i A', strtotime($n['created_at'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
