<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['worker']);

$worker_id = $_SESSION['user_id'];

// Handle quick availability toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_availability'])) {
    if (csrf_validate($_POST['csrf_token'] ?? '')) {
        $new_avail = $_POST['new_availability'] === 'available' ? 'available' : 'busy';
        $stmtAv = $pdo->prepare("UPDATE worker_profiles SET availability = ? WHERE user_id = ?");
        $stmtAv->execute([$new_avail, $worker_id]);
        header("Location: dashboard.php?msg=Status updated");
        exit();
    }
}

// Fetch worker profile & stats
$stmtProfile = $pdo->prepare("
    SELECT wp.*, c.name as category_name, c.icon as category_icon, u.username, u.email
    FROM users u
    LEFT JOIN worker_profiles wp ON u.id = wp.user_id
    LEFT JOIN categories c ON wp.category_id = c.id
    WHERE u.id = ?
");
$stmtProfile->execute([$worker_id]);
$profile = $stmtProfile->fetch();

// Calculate stats
$stmt_pending = $pdo->prepare("SELECT COUNT(*) FROM work_requests WHERE worker_id = ? AND status = 'pending'");
$stmt_pending->execute([$worker_id]);
$pending_count = $stmt_pending->fetchColumn();

$stmt_accepted = $pdo->prepare("SELECT COUNT(*) FROM work_requests WHERE worker_id = ? AND status = 'accepted'");
$stmt_accepted->execute([$worker_id]);
$accepted_count = $stmt_accepted->fetchColumn();

$stmt_completed = $pdo->prepare("SELECT COUNT(*) FROM work_requests WHERE worker_id = ? AND status = 'completed'");
$stmt_completed->execute([$worker_id]);
$completed_count = $stmt_completed->fetchColumn();

// Fetch recent incoming requests
$stmtRecent = $pdo->prepare("
    SELECT wr.*, u.username as client_name, u.email as client_email
    FROM work_requests wr
    JOIN users u ON wr.user_id = u.id
    WHERE wr.worker_id = ?
    ORDER BY wr.created_at DESC LIMIT 5
");
$stmtRecent->execute([$worker_id]);
$recent_requests = $stmtRecent->fetchAll();

$csrf_token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard - Work Connect</title>
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
                    <h2 class="text-lg md:text-xl font-bold text-slate-800">Worker Dashboard</h2>
                    <p class="text-xs text-slate-400">Welcome, <?php echo htmlspecialchars($profile['full_name'] ?: $_SESSION['username']); ?></p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <!-- Status Switcher -->
                <form method="POST" class="inline">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="toggle_availability" value="1">
                    <input type="hidden" name="new_availability" value="<?php echo ($profile['availability'] === 'available') ? 'busy' : 'available'; ?>">
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all border flex items-center space-x-1.5
                        <?php echo ($profile['availability'] === 'available') ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'; ?>">
                        <span class="w-2 h-2 rounded-full <?php echo ($profile['availability'] === 'available') ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'; ?>"></span>
                        <span><?php echo ($profile['availability'] === 'available') ? 'Available for Work' : 'Currently Busy'; ?></span>
                    </button>
                </form>

                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 font-bold flex items-center justify-center text-sm">
                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'W', 0, 1)); ?>
                </div>
            </div>
        </header>

        <!-- Body Content -->
        <div class="p-4 md:p-8 space-y-8 max-w-7xl mx-auto w-full">
            
            <!-- Welcome Profile Banner -->
            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 rounded-3xl p-6 md:p-8 text-white shadow-xl relative overflow-hidden flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="relative z-10 max-w-xl">
                    <span class="inline-block px-3 py-1 bg-emerald-500/20 text-emerald-300 rounded-full text-xs font-bold uppercase tracking-wider mb-2">
                        Skilled Worker Profile
                    </span>
                    <h1 class="text-2xl md:text-3xl font-bold">
                        <?php echo htmlspecialchars($profile['full_name'] ?: $_SESSION['username']); ?>
                    </h1>
                    <p class="text-xs md:text-sm text-slate-300 mt-1">
                        Category: <strong class="text-white"><?php echo htmlspecialchars($profile['category_name'] ?: 'Not selected yet'); ?></strong> &bull; 
                        Location: <strong class="text-white"><?php echo htmlspecialchars($profile['location'] ?: 'Not set'); ?></strong> &bull; 
                        Daily Rate: <strong class="text-white">₹ <?php echo htmlspecialchars($profile['daily_rate'] ?: 'Negotiable'); ?></strong>
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="profile.php" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md shadow-emerald-600/20 flex items-center space-x-1.5">
                        <i class="fas fa-user-edit"></i>
                        <span>Edit Profile & Skills</span>
                    </a>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">New Work Requests</p>
                        <p class="text-3xl font-extrabold text-amber-500 mt-1"><?php echo $pending_count; ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                        <i class="fas fa-inbox"></i>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active / In-Progress</p>
                        <p class="text-3xl font-extrabold text-emerald-600 mt-1"><?php echo $accepted_count; ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fas fa-handshake"></i>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jobs Completed</p>
                        <p class="text-3xl font-extrabold text-blue-600 mt-1"><?php echo $completed_count; ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                        <i class="fas fa-check-double"></i>
                    </div>
                </div>
            </div>

            <!-- Recent Work Requests List -->
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Incoming Work Requests</h3>
                        <p class="text-xs text-slate-400">Respond to clients requesting your services</p>
                    </div>
                    <a href="requests.php" class="text-xs font-bold text-emerald-600 hover:underline">View All Requests</a>
                </div>

                <div class="space-y-4">
                    <?php if (empty($recent_requests)): ?>
                        <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center shadow-sm">
                            <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center text-2xl mx-auto mb-3">
                                <i class="fas fa-bell-slash"></i>
                            </div>
                            <h4 class="text-sm font-bold text-slate-800">No New Work Requests</h4>
                            <p class="text-xs text-slate-400 mt-1">Keep your profile updated with complete skills and location so users can find you.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_requests as $r): ?>
                            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="space-y-1.5 flex-1">
                                    <div class="flex items-center space-x-2">
                                        <h4 class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($r['title']); ?></h4>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                            <?php 
                                            if ($r['status'] === 'accepted') echo 'bg-emerald-50 text-emerald-700';
                                            elseif ($r['status'] === 'completed') echo 'bg-blue-50 text-blue-700';
                                            elseif ($r['status'] === 'rejected') echo 'bg-red-50 text-red-700';
                                            else echo 'bg-amber-50 text-amber-700';
                                            ?>">
                                            <?php echo htmlspecialchars($r['status']); ?>
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 line-clamp-1"><?php echo htmlspecialchars($r['description']); ?></p>
                                    <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-400 pt-1">
                                        <span><i class="fas fa-user mr-1 text-slate-400"></i>Client: <strong><?php echo htmlspecialchars($r['client_name']); ?></strong></span>
                                        <span><i class="fas fa-location-dot mr-1 text-slate-400"></i><?php echo htmlspecialchars($r['location']); ?></span>
                                        <?php if(!empty($r['budget'])): ?>
                                            <span><i class="fas fa-indian-rupee-sign mr-1 text-slate-400"></i>Budget: <strong>₹ <?php echo htmlspecialchars($r['budget']); ?></strong></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center space-x-2 shrink-0">
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <button onclick="handleRequest(<?php echo $r['id']; ?>, 'accept', this)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm">
                                            Accept Request
                                        </button>
                                        <button onclick="handleRequest(<?php echo $r['id']; ?>, 'reject', this)" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-3.5 py-2 rounded-xl text-xs font-bold transition-all">
                                            Decline
                                        </button>
                                    <?php elseif ($r['status'] === 'accepted'): ?>
                                        <a href="chat.php?user_id=<?php echo $r['user_id']; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center space-x-1">
                                            <i class="fas fa-comments"></i>
                                            <span>Chat Client</span>
                                        </a>
                                        <button onclick="handleRequest(<?php echo $r['id']; ?>, 'complete', this)" class="bg-blue-600 hover:bg-blue-700 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition-all">
                                            Mark Done
                                        </button>
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

<script>
const csrfToken = <?php echo json_encode($csrf_token); ?>;

async function handleRequest(requestId, action, btn) {
    if (!confirm(`Are you sure you want to mark this request as ${action}?`)) return;
    const oldHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    try {
        const res = await fetch('../includes/handle_work_request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ request_id: requestId, action: action })
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.error || 'Failed to update request');
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }
    } catch (e) {
        alert('Network error');
        btn.disabled = false;
        btn.innerHTML = oldHtml;
    }
}
</script>
</body>
</html>
