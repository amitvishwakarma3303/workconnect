<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
checkRole(['employee']);

$stmt = $pdo->prepare("
    SELECT ja.*, j.title, j.location, j.salary, j.employer_id, u.username AS employer_name
    FROM job_applications ja
    JOIN jobs j ON ja.job_id = j.id
    JOIN users u ON j.employer_id = u.id
    WHERE ja.employee_id = ?
    ORDER BY ja.applied_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$applications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - Work Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 font-sans">
<div class="flex h-screen overflow-hidden">
    <?php include '../includes/employee_sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-y-auto">
        <header class="bg-white p-4 flex justify-between items-center shadow-sm sticky top-0 z-10">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="md:hidden text-xl text-indigo-900">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 class="text-xl font-semibold">My Applications</h2>
            </div>
        </header>

        <div class="p-4 md:p-8">
            <?php if (empty($applications)): ?>
                <div class="bg-white p-12 rounded-3xl text-center border border-gray-100 shadow-sm">
                    <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-4 text-indigo-600 text-3xl">
                        <i class="fas fa-file-circle-check"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">No applications yet</h3>
                    <p class="text-gray-500 mt-2">Start applying from the Find Jobs page.</p>
                    <a href="jobs.php" class="inline-block mt-6 px-6 py-3 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
                        Browse Jobs
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <?php foreach ($applications as $app): ?>
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($app['title']); ?></h3>
                                    <p class="text-xs text-indigo-600 font-semibold uppercase mt-1">
                                        <?php echo htmlspecialchars($app['employer_name']); ?>
                                    </p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider <?php echo $app['status'] === 'accepted' ? 'bg-green-100 text-green-600' : ($app['status'] === 'rejected' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700'); ?>">
                                    <?php echo htmlspecialchars($app['status']); ?>
                                </span>
                            </div>

                            <div class="mt-4 text-sm text-gray-600 space-y-1">
                                <p><i class="fas fa-location-dot mr-2 text-gray-400"></i><?php echo htmlspecialchars($app['location'] ?: 'Location not specified'); ?></p>
                                <p><i class="fas fa-indian-rupee-sign mr-2 text-gray-400"></i><?php echo htmlspecialchars($app['salary'] ?: 'Negotiable'); ?></p>
                                <p><i class="fas fa-calendar mr-2 text-gray-400"></i>Applied on <?php echo date('M d, Y', strtotime($app['applied_at'])); ?></p>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <?php if ($app['status'] === 'accepted'): ?>
                                    <a href="chat.php?employer_id=<?php echo (int)$app['employer_id']; ?>" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
                                        <i class="fas fa-comment-dots mr-1"></i> Chat Employer
                                    </a>
                                <?php else: ?>
                                    <a href="jobs.php" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200">
                                        Find More Jobs
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
