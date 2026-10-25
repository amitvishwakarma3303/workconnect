<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['admin']);

// Handle Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        header("Location: workers.php?error=Invalid request token");
        exit();
    }
    $delId = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$delId]);
    header("Location: workers.php?msg=Worker deleted successfully");
    exit();
}

$search = trim($_GET['q'] ?? '');
$query = "
    SELECT u.id, u.username, u.email, u.created_at,
           wp.full_name, wp.category_id, wp.skills, wp.experience_years, wp.location, wp.availability, wp.phone, wp.daily_rate,
           c.name as category_name
    FROM users u
    JOIN roles r ON u.role_id = r.id
    LEFT JOIN worker_profiles wp ON u.id = wp.user_id
    LEFT JOIN categories c ON wp.category_id = c.id
    WHERE r.role_name = 'worker'
";
$params = [];

if (!empty($search)) {
    $query .= " AND (u.username LIKE ? OR u.email LIKE ? OR wp.full_name LIKE ? OR wp.skills LIKE ? OR wp.location LIKE ? OR c.name LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like, $like, $like, $like];
}

$query .= " ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$workers = $stmt->fetchAll();

$csrf_token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Workers - Admin</title>
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
        <header class="bg-white border-b border-slate-200/80 p-4 md:px-8 flex justify-between items-center sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-700 text-xl focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h2 class="text-lg md:text-xl font-bold text-slate-800">Manage Workers</h2>
                    <p class="text-xs text-slate-400">View and moderate registered skilled labourers</p>
                </div>
            </div>

            <form action="" method="GET" class="relative max-w-xs w-full hidden sm:block">
                <i class="fas fa-search absolute left-3 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search worker, trade, city..." 
                       class="w-full pl-9 pr-4 py-2 bg-slate-100 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </form>
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
                                <th class="p-4">Worker Profile</th>
                                <th class="p-4">Trade Category</th>
                                <th class="p-4">Location</th>
                                <th class="p-4">Experience</th>
                                <th class="p-4">Status</th>
                                <th class="p-4">Rate</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($workers)): ?>
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-400">No workers found matching your search.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($workers as $w): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="p-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center text-xs">
                                                    <?php echo strtoupper(substr($w['username'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-slate-800"><?php echo htmlspecialchars($w['full_name'] ?: $w['username']); ?></p>
                                                    <p class="text-[10px] text-slate-400"><?php echo htmlspecialchars($w['email']); ?> &bull; <?php echo htmlspecialchars($w['phone'] ?? 'No phone'); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4 font-semibold text-indigo-600">
                                            <?php echo htmlspecialchars($w['category_name'] ?: 'Not Assigned'); ?>
                                        </td>
                                        <td class="p-4 text-slate-600">
                                            <?php echo htmlspecialchars($w['location'] ?: 'Not specified'); ?>
                                        </td>
                                        <td class="p-4 text-slate-600">
                                            <?php echo $w['experience_years']; ?> Years
                                        </td>
                                        <td class="p-4">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                                <?php echo ($w['availability'] === 'available') ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'; ?>">
                                                <?php echo htmlspecialchars($w['availability'] ?: 'available'); ?>
                                            </span>
                                        </td>
                                        <td class="p-4 font-bold text-slate-700">
                                            ₹ <?php echo htmlspecialchars($w['daily_rate'] ?: 'Negotiable'); ?>
                                        </td>
                                        <td class="p-4 text-right">
                                            <form method="POST" class="inline" onsubmit="return confirm('Delete this worker? All their data will be removed.');">
                                                <input type="hidden" name="delete_id" value="<?php echo $w['id']; ?>">
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
