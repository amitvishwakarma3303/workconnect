<?php
// employer/applications.php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['employer']);

$stmt = $pdo->prepare("
    SELECT ja.*, j.title as job_title, u.username as employee_name, u.email as employee_email, ep.skills, ep.phone
    FROM job_applications ja
    JOIN jobs j ON ja.job_id = j.id
    JOIN users u ON ja.employee_id = u.id
    LEFT JOIN employee_profiles ep ON u.id = ep.user_id
    WHERE j.employer_id = ?
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
    <title>Manage Applications - Work Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
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
                <h2 class="text-xl font-semibold text-gray-800">Job Applications</h2>
            </div>
        </header>

        <div class="p-4 md:p-8">
            <?php if (empty($applications)): ?>
                <div class="bg-white p-12 rounded-3xl text-center border border-gray-100 shadow-sm">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400 text-3xl">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">No applications yet</h3>
                    <p class="text-gray-500 mt-2">When employees apply for your jobs, they'll appear here.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-6">
                    <?php foreach ($applications as $app): ?>
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-center space-x-4">
                                <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xl">
                                    <?php echo strtoupper(substr($app['employee_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($app['employee_name']); ?></h3>
                                    <p class="text-xs text-indigo-600 font-medium uppercase mt-1">Applied for: <?php echo htmlspecialchars($app['job_title']); ?></p>
                                    <div class="flex items-center text-[10px] text-gray-400 mt-1 space-x-3">
                                        <span><i class="fas fa-calendar-alt mr-1"></i> <?php echo date('M d, Y', strtotime($app['applied_at'])); ?></span>
                                        <span><i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($app['employee_email']); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-3">
                                <?php if ($app['status'] === 'pending'): ?>
                                    <button onclick="handleApp(<?php echo $app['id']; ?>, 'accept', this)" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition-all text-sm">
                                        Hire & Connect
                                    </button>
                                    <button onclick="handleApp(<?php echo $app['id']; ?>, 'reject', this)" class="bg-gray-100 text-gray-600 px-6 py-2.5 rounded-xl font-bold hover:bg-gray-200 transition-all text-sm">
                                        Reject
                                    </button>
                                <?php else: ?>
                                    <span class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider <?php echo $app['status'] === 'accepted' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'; ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                    <?php if ($app['status'] === 'accepted'): ?>
                                        <a href="chat.php?employee_id=<?php echo $app['employee_id']; ?>" class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl font-bold hover:bg-indigo-100 transition-all text-sm">
                                            <i class="fas fa-comment-dots mr-1"></i> Chat
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
    const csrfToken = <?php echo json_encode(csrf_token()); ?>;

    async function handleApp(appId, action, btn) {
        if (!confirm(`Are you sure you want to ${action} this application?`)) return;
        
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        try {
            const response = await fetch('../includes/handle_application.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ application_id: appId, action: action })
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert(result.error || 'Failed to process request');
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
