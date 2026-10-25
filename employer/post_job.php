<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['employer']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        header("Location: post_job.php?error=Invalid request token.");
        exit();
    }

    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $location = $_POST['location'] ?? '';
    $salary = $_POST['salary'] ?? '';
    $employer_id = $_SESSION['user_id'];
    $no_of_vacancy = $_POST['no_of_vacancy'] ?? '';

    if (!empty($title) && !empty($description)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO jobs (employer_id, title, description, location, salary, no_of_vacancy) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$employer_id, $title, $description, $location, $salary, $no_of_vacancy]);

            header("Location: dashboard.php?success=Job posted successfully!");
            exit();
        } catch (PDOException $e) {
            header("Location: dashboard.php?error=Failed to post job.");
            exit();
        }
    } else {
        header("Location: post_job.php?error=Title and Description are required.");
        exit();
    }
}

$error_msg = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Job - Work Connect</title>
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
        <header class="bg-white border-b border-gray-200 p-4 flex items-center space-x-4 sticky top-0 z-20">
            <button onclick="toggleSidebar()" class="md:hidden text-indigo-900">
                <i class="fas fa-bars text-2xl"></i>
            </button>
            <h2 class="text-xl font-semibold text-gray-800">Post a New Job</h2>
        </header>

        <div class="p-4 md:p-8">
            <div class="max-w-3xl mx-auto bg-white p-6 md:p-10 rounded-3xl shadow-sm border border-gray-100">
                <?php if ($error_msg): ?>
                    <div class="bg-red-50 text-red-600 p-4 rounded-xl border border-red-100 mb-6 flex items-center">
                        <i class="fas fa-exclamation-circle mr-3"></i>
                        <span class="text-sm font-medium"><?php echo htmlspecialchars($error_msg); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Job Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required placeholder="e.g. Senior Carpenter, Site Supervisor"
                               class="w-full border-gray-200 border p-4 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Detailed Description <span class="text-red-500">*</span></label>
                        <textarea name="description" required rows="6" placeholder="Describe the job roles, requirements, and timing..."
                            class="w-full border-gray-200 border p-4 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Location</label>
                            <div class="relative">
                                <i class="fas fa-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="location" placeholder="City, Area"
                                       class="w-full border-gray-200 border p-4 pl-12 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Budget / Salary Range</label>
                            <div class="relative">
                                <i class="fas fa-indian-rupee-sign absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="salary" placeholder="e.g. ₹500 - ₹800 / day"
                                       class="w-full border-gray-200 border p-4 pl-12 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                        </div>
                        <!-- no of vacancy -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">No. of Vacancy</label>
                            <div class="relative">
                                <i class="fas fa-users absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="number" name="no_of_vacancy" placeholder="e.g. 5"
                                       class="w-full border-gray-200 border p-4 pl-12 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-4">
                        <a href="dashboard.php" class="px-8 py-4 bg-gray-100 text-gray-600 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="px-8 py-4 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all">
                            Publish Vacancy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
