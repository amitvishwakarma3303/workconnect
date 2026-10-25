<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
checkRole(['admin']);

// Fetch stats aligned with the synopsis
$total_users = $pdo->query("SELECT COUNT(*) FROM users JOIN roles ON users.role_id = roles.id WHERE roles.role_name = 'user'")->fetchColumn();
$total_workers = $pdo->query("SELECT COUNT(*) FROM users JOIN roles ON users.role_id = roles.id WHERE roles.role_name = 'worker'")->fetchColumn();
$total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$total_requests = $pdo->query("SELECT COUNT(*) FROM work_requests")->fetchColumn();

// Recent Workers
$recent_workers = $pdo->query("
    SELECT u.id, u.username, u.email, u.created_at, wp.full_name, wp.location, wp.experience_years, c.name as category_name
    FROM users u
    JOIN roles r ON u.role_id = r.id
    LEFT JOIN worker_profiles wp ON u.id = wp.user_id
    LEFT JOIN categories c ON wp.category_id = c.id
    WHERE r.role_name = 'worker'
    ORDER BY u.created_at DESC LIMIT 5
")->fetchAll();

// Recent Work Requests
$recent_requests = $pdo->query("
    SELECT wr.*, u1.username as client_name, u2.username as worker_name, c.name as category_name
    FROM work_requests wr
    JOIN users u1 ON wr.user_id = u1.id
    JOIN users u2 ON wr.worker_id = u2.id
    LEFT JOIN categories c ON wr.category_id = c.id
    ORDER BY wr.created_at DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Work Connect</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 font-sans">

<div class="flex h-screen overflow-hidden">
    <!-- Admin Sidebar -->
    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-y-auto">
        <!-- Top Header -->
        <header class="bg-white border-b border-slate-200/80 p-4 md:px-8 flex justify-between items-center sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-700 text-xl focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h2 class="text-lg md:text-xl font-bold text-slate-800">Admin Control Center</h2>
                    <p class="text-xs text-slate-400">Work Connect &bull; Worker Job Portal Administration</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-bold uppercase">Super Admin</span>
            </div>
        </header>

        <!-- Content -->
        <div class="p-4 md:p-8 space-y-8 max-w-7xl mx-auto w-full">
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Clients / Users -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Registered Clients</p>
                        <p class="text-3xl font-extrabold text-slate-800 mt-1"><?php echo number_format($total_users); ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                        <i class="fas fa-users"></i>
                    </div>
                </div>

                <!-- Workers -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Skilled Workers</p>
                        <p class="text-3xl font-extrabold text-emerald-600 mt-1"><?php echo number_format($total_workers); ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fas fa-hard-hat"></i>
                    </div>
                </div>

                <!-- Work Categories -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Work Categories</p>
                        <p class="text-3xl font-extrabold text-indigo-600 mt-1"><?php echo number_format($total_categories); ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>

                <!-- Total Work Requests -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Work Requests</p>
                        <p class="text-3xl font-extrabold text-amber-500 mt-1"><?php echo number_format($total_requests); ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                </div>
            </div>

            <!-- Management Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Registered Workers -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-base font-bold text-slate-800">Recent Registered Workers</h3>
                        <a href="workers.php" class="text-xs font-bold text-indigo-600 hover:underline">Manage All</a>
                    </div>
                    <div class="space-y-4">
                        <?php if (empty($recent_workers)): ?>
                            <p class="text-xs text-slate-400 py-6 text-center">No workers registered yet.</p>
                        <?php else: ?>
                            <?php foreach ($recent_workers as $w): ?>
                                <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50/70 border border-slate-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-sm">
                                            <?php echo strtoupper(substr($w['username'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 text-xs"><?php echo htmlspecialchars($w['full_name'] ?: $w['username']); ?></p>
                                            <p class="text-[10px] text-slate-400"><?php echo htmlspecialchars($w['category_name'] ?: 'General'); ?> &bull; <?php echo htmlspecialchars($w['location'] ?: 'Local'); ?></p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-400"><?php echo date('M d, Y', strtotime($w['created_at'])); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Work Requests -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-base font-bold text-slate-800">Recent Work Requests</h3>
                        <a href="requests.php" class="text-xs font-bold text-indigo-600 hover:underline">View All</a>
                    </div>
                    <div class="space-y-4">
                        <?php if (empty($recent_requests)): ?>
                            <p class="text-xs text-slate-400 py-6 text-center">No work requests made yet.</p>
                        <?php else: ?>
                            <?php foreach ($recent_requests as $r): ?>
                                <div class="p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100 space-y-1.5">
                                    <div class="flex justify-between items-start">
                                        <p class="font-bold text-slate-800 text-xs line-clamp-1"><?php echo htmlspecialchars($r['title']); ?></p>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase
                                            <?php 
                                            if ($r['status'] === 'accepted') echo 'bg-emerald-50 text-emerald-700';
                                            elseif ($r['status'] === 'completed') echo 'bg-blue-50 text-blue-700';
                                            elseif ($r['status'] === 'rejected') echo 'bg-red-50 text-red-700';
                                            else echo 'bg-amber-50 text-amber-700';
                                            ?>">
                                            <?php echo htmlspecialchars($r['status']); ?>
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-[10px] text-slate-400">
                                        <span><?php echo htmlspecialchars($r['client_name']); ?> &rarr; <?php echo htmlspecialchars($r['worker_name']); ?></span>
                                        <span><?php echo date('M d', strtotime($r['created_at'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

</body>
</html>
