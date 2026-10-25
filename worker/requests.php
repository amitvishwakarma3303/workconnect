<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['worker']);

$worker_id = $_SESSION['user_id'];
$status_filter = trim($_GET['status'] ?? '');

$query = "
    SELECT wr.*, u.username as client_name, u.email as client_email,
           c.name as category_name, c.icon as category_icon
    FROM work_requests wr
    JOIN users u ON wr.user_id = u.id
    LEFT JOIN categories c ON wr.category_id = c.id
    WHERE wr.worker_id = ?
";
$params = [$worker_id];

if (!empty($status_filter)) {
    $query .= " AND wr.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY (wr.status = 'pending') DESC, wr.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll();

$csrf_token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Requests - Worker Portal</title>
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
                    <h2 class="text-lg md:text-xl font-bold text-slate-800">Work Requests & Inquiries</h2>
                    <p class="text-xs text-slate-400">Manage client service orders and task requests</p>
                </div>
            </div>
            <span class="text-xs font-semibold text-slate-500">
                Total: <strong class="text-emerald-600"><?php echo count($requests); ?> requests</strong>
            </span>
        </header>

        <!-- Body Content -->
        <div class="p-4 md:p-8 space-y-6 max-w-6xl mx-auto w-full">
            
            <!-- Filters -->
            <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="requests.php" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?php echo empty($status_filter) ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
                        All (<?php echo count($requests); ?>)
                    </a>
                    <a href="requests.php?status=pending" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?php echo ($status_filter === 'pending') ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
                        Pending Action
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

            <!-- List -->
            <div class="space-y-4">
                <?php if (empty($requests)): ?>
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center max-w-md mx-auto shadow-sm">
                        <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-3">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">No Requests Found</h3>
                        <p class="text-xs text-slate-400 mt-1">There are no work requests matching this status.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($requests as $r): ?>
                        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:shadow-md transition-all space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <h3 class="font-bold text-slate-800 text-base"><?php echo htmlspecialchars($r['title']); ?></h3>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                            <?php 
                                            if ($r['status'] === 'accepted') echo 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                            elseif ($r['status'] === 'completed') echo 'bg-blue-50 text-blue-700 border border-blue-200';
                                            elseif ($r['status'] === 'rejected') echo 'bg-red-50 text-red-700 border border-red-200';
                                            else echo 'bg-amber-50 text-amber-700 border border-amber-200';
                                            ?>">
                                            <?php echo htmlspecialchars($r['status']); ?>
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        From: <strong class="text-slate-700"><?php echo htmlspecialchars($r['client_name']); ?></strong> (<?php echo htmlspecialchars($r['client_email']); ?>) &bull; 
                                        <?php echo date('M d, Y h:i A', strtotime($r['created_at'])); ?>
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="text-[10px] text-slate-400 font-semibold uppercase">Offered Budget</p>
                                    <p class="text-base font-extrabold text-slate-800">
                                        ₹ <?php echo htmlspecialchars($r['budget'] ?: 'Negotiable'); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Description -->
                            <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-wrap"><?php echo htmlspecialchars($r['description']); ?></p>

                            <!-- Location & Date -->
                            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 py-2 border-t border-b border-slate-50">
                                <span><i class="fas fa-location-dot text-indigo-500 mr-1.5"></i>Location: <strong><?php echo htmlspecialchars($r['location']); ?></strong></span>
                                <?php if(!empty($r['work_date'])): ?>
                                    <span><i class="fas fa-calendar-alt text-indigo-500 mr-1.5"></i>Scheduled Date: <strong><?php echo date('M d, Y', strtotime($r['work_date'])); ?></strong></span>
                                <?php endif; ?>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                                <div class="text-xs text-slate-400">
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <span class="text-amber-600 font-semibold"><i class="fas fa-clock mr-1"></i> Client is waiting for your response.</span>
                                    <?php elseif ($r['status'] === 'accepted'): ?>
                                        <span class="text-emerald-600 font-semibold"><i class="fas fa-check-circle mr-1"></i> You accepted this request. Contact the client to coordinate.</span>
                                    <?php elseif ($r['status'] === 'completed'): ?>
                                        <span class="text-blue-600 font-semibold"><i class="fas fa-circle-check mr-1"></i> Completed task.</span>
                                    <?php endif; ?>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <button onclick="handleRequest(<?php echo $r['id']; ?>, 'accept', this)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center space-x-1.5">
                                            <i class="fas fa-check"></i>
                                            <span>Accept Request</span>
                                        </button>
                                        <button onclick="handleRequest(<?php echo $r['id']; ?>, 'reject', this)" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-xl text-xs font-bold transition-all">
                                            Decline
                                        </button>
                                    <?php elseif ($r['status'] === 'accepted'): ?>
                                        <a href="chat.php?user_id=<?php echo $r['user_id']; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center space-x-1.5">
                                            <i class="fas fa-comments"></i>
                                            <span>Chat with Client</span>
                                        </a>
                                        <button onclick="handleRequest(<?php echo $r['id']; ?>, 'complete', this)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center space-x-1.5">
                                            <i class="fas fa-check-double"></i>
                                            <span>Mark as Completed</span>
                                        </button>
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

<script>
const csrfToken = <?php echo json_encode($csrf_token); ?>;

async function handleRequest(requestId, action, btn) {
    if (!confirm(`Are you sure you want to ${action} this work request?`)) return;
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
