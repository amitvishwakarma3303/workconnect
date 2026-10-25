<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
checkRole(['user']);

$user_id = $_SESSION['user_id'];

// Fetch Stats for this user
$stmt_sent = $pdo->prepare("SELECT COUNT(*) FROM work_requests WHERE user_id = ?");
$stmt_sent->execute([$user_id]);
$total_sent = $stmt_sent->fetchColumn();

$stmt_accepted = $pdo->prepare("SELECT COUNT(*) FROM work_requests WHERE user_id = ? AND status = 'accepted'");
$stmt_accepted->execute([$user_id]);
$total_accepted = $stmt_accepted->fetchColumn();

$stmt_completed = $pdo->prepare("SELECT COUNT(*) FROM work_requests WHERE user_id = ? AND status = 'completed'");
$stmt_completed->execute([$user_id]);
$total_completed = $stmt_completed->fetchColumn();

// Fetch Categories with worker counts
$categories = $pdo->query("
    SELECT c.*, COUNT(wp.user_id) as worker_count 
    FROM categories c 
    LEFT JOIN worker_profiles wp ON c.id = wp.category_id 
    GROUP BY c.id 
    ORDER BY c.name ASC
")->fetchAll();

// Fetch Recent Work Requests
$stmt_recent_req = $pdo->prepare("
    SELECT wr.*, u.username as worker_name, c.name as category_name, wp.phone as worker_phone
    FROM work_requests wr
    JOIN users u ON wr.worker_id = u.id
    LEFT JOIN categories c ON wr.category_id = c.id
    LEFT JOIN worker_profiles wp ON u.id = wp.user_id
    WHERE wr.user_id = ?
    ORDER BY wr.created_at DESC LIMIT 4
");
$stmt_recent_req->execute([$user_id]);
$recent_requests = $stmt_recent_req->fetchAll();

// Fetch Featured / Available Workers
$stmt_workers = $pdo->query("
    SELECT u.id, u.username, wp.full_name, wp.skills, wp.location, wp.availability, wp.experience_years, wp.daily_rate, c.name as category_name, c.icon as category_icon
    FROM users u
    JOIN worker_profiles wp ON u.id = wp.user_id
    LEFT JOIN categories c ON wp.category_id = c.id
    WHERE wp.availability = 'available'
    ORDER BY wp.experience_years DESC LIMIT 6
");
$available_workers = $stmt_workers->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Work Connect</title>
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
    <!-- Sidebar -->
    <?php include '../includes/user_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-y-auto">
        <!-- Topbar Header -->
        <header class="bg-white border-b border-slate-200/80 p-4 md:px-8 flex justify-between items-center sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-700 text-xl focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h2 class="text-lg md:text-xl font-bold text-slate-800">Find & Hire Workers</h2>
                    <p class="text-xs text-slate-400 hidden sm:block">Work Connect &bull; Worker Job Portal</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <a href="search_workers.php" class="inline-flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-600/20">
                    <i class="fas fa-search"></i>
                    <span>Search Workers</span>
                </a>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center text-sm">
                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                </div>
            </div>
        </header>

        <!-- Body Content -->
        <div class="p-4 md:p-8 space-y-8 max-w-7xl mx-auto w-full">
            
            <!-- Hero Banner -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-indigo-900 rounded-3xl p-6 md:p-10 text-white shadow-xl relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 opacity-10 text-9xl">
                    <i class="fas fa-toolbox"></i>
                </div>
                <div class="relative z-10 max-w-2xl">
                    <span class="inline-block px-3 py-1 bg-indigo-500/30 text-indigo-200 rounded-full text-xs font-bold uppercase tracking-wider mb-3">
                        Person Who Needs a Worker
                    </span>
                    <h1 class="text-2xl md:text-4xl font-bold leading-tight">
                        Need an electrician, plumber, or carpenter?
                    </h1>
                    <p class="text-sm md:text-base text-slate-300 mt-2 leading-relaxed">
                        Search skilled workers by trade category, filter by experience and location, view their full profiles, and hire them instantly.
                    </p>

                    <!-- Instant Search Form Bar -->
                    <form action="search_workers.php" method="GET" class="mt-6 flex flex-col sm:flex-row gap-2 bg-white/10 p-2 rounded-2xl backdrop-blur-md border border-white/20">
                        <div class="flex-1 relative">
                            <i class="fas fa-layer-group absolute left-4 top-3.5 text-indigo-300 text-sm"></i>
                            <select name="category" class="w-full pl-10 pr-4 py-2.5 bg-white text-slate-800 rounded-xl text-xs font-semibold focus:outline-none">
                                <option value="">All Categories (Plumber, Electrician...)</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex-1 relative">
                            <i class="fas fa-location-dot absolute left-4 top-3.5 text-slate-400 text-sm"></i>
                            <input type="text" name="location" placeholder="City or area (e.g. Nallasopara)" class="w-full pl-10 pr-4 py-2.5 bg-white text-slate-800 rounded-xl text-xs font-semibold focus:outline-none">
                        </div>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center space-x-2">
                            <i class="fas fa-search"></i>
                            <span>Find</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Requests Sent</p>
                        <p class="text-3xl font-extrabold text-slate-800 mt-1"><?php echo $total_sent; ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hired & Connected</p>
                        <p class="text-3xl font-extrabold text-emerald-600 mt-1"><?php echo $total_accepted; ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fas fa-handshake"></i>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tasks Completed</p>
                        <p class="text-3xl font-extrabold text-blue-600 mt-1"><?php echo $total_completed; ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
            </div>

            <!-- Browse by Categories (from Synopsis Section 4) -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Browse Workers by Category</h3>
                        <p class="text-xs text-slate-400">Select a trade to find matching skilled workers</p>
                    </div>
                    <a href="search_workers.php" class="text-xs font-bold text-indigo-600 hover:underline">View All</a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <?php foreach ($categories as $cat): ?>
                        <a href="search_workers.php?category=<?php echo $cat['id']; ?>" class="bg-white p-4 rounded-2xl border border-slate-200/70 hover:border-indigo-400 hover:shadow-md transition-all text-center group flex flex-col items-center">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors flex items-center justify-center text-lg mb-2">
                                <i class="fas <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-indigo-600 transition-colors"><?php echo htmlspecialchars($cat['name']); ?></span>
                            <span class="text-[10px] text-slate-400 mt-0.5"><?php echo $cat['worker_count']; ?> available</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Featured Workers & Recent Work Requests Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Available Workers (2 cols) -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-800">Available Workers Nearby</h3>
                        <a href="search_workers.php" class="text-xs font-bold text-indigo-600 hover:underline">See All</a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php if (empty($available_workers)): ?>
                            <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-slate-200/70 text-slate-400 text-xs">
                                No workers registered yet.
                            </div>
                        <?php else: ?>
                            <?php foreach ($available_workers as $wk): ?>
                                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-base">
                                                    <?php echo strtoupper(substr($wk['username'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($wk['full_name'] ?: $wk['username']); ?></h4>
                                                    <span class="inline-block text-[11px] font-semibold text-indigo-600">
                                                        <?php echo htmlspecialchars($wk['category_name'] ?: 'General Worker'); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1 animate-pulse"></span> Available
                                            </span>
                                        </div>

                                        <p class="text-xs text-slate-500 line-clamp-2 mb-3">
                                            <?php echo htmlspecialchars($wk['skills'] ?: 'Experienced in household and on-site works.'); ?>
                                        </p>

                                        <div class="flex items-center justify-between text-[11px] text-slate-500 pb-3 border-b border-slate-100">
                                            <span><i class="fas fa-location-dot text-slate-400 mr-1"></i><?php echo htmlspecialchars($wk['location'] ?: 'Local'); ?></span>
                                            <span><i class="fas fa-briefcase text-slate-400 mr-1"></i><?php echo $wk['experience_years']; ?> yrs exp</span>
                                        </div>
                                    </div>

                                    <div class="pt-3 flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-800">
                                            ₹ <?php echo htmlspecialchars($wk['daily_rate'] ?: '500-800'); ?> <span class="text-[10px] text-slate-400 font-normal">/ day</span>
                                        </span>
                                        <a href="worker_profile.php?id=<?php echo $wk['id']; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                            View Profile
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Work Requests Sent (1 col) -->
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-800">My Work Requests</h3>
                        <a href="requests.php" class="text-xs font-bold text-indigo-600 hover:underline">View All</a>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4">
                        <?php if (empty($recent_requests)): ?>
                            <div class="text-center py-10 text-slate-400">
                                <i class="fas fa-inbox text-3xl mb-2 text-slate-300"></i>
                                <p class="text-xs">You have not sent any work requests yet.</p>
                                <a href="search_workers.php" class="inline-block mt-3 text-xs font-bold text-indigo-600 hover:underline">Find a Worker</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_requests as $req): ?>
                                <div class="p-3.5 rounded-xl border border-slate-100 hover:border-slate-200 bg-slate-50/50 transition-all space-y-2">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-xs font-bold text-slate-800 line-clamp-1"><?php echo htmlspecialchars($req['title']); ?></p>
                                            <p class="text-[11px] text-indigo-600 font-medium">To: <?php echo htmlspecialchars($req['worker_name']); ?></p>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase
                                            <?php 
                                            if($req['status'] === 'accepted') echo 'bg-emerald-100 text-emerald-700';
                                            elseif($req['status'] === 'completed') echo 'bg-blue-100 text-blue-700';
                                            elseif($req['status'] === 'rejected') echo 'bg-red-100 text-red-700';
                                            else echo 'bg-amber-100 text-amber-700';
                                            ?>">
                                            <?php echo htmlspecialchars($req['status']); ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] text-slate-400">
                                        <span><i class="fas fa-calendar-alt mr-1"></i><?php echo date('M d, Y', strtotime($req['created_at'])); ?></span>
                                        <?php if($req['status'] === 'accepted'): ?>
                                            <a href="chat.php?worker_id=<?php echo $req['worker_id']; ?>" class="text-indigo-600 font-bold hover:underline">
                                                <i class="fas fa-comment-dots mr-1"></i> Chat
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

</body>
</html>
