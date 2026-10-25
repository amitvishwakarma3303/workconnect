<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['admin']);

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        header("Location: requests.php?error=Invalid token");
        exit();
    }
    $delId = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM work_requests WHERE id = ?");
    $stmt->execute([$delId]);
    header("Location: requests.php?msg=Work request removed");
    exit();
}

$status = trim($_GET['status'] ?? '');
$query = "
    SELECT wr.*, u1.username as client_name, u1.email as client_email,
           u2.username as worker_name, c.name as category_name
    FROM work_requests wr
    JOIN users u1 ON wr.user_id = u1.id
    JOIN users u2 ON wr.worker_id = u2.id
    LEFT JOIN categories c ON wr.category_id = c.id
    WHERE 1=1
";
$params = [];

if (!empty($status)) {
    $query .= " AND wr.status = ?";
    $params[] = $status;
}

$query .= " ORDER BY wr.created_at DESC";
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
    <title>Work Requests Moderation - Admin</title>
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
        <header class="bg-white border-b border-slate-200/80 p-4 md:px-8 flex justify-between items-center sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-700 text-xl focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h2 class="text-lg md:text-xl font-bold text-slate-800">Work Requests & Hiring Records</h2>
                    <p class="text-xs text-slate-400">Moderate hiring requests and task exchanges</p>
                </div>
            </div>
            <span class="text-xs font-semibold text-slate-500">
                Total: <strong class="text-indigo-600"><?php echo count($requests); ?> records</strong>
            </span>
        </header>

        <div class="p-4 md:p-8 space-y-6 max-w-7xl mx-auto w-full">
            <?php if (isset($_GET['msg'])): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold flex items-center">
                    <i class="fas fa-circle-check text-base mr-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                                <th class="p-4">Task Title</th>
                                <th class="p-4">Client</th>
                                <th class="p-4">Worker</th>
                                <th class="p-4">Location</th>
                                <th class="p-4">Budget</th>
                                <th class="p-4">Status</th>
                                <th class="p-4">Date</th>
                                <th class="p-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($requests)): ?>
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-slate-400">No work requests found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($requests as $r): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="p-4 font-bold text-slate-800">
                                            <?php echo htmlspecialchars($r['title']); ?>
                                        </td>
                                        <td class="p-4 text-slate-700">
                                            <?php echo htmlspecialchars($r['client_name']); ?>
                                        </td>
                                        <td class="p-4 font-semibold text-indigo-600">
                                            <?php echo htmlspecialchars($r['worker_name']); ?>
                                        </td>
                                        <td class="p-4 text-slate-500">
                                            <?php echo htmlspecialchars($r['location']); ?>
                                        </td>
                                        <td class="p-4 font-bold text-slate-800">
                                            ₹ <?php echo htmlspecialchars($r['budget'] ?: 'N/A'); ?>
                                        </td>
                                        <td class="p-4">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                                <?php 
                                                if ($r['status'] === 'accepted') echo 'bg-emerald-50 text-emerald-700';
                                                elseif ($r['status'] === 'completed') echo 'bg-blue-50 text-blue-700';
                                                elseif ($r['status'] === 'rejected') echo 'bg-red-50 text-red-700';
                                                else echo 'bg-amber-50 text-amber-700';
                                                ?>">
                                                <?php echo htmlspecialchars($r['status']); ?>
                                            </span>
                                        </td>
                                        <td class="p-4 text-slate-400 text-[11px]">
                                            <?php echo date('M d, Y', strtotime($r['created_at'])); ?>
                                        </td>
                                        <td class="p-4 text-right">
                                            <form method="POST" class="inline" onsubmit="return confirm('Delete this work request record?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $r['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                                <button type="submit" class="text-red-400 hover:text-red-600 p-2 transition-colors">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>
