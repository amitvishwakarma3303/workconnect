<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
checkRole(['admin']);

// Fetch stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$active_jobs = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();

$role_stmt = $pdo->query("SELECT roles.role_name, COUNT(users.id) as count FROM roles LEFT JOIN users ON roles.id = users.role_id GROUP BY roles.role_name");
$role_counts = $role_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Recent Users
$recent_users = $pdo->query("SELECT users.*, roles.role_name FROM users JOIN roles ON users.role_id = roles.id ORDER BY users.created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Work Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 font-sans">

<div class="flex h-screen overflow-hidden">
    
    <!-- Sidebar -->
    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-y-auto">
        
        <!-- Header -->
        <header class="bg-white border-b border-gray-100 p-6 flex justify-between items-center sticky top-0 z-10">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="md:hidden text-indigo-900">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                <h2 class="text-xl font-bold text-gray-800">System Overview</h2>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-gray-900"><?php echo $_SESSION['username']; ?></p>
                    <p class="text-[10px] text-indigo-600 font-bold uppercase tracking-widest leading-none">Super Administrator</p>
                </div>
                <div class="w-10 h-10 bg-indigo-900 text-white rounded-full flex items-center justify-center font-bold">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
            </div>
        </header>

        <div class="p-6 md:p-10 space-y-10">
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Users -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-50 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-blue-50 p-3 rounded-2xl text-blue-600"><i class="fas fa-users text-xl"></i></div>
                        <span class="text-green-500 text-xs font-bold">+12%</span>
                    </div>
                    <p class="text-sm text-gray-400 font-medium">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo number_format($total_users); ?></p>
                </div>
                <!-- Active Jobs -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-50 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-indigo-50 p-3 rounded-2xl text-indigo-600"><i class="fas fa-briefcase text-xl"></i></div>
                        <span class="text-indigo-500 text-xs font-bold">Live</span>
                    </div>
                    <p class="text-sm text-gray-400 font-medium">Active Jobs</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo number_format($active_jobs); ?></p>
                </div>
                <!-- Employers -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-50 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-purple-50 p-3 rounded-2xl text-purple-600"><i class="fas fa-user-tie text-xl"></i></div>
                        <span class="text-xs font-bold text-gray-400">Hub</span>
                    </div>
                    <p class="text-sm text-gray-400 font-medium">Employers</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo number_format($role_counts['employer'] ?? 0); ?></p>
                </div>
                <!-- Employees -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-50 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-green-50 p-3 rounded-2xl text-green-600"><i class="fas fa-hard-hat text-xl"></i></div>
                        <span class="text-xs font-bold text-gray-400">Workforce</span>
                    </div>
                    <p class="text-sm text-gray-400 font-medium">Employees</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo number_format($role_counts['employee'] ?? 0); ?></p>
                </div>
            </div>

            <!-- Management Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                
                <!-- Recent Registrations -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-50 p-8">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-xl font-bold text-gray-900">Recent Registrations</h3>
                        <a href="users.php" class="text-sm font-bold text-indigo-600 hover:underline">Manage All</a>
                    </div>
                    <div class="space-y-6">
                        <?php foreach ($recent_users as $user): ?>
                        <div class="flex items-center justify-between group">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900"><?php echo htmlspecialchars($user['username']); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider <?php echo $user['role_name'] == 'employer' ? 'bg-purple-50 text-purple-600' : 'bg-green-50 text-green-600'; ?>">
                                <?php echo $user['role_name']; ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Quick Actions / System Health -->
                <div class="space-y-6">
                    <div class="bg-indigo-900 rounded-3xl p-8 text-white shadow-xl shadow-indigo-100 overflow-hidden relative">
                         <i class="fas fa-shield-alt absolute -right-10 -bottom-10 text-9xl text-white/5"></i>
                         <h3 class="text-xl font-bold mb-2">System Health</h3>
                         <p class="text-indigo-200 text-sm mb-8">Everything is running smoothly. 0 critical issues reported today.</p>
                         <div class="grid grid-cols-2 gap-4">
                             <div class="bg-white/10 p-4 rounded-2xl border border-white/10">
                                 <p class="text-[10px] uppercase font-bold text-indigo-300">Server Load</p>
                                 <p class="text-xl font-bold">14%</p>
                             </div>
                             <div class="bg-white/10 p-4 rounded-2xl border border-white/10">
                                 <p class="text-[10px] uppercase font-bold text-indigo-300">DB Connections</p>
                                 <p class="text-xl font-bold">Active</p>
                             </div>
                         </div>
                    </div>
                    
                    <div class="bg-white rounded-3xl border border-gray-50 p-6 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                             <div class="bg-red-50 p-4 rounded-2xl text-red-600"><i class="fas fa-exclamation-triangle"></i></div>
                             <div>
                                 <h4 class="font-bold text-gray-900">Pending Flags</h4>
                                 <p class="text-sm text-gray-400">3 jobs need moderation review.</p>
                             </div>
                        </div>
                        <a href="jobs.php" class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-50 text-gray-900 hover:bg-gray-100"><i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>

            </div>

        </div>

    </main>

</div>

</body>
</html>
