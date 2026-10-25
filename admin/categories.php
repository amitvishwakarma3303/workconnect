<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['admin']);

$msg = '';
$error = '';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request token.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fa-tools');
        $description = trim($_POST['description'] ?? '');

        if (!empty($name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name, icon, description) VALUES (?, ?, ?)");
                $stmt->execute([$name, $icon, $description]);
                $msg = "Category '{$name}' created successfully!";
            } catch (PDOException $e) {
                $error = "Failed to add category. Category name may already exist.";
            }
        } else {
            $error = "Category name is required.";
        }
    }
}

// Handle Delete Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request token.";
    } else {
        $delId = (int)$_POST['delete_id'];
        $delStmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $delStmt->execute([$delId]);
        $msg = "Category deleted successfully.";
    }
}

// Fetch categories with worker count
$stmt = $pdo->query("
    SELECT c.*, COUNT(wp.user_id) as worker_count 
    FROM categories c 
    LEFT JOIN worker_profiles wp ON c.id = wp.category_id 
    GROUP BY c.id 
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll();

$csrf_token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Categories - Admin</title>
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
                    <h2 class="text-lg md:text-xl font-bold text-slate-800">Manage Work Categories</h2>
                    <p class="text-xs text-slate-400">Add and organize trade categories (Plumbers, Electricians, Carpenters, etc.)</p>
                </div>
            </div>
        </header>

        <div class="p-4 md:p-8 space-y-6 max-w-6xl mx-auto w-full">
            <?php if ($msg): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold flex items-center">
                    <i class="fas fa-circle-check text-base mr-2"></i> <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="p-4 rounded-2xl bg-red-50 text-red-700 border border-red-200 text-xs font-semibold flex items-center">
                    <i class="fas fa-circle-exclamation text-base mr-2"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Add Category Card -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Add New Category</h3>
                <form action="" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="add_category" value="1">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Category Name *</label>
                        <input type="text" name="name" placeholder="e.g. Welder, Mason, Roofer" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">FontAwesome Icon Class</label>
                        <input type="text" name="icon" value="fa-tools" placeholder="fa-hammer, fa-bolt..." required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Description</label>
                        <input type="text" name="description" placeholder="Short description of services..." 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>

                    <div class="sm:col-span-3 flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-600/20">
                            + Add Category
                        </button>
                    </div>
                </form>
            </div>

            <!-- Categories List Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php foreach ($categories as $cat): ?>
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition-all flex items-start justify-between">
                        <div class="flex items-start space-x-3">
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center text-lg shrink-0">
                                <i class="fas <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($cat['name']); ?></h4>
                                <p class="text-[10px] text-slate-400 mt-0.5 line-clamp-2"><?php echo htmlspecialchars($cat['description'] ?: 'Trade category'); ?></p>
                                <span class="inline-block mt-2 px-2 py-0.5 bg-slate-100 text-slate-600 rounded-md text-[10px] font-bold">
                                    <?php echo $cat['worker_count']; ?> registered workers
                                </span>
                            </div>
                        </div>

                        <form method="POST" onsubmit="return confirm('Delete category <?php echo htmlspecialchars($cat['name']); ?>?');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="delete_id" value="<?php echo $cat['id']; ?>">
                            <button type="submit" class="text-slate-300 hover:text-red-500 p-1.5 transition-colors">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </main>
</div>

</body>
</html>
