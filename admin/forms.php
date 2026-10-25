<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        header("Location: forms.php?error=Invalid request token");
        exit();
    }

    $delete_id = (int) $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM contact_forms WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("Location: forms.php?success=Form submission deleted");
    exit();
}

$search = trim($_GET['q'] ?? '');
$query = "SELECT * FROM contact_forms";
$params = [];

if ($search !== '') {
    $query .= " WHERE name LIKE ? OR email LIKE ? OR city LIKE ? OR message LIKE ?";
    $like = '%' . $search . '%';
    $params = [$like, $like, $like, $like];
}

$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$forms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Forms - Admin</title>
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
            <h2 class="text-xl font-bold text-gray-800">Contact Form Submissions</h2>
            <form method="GET" class="relative max-w-sm w-full hidden md:block">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, email, city, message..." class="w-full pl-10 pr-4 py-2.5 bg-gray-100 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
            </form>
        </header>

        <div class="p-6 md:p-10">
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-50 text-green-600 p-4 rounded-2xl mb-6 text-sm font-bold">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-2xl mb-6 text-sm font-bold">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50/50 text-gray-500 text-[10px] uppercase tracking-widest font-bold">
                                <th class="p-6">Name</th>
                                <th class="p-6">Email</th>
                                <th class="p-6">City</th>
                                <th class="p-6">Source</th>
                                <th class="p-6">Message</th>
                                <th class="p-6">Submitted</th>
                                <th class="p-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($forms)): ?>
                                <tr>
                                    <td colspan="7" class="p-10 text-center text-gray-500">No form submissions found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($forms as $form): ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors align-top">
                                        <td class="p-6 font-semibold text-gray-900"><?php echo htmlspecialchars($form['name']); ?></td>
                                        <td class="p-6 text-sm text-gray-700"><?php echo htmlspecialchars($form['email']); ?></td>
                                        <td class="p-6 text-sm text-gray-700"><?php echo htmlspecialchars($form['city'] ?: '-'); ?></td>
                                        <td class="p-6">
                                            <span class="px-2 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider <?php echo $form['source_page'] === 'home' ? 'bg-indigo-50 text-indigo-600' : 'bg-emerald-50 text-emerald-600'; ?>">
                                                <?php echo htmlspecialchars($form['source_page']); ?>
                                            </span>
                                        </td>
                                        <td class="p-6 text-sm text-gray-700 max-w-sm whitespace-pre-wrap"><?php echo htmlspecialchars($form['message']); ?></td>
                                        <td class="p-6 text-xs text-gray-500"><?php echo date('M d, Y h:i A', strtotime($form['created_at'])); ?></td>
                                        <td class="p-6 text-right">
                                            <form method="POST" class="inline" onsubmit="return confirm('Delete this form submission?');">
                                                <input type="hidden" name="delete_id" value="<?php echo (int) $form['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                                                <button type="submit" class="text-red-500 hover:text-red-700 p-2">
                                                    <i class="fas fa-trash-alt"></i>
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
</div>
</body>
</html>
