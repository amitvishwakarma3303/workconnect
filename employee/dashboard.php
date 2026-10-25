<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
checkRole(['employee']);

// Get latest jobs
$stmt = $pdo->query("SELECT jobs.*, users.username as employer_name FROM jobs JOIN users ON jobs.employer_id = users.id ORDER BY created_at DESC LIMIT 6");
$jobs = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - Work Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 font-sans">

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <?php include '../includes/employee_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-y-auto">

        <!-- Header -->
        <header class="bg-white p-4 flex justify-between items-center shadow-sm sticky top-0 z-10">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="md:hidden text-xl text-indigo-900">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 class="text-lg md:text-xl font-semibold">Opportunities</h2>
            </div>
            <div class="flex items-center space-x-4">
                <div class="hidden md:block text-right">
                    <p class="text-sm font-semibold text-gray-800"><?php echo $_SESSION['username']; ?></p>
                    <p class="text-xs text-green-500 font-bold uppercase">Online</p>
                </div>
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="p-4 md:p-8 space-y-8">
            
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-3xl p-6 md:p-10 text-white shadow-xl">
                <div class="max-w-xl">
                    <h1 class="text-3xl md:text-4xl font-bold">Welcome back, <?php echo explode(' ', $_SESSION['username'])[0]; ?>!</h1>
                </div>
            </div>

            <!-- Jobs Section -->
            <div>
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-800">Latest Jobs</h3>
                    <a href="jobs.php" class="text-indigo-600 font-semibold hover:underline">View All</a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if (empty($jobs)): ?>
                        <div class="col-span-full py-12 text-center text-gray-500 italic bg-white rounded-2xl border">
                            No jobs available at the moment.
                        </div>
                    <?php else: ?>
                        <?php foreach ($jobs as $job): ?>
                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-all border-l-4 border-indigo-500 flex flex-col h-full">
                                <div class="flex justify-between items-start mb-4">
                                    <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                                        <?php echo htmlspecialchars($job['employer_name']); ?>
                                    </span>
                                    <span class="text-lg font-bold text-indigo-700 italic underline underline-offset-4">
                                        ₹ <?php echo htmlspecialchars($job['salary'] ?: 'Negotiable'); ?>
                                    </span>
                                </div>
                                <h4 class="text-lg font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($job['title']); ?></h4>
                                <p class="text-sm text-gray-500 mb-4 line-clamp-2"><?php echo htmlspecialchars($job['description']); ?></p>
                                <div class="mt-auto pt-4 border-t border-gray-50">
                                    <div class="flex items-center text-xs text-gray-400 space-x-4 mb-4">
                                        <span><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                                        <span><i class="fas fa-calendar-alt mr-1"></i> <?php echo date('M d', strtotime($job['created_at'])); ?></span>
                                    </div>
                                    <a href="jobs.php" class="block text-center w-full bg-indigo-600 text-white py-3 rounded-xl font-bold hover:bg-indigo-700 transition-all">Apply Now</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>

</body>
</html>
