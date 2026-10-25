<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
checkRole(['employer']);

$stmt = $pdo->query("
    SELECT users.id, users.username, users.email, employee_profiles.skills, employee_profiles.phone
    FROM users
    LEFT JOIN employee_profiles ON users.id = employee_profiles.user_id
    JOIN roles ON users.role_id = roles.id
    WHERE roles.role_name = 'employee'
");
$employees = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workforce - Work Connect</title>
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
                <h2 class="text-xl font-semibold text-gray-800">Available Workforce</h2>
            </div>
        </header>

        <div class="p-4 md:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($employees as $emp): ?>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 text-2xl font-bold">
                                <?php echo strtoupper(substr($emp['username'], 0, 1)); ?>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($emp['username']); ?></h3>
                                <p class="text-sm text-indigo-600 font-medium"><?php echo htmlspecialchars($emp['skills'] ?? 'General Laborer'); ?></p>
                            </div>
                        </div>
                        <div class="space-y-2 mb-6">
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-envelope w-5"></i>
                                <span><?php echo htmlspecialchars($emp['email']); ?></span>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-phone w-5"></i>
                                <span><?php echo htmlspecialchars($emp['phone'] ?? 'Not provided'); ?></span>
                            </div>
                        </div>
                        <a href="chat.php?employee_id=<?php echo $emp['id']; ?>" class="block w-full py-3 bg-indigo-600 text-white rounded-xl text-center font-semibold hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-comment-dots mr-2"></i> Start Chat
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>
</html>