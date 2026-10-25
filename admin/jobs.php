<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['admin']);

// Handle Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        header("Location: jobs.php?error=Invalid request token");
        exit;
    }

    $delete_id = (int) $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("Location: jobs.php?success=Job listing removed");
    exit;
}

$stmt = $pdo->query("SELECT jobs.*, users.username as employer_name FROM jobs JOIN users ON jobs.employer_id = users.id ORDER BY created_at DESC");
$jobs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderate Jobs - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 font-sans">

<div class="flex h-screen overflow-hidden">
    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-y-auto">
        <header class="bg-white border-b border-gray-100 p-6 flex justify-between items-center sticky top-0 z-10">
            <h2 class="text-xl font-bold text-gray-800">Job Moderation</h2>
        </header>

        <div class="p-6 md:p-10">
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-indigo-50 text-indigo-600 p-4 rounded-2xl mb-6 text-sm font-bold">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-2xl mb-6 text-sm font-bold">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($jobs as $job): ?>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-50 hover:border-indigo-100 transition-all flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                            By <?php echo htmlspecialchars($job['employer_name']); ?>
                        </span>
                        <span class="text-xs text-gray-400"><?php echo date('M d', strtotime($job['created_at'])); ?></span>
                    </div>
                    <h4 class="font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($job['title']); ?></h4>
                    <p class="text-xs text-gray-500 mb-6 flex-1 line-clamp-3"><?php echo htmlspecialchars($job['description']); ?></p>
                    
                    <div class="pt-6 border-t border-gray-50 flex items-center justify-between">
                        <span class="text-indigo-600 font-bold text-sm">₹ <?php echo htmlspecialchars($job['salary'] ?: 'N/A'); ?></span>
                        <div class="flex space-x-2">
                             <form method="POST" onsubmit="return confirm('Delete this job listing? This action cannot be undone.');">
                                 <input type="hidden" name="delete_id" value="<?php echo (int)$job['id']; ?>">
                                 <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                                 <button type="submit" class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all">
                                     <i class="fas fa-trash-alt text-xs"></i>
                                 </button>
                             </form>
                             <button class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all">
                                 <i class="fas fa-eye text-xs"></i>
                             </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>
