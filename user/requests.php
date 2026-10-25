<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
checkRole(['user']);

$user_id = $_SESSION['user_id'];
$status_filter = trim($_GET['status'] ?? '');

$query = "
    SELECT wr.*, u.username as worker_name, wp.phone as worker_phone, wp.skills, wp.daily_rate,
           c.name as category_name, c.icon as category_icon
    FROM work_requests wr
    JOIN users u ON wr.worker_id = u.id
    LEFT JOIN worker_profiles wp ON u.id = wp.user_id
    LEFT JOIN categories c ON wr.category_id = c.id
    WHERE wr.user_id = ?
";
$params = [$user_id];

if (!empty($status_filter)) {
    $query .= " AND wr.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY wr.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Work Requests - Work Connect</title>
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
        <!-- Topbar -->
        <header class="bg-white border-b border-slate-200/80 p-4 md:px-8 flex justify-between items-center sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-700 text-xl focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h2 class="text-lg md:text-xl font-bold text-slate-800">My Work Requests</h2>
                    <p class="text-xs text-slate-400">Track all worker hiring inquiries and task progress</p>
                </div>
            </div>

            <a href="search_workers.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-600/20 flex items-center space-x-1.5">
                <i class="fas fa-plus"></i>
                <span>Find Another Worker</span>
            </a>
        </header>

        <!-- Body Content -->
        <div class="p-4 md:p-8 space-y-6 max-w-6xl mx-auto w-full">
            
            <!-- Filters bar -->
            <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="requests.php" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?php echo empty($status_filter) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
                        All Requests (<?php echo count($requests); ?>)
                    </a>
                    <a href="requests.php?status=pending" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?php echo ($status_filter === 'pending') ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
                        Pending
                    </a>
                    <a href="requests.php?status=accepted" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?php echo ($status_filter === 'accepted') ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
                        Accepted / Active
                    </a>
                    <a href="requests.php?status=completed" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?php echo ($status_filter === 'completed') ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
                        Completed
                    </a>
                    <a href="requests.php?status=rejected" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?php echo ($status_filter === 'rejected') ? 'bg-red-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
                        Declined
                    </a>
                </div>
            </div>

            <!-- List of Requests -->
            <div class="space-y-4">
                <?php if (empty($requests)): ?>
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center max-w-md mx-auto shadow-sm">
                        <div class="w-20 h-20 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center text-3xl mx-auto mb-4">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">No Work Requests</h3>
                        <p class="text-xs text-slate-500 mt-1">You haven't sent any work requests with this status filter.</p>
                        <a href="search_workers.php" class="inline-block mt-4 bg-indigo-600 text-white px-5 py-2 rounded-xl text-xs font-bold hover:bg-indigo-700 transition-colors">
                            Find a Worker to Hire
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($requests as $r): ?>
                        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:shadow-md transition-all">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 font-extrabold flex items-center justify-center text-lg">
                                        <?php echo strtoupper(substr($r['worker_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <h3 class="font-bold text-slate-800 text-base"><?php echo htmlspecialchars($r['worker_name']); ?></h3>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-600">
                                                <i class="fas <?php echo htmlspecialchars($r['category_icon'] ?: 'fa-tools'); ?> mr-1"></i>
                                                <?php echo htmlspecialchars($r['category_name'] ?: 'Worker'); ?>
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-0.5">Requested on <?php echo date('M d, Y &bull; h:i A', strtotime($r['created_at'])); ?></p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <span class="px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                        <?php 
                                        if ($r['status'] === 'accepted') echo 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                        elseif ($r['status'] === 'completed') echo 'bg-blue-50 text-blue-700 border border-blue-200';
                                        elseif ($r['status'] === 'rejected') echo 'bg-red-50 text-red-700 border border-red-200';
                                        else echo 'bg-amber-50 text-amber-700 border border-amber-200';
                                        ?>">
                                        <?php echo htmlspecialchars($r['status']); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Task Information -->
                            <div class="py-4 space-y-2">
                                <h4 class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($r['title']); ?></h4>
                                <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-wrap"><?php echo htmlspecialchars($r['description']); ?></p>
                                
                                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 pt-2">
                                    <span><i class="fas fa-location-dot text-indigo-500 mr-1.5"></i><?php echo htmlspecialchars($r['location']); ?></span>
                                    <?php if(!empty($r['work_date'])): ?>
                                        <span><i class="fas fa-calendar-alt text-indigo-500 mr-1.5"></i>Date: <?php echo date('M d, Y', strtotime($r['work_date'])); ?></span>
                                    <?php endif; ?>
                                    <?php if(!empty($r['budget'])): ?>
                                        <span><i class="fas fa-indian-rupee-sign text-indigo-500 mr-1.5"></i>Offered Budget: <strong>₹ <?php echo htmlspecialchars($r['budget']); ?></strong></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Actions Footer -->
                            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                                <div class="text-xs text-slate-400">
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <span class="text-amber-600"><i class="fas fa-clock mr-1"></i> Awaiting worker's response</span>
                                    <?php elseif ($r['status'] === 'accepted'): ?>
                                        <span class="text-emerald-600 font-semibold"><i class="fas fa-check mr-1"></i> Worker accepted! You can now call or chat directly.</span>
                                    <?php elseif ($r['status'] === 'completed'): ?>
                                        <span class="text-blue-600 font-semibold"><i class="fas fa-circle-check mr-1"></i> Work has been completed successfully!</span>
                                    <?php else: ?>
                                        <span class="text-red-500"><i class="fas fa-circle-xmark mr-1"></i> Worker is unavailable for this request.</span>
                                    <?php endif; ?>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <a href="worker_profile.php?id=<?php echo $r['worker_id']; ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-colors">
                                        View Worker Profile
                                    </a>
                                    <?php if ($r['status'] === 'accepted' || $r['status'] === 'completed'): ?>
                                        <a href="chat.php?worker_id=<?php echo $r['worker_id']; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center space-x-1.5">
                                            <i class="fas fa-comment-dots"></i>
                                            <span>Chat with Worker</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

</body>
</html>
