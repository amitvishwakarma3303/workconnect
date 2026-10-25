<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['employer']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        header("Location: my_listings.php");
        exit();
    }

    $deleteId = (int) $_POST['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ? AND employer_id = ?");
    $stmt->execute([$deleteId, $_SESSION['user_id']]);

    header("Location: my_listings.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM jobs WHERE employer_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$jobs = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Job Listings - Work Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    <?php include '../includes/employer_sidebar.php'; ?>

    <main class="flex-grow flex flex-col overflow-y-auto">
        <header class="bg-white border-b border-gray-200 p-4 flex justify-between items-center sticky top-0 z-20">
            <div class="flex items-center space-x-4">
                <button onclick="toggleSidebar()" class="md:hidden text-indigo-900">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                <h2 class="text-xl font-semibold text-gray-800">My Job Listings</h2>
            </div>
            <a href="post_job.php" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">+ Post New</a>
        </header>

        <div class="p-4 md:p-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="p-4 border-b">Job Title</th>
                                <th class="p-4 border-b">Location</th>
                                <th class="p-4 border-b">Salary</th>
                                <th class="p-4 border-b">Posted Date</th>
                                <th class="p-4 border-b text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($jobs)): ?>
                                <tr>
                                    <td colspan="5" class="p-12 text-center text-gray-500 italic">No jobs posted yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($jobs as $job): ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="p-4">
                                            <div class="font-bold text-gray-800"><?php echo htmlspecialchars($job['title']); ?></div>
                                            <div class="text-xs text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars(substr($job['description'], 0, 80)) . '...'; ?></div>
                                        </td>
                                        <td class="p-4 text-sm text-gray-600"><?php echo htmlspecialchars($job['location']); ?></td>
                                        <td class="p-4 text-sm font-medium text-gray-700"><?php echo htmlspecialchars($job['salary'] ?: 'N/A'); ?></td>
                                        <td class="p-4 text-sm text-gray-500"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                                        <td class="p-4 text-right">
                                            <button class="text-indigo-600 hover:text-indigo-900 font-semibold text-sm mr-4">Edit</button>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="delete_id" value="<?php echo $job['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-semibold text-sm"
                                                        onclick="return confirm('Are you sure you want to delete this job?');">
                                                    Delete
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
</body>
</html>
