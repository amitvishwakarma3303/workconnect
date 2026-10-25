<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
checkRole(['employer']);

$success_msg = $_GET['success'] ?? '';
$error_msg = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard - Work Connect</title>
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
    <?php include '../includes/employer_sidebar.php'; ?>


    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-y-auto">

        <!-- Header -->
        <header class="bg-white p-4 flex justify-between items-center shadow-sm">

            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="md:hidden text-xl">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 class="text-lg md:text-xl font-semibold">
                    Employer Dashboard
                </h2>
            </div>

            <!-- Remove Modal Button -->
            <a href="post_job.php"
               class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
               + Post New Job
            </a>
        </header>


        <!-- Content -->
        <div class="p-4 md:p-8 space-y-6">
            <?php if (!empty($success_msg)): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                    <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_msg)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                    <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                // Fetch stats
                $active_postings = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ?");
                $active_postings->execute([$_SESSION['user_id']]);
                $jobs_count = $active_postings->fetchColumn();

                // Fetch total hires (accepted applications)
                $stmt_hires = $pdo->prepare("SELECT COUNT(*) FROM job_applications ja JOIN jobs j ON ja.job_id = j.id WHERE j.employer_id = ? AND ja.status = 'accepted'");
                $stmt_hires->execute([$_SESSION['user_id']]);
                $total_hires = $stmt_hires->fetchColumn();

                // Fetch unread applications (pending)
                $stmt_apps = $pdo->prepare("SELECT COUNT(*) FROM job_applications ja JOIN jobs j ON ja.job_id = j.id WHERE j.employer_id = ? AND ja.status = 'pending'");
                $stmt_apps->execute([$_SESSION['user_id']]);
                $unread_apps = $stmt_apps->fetchColumn();
                ?>

                <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 p-6 rounded-2xl text-white shadow-lg">
                    <p class="text-sm uppercase tracking-wider">Active Postings</p>
                    <p class="text-3xl md:text-4xl font-bold mt-2"><?php echo $jobs_count; ?></p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border">
                    <p class="text-sm text-gray-500 uppercase">Total Hires</p>
                    <p class="text-3xl md:text-4xl font-bold mt-2"><?php echo $total_hires; ?></p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border">
                    <p class="text-sm text-gray-500 uppercase">Unread Applications</p>
                    <p class="text-3xl md:text-4xl font-bold mt-2"><?php echo $unread_apps; ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Jobs -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 font-primary">Recent Job Postings</h3>
                    <div class="space-y-4">
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM jobs WHERE employer_id = ? ORDER BY created_at DESC LIMIT 3");
                        $stmt->execute([$_SESSION['user_id']]);
                        while ($job = $stmt->fetch()): ?>
                            <div class="flex items-center justify-between p-4 border border-gray-50 rounded-xl hover:shadow-md transition-shadow bg-gray-50/10">
                                <div class="flex items-center space-x-4">
                                    <div class="bg-indigo-100 p-3 rounded-lg text-indigo-600 font-bold"><i class="fas fa-briefcase"></i></div>
                                    <div>
                                        <p class="font-bold text-gray-800"><?php echo htmlspecialchars($job['title']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($job['location']); ?> • <?php echo date('M d', strtotime($job['created_at'])); ?></p>
                                    </div>
                                </div>
                                <a href="my_listings.php" class="text-indigo-600 text-sm font-semibold hover:underline">View</a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Recent Employees -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-6">Top Rated Employees</h3>
                    <div class="space-y-4">
                        <?php
                        $stmt = $pdo->query("SELECT users.id, users.username, employee_profiles.skills FROM users LEFT JOIN employee_profiles ON users.id = employee_profiles.user_id JOIN roles ON users.role_id = roles.id WHERE roles.role_name = 'employee' LIMIT 3");
                        while ($employee = $stmt->fetch()): ?>
                            <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold">
                                        <?php echo strtoupper(substr($employee['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($employee['username']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($employee['skills'] ?? 'General Laborer'); ?></p>
                                    </div>
                                </div>
                                <a href="chat.php?employee_id=<?php echo $employee['id']; ?>" class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all">
                                    <i class="fas fa-comment-dots"></i>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <a href="workforce.php" class="block w-full mt-6 py-3 border border-indigo-100 rounded-xl text-indigo-600 font-semibold text-center hover:bg-indigo-50 transition-colors">Browse Workforce</a>
                </div>
            </div>

        </div>

    </div>
</div>


</body>
</html>
