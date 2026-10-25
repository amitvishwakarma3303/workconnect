<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['employee']);

$searchTerm = $_GET['q'] ?? '';
$user_id = $_SESSION['user_id'];
$query = "SELECT jobs.*, users.username as employer_name, 
          (SELECT id FROM job_applications WHERE job_id = jobs.id AND employee_id = ?) as applied_id 
          FROM jobs 
          JOIN users ON jobs.employer_id = users.id";
$params = [$user_id];

if (!empty($searchTerm)) {
    $query .= " WHERE (title LIKE ? OR description LIKE ? OR location LIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
}

$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Jobs - Work Connect</title>
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
                <h2 class="text-xl font-semibold">Find Jobs</h2>
            </div>
            <form action="" method="GET" class="flex-1 max-w-md mx-4 hidden md:block">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Search titles, skills, location..." class="w-full pl-10 pr-4 py-2 bg-gray-100 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </form>
            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
        </header>

        <!-- Content -->
        <div class="p-4 md:p-8">
            
            <!-- Mobile Search -->
            <div class="md:hidden mb-6">
                <form action="" method="GET">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        <input type="text" name="q" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Search jobs..." class="w-full pl-10 pr-4 py-2 bg-white border rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </form>
            </div>

            <?php if (!empty($searchTerm)): ?>
                <p class="mb-6 text-gray-600">Showing results for "<span class="font-semibold"><?php echo htmlspecialchars($searchTerm); ?></span>"</p>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($jobs)): ?>
                    <div class="col-span-full py-20 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
                        <div class="bg-indigo-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 text-indigo-600 text-3xl">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">No jobs found</h3>
                        <p class="text-gray-500 mt-2">Try adjusting your search terms or filters.</p>
                        <a href="jobs.php" class="mt-6 inline-block text-indigo-600 font-bold hover:underline">Clear all search</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-all border-l-4 border-indigo-500 flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($job['title']); ?></h4>
                                    <p class="text-xs text-indigo-600 font-semibold uppercase tracking-wider mt-1"><?php echo htmlspecialchars($job['employer_name']); ?></p>
                                </div>
                                <span class="text-lg font-bold text-indigo-700 italic">
                                    ₹ <?php echo htmlspecialchars($job['salary'] ?: 'N/A'); ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mb-6"><?php echo htmlspecialchars($job['description']); ?></p>
                            <div class="mt-auto">
                                <div class="flex items-center text-xs text-gray-400 space-x-4 mb-4">
                                    <span><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                                    <span><i class="fas fa-clock mr-1"></i> <?php echo date('M d', strtotime($job['created_at'])); ?></span>
                                    <span><i class="fas fa-users mr-1"></i> <?php echo htmlspecialchars($job['no_of_vacancy']); ?></span>
                                </div>
                                <div class="flex space-x-2">
                                    <?php if ($job['applied_id']): ?>
                                        <button disabled class="flex-1 bg-green-100 text-green-600 py-3 rounded-xl font-bold flex items-center justify-center cursor-not-allowed">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            <span>Applied</span>
                                        </button>
                                        <a href="chat.php?employer_id=<?php echo $job['employer_id']; ?>" class="w-12 bg-indigo-50 text-indigo-600 py-3 rounded-xl font-bold flex items-center justify-center hover:bg-indigo-100 transition-all">
                                            <i class="fas fa-comment-dots"></i>
                                        </a>
                                    <?php else: ?>
                                        <button onclick="applyJob(<?php echo $job['id']; ?>, this)" class="flex-1 bg-indigo-600 text-white py-3 rounded-xl font-bold hover:bg-indigo-700 transition-all flex items-center justify-center">
                                            <span>Apply Now</span>
                                            <i class="fas fa-arrow-right ml-2 text-xs"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script>
const csrfToken = <?php echo json_encode(csrf_token()); ?>;

async function applyJob(jobId, btn) {
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Applying...';
    
    try {
        const response = await fetch('../includes/apply_job.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ job_id: jobId })
        });
        const result = await response.json();
        
        if (result.success) {
            btn.className = "flex-1 bg-green-100 text-green-600 py-3 rounded-xl font-bold flex items-center justify-center cursor-not-allowed";
            btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Applied';
            location.reload(); 
        } else {
            alert(result.error || 'Failed to apply');
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    } catch (err) {
        console.error(err);
        btn.disabled = false;
        btn.innerHTML = originalContent;
    }
}
</script>
</body>
</html>
