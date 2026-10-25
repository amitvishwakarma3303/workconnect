<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['admin']);

// Handle Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        header("Location: users.php?error=Invalid request token");
        exit;
    }

    $id = (int) $_POST['delete_id'];
    if ($id !== (int) $_SESSION['user_id']) { // Don't delete yourself
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: users.php?success=User deleted");
        exit;
    }
}

$searchTerm = $_GET['q'] ?? '';
$query = "SELECT users.*, roles.role_name FROM users JOIN roles ON users.role_id = roles.id";
$params = [];

if (!empty($searchTerm)) {
    $query .= " WHERE username LIKE ? OR email LIKE ?";
    $params = ["%$searchTerm%", "%$searchTerm%"];
}

$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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
            <h2 class="text-xl font-bold text-gray-800">Manage Users</h2>
            <form action="" method="GET" class="relative max-w-sm w-full hidden md:block">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                <input type="text" name="q" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Search name or email..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-gray-100 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
            </form>
        </header>

        <div class="p-6 md:p-10">
            
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-50 text-green-600 p-4 rounded-2xl mb-6 text-sm font-bold flex items-center">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-2xl mb-6 text-sm font-bold flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50/50 text-gray-500 text-[10px] uppercase tracking-widest font-bold">
                                <th class="p-6">User Details</th>
                                <th class="p-6">Role</th>
                                <th class="p-6">Joined Date</th>
                                <th class="p-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="p-6">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-bold text-sm">
                                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900"><?php echo htmlspecialchars($user['username']); ?></p>
                                                <p class="text-xs text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-6">
                                        <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider <?php echo $user['role_name'] == 'employer' ? 'bg-purple-50 text-purple-600' : ($user['role_name'] == 'admin' ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600'); ?>">
                                            <?php echo $user['role_name']; ?>
                                        </span>
                                    </td>
                                    <td class="p-6 text-xs text-gray-500">
                                        <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td class="p-6 text-right">
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? All their data will be permanently removed.');">
                                                <input type="hidden" name="delete_id" value="<?php echo (int)$user['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                                                <button type="submit" class="text-red-400 hover:text-red-600 p-2 transition-colors">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <button class="text-gray-400 hover:text-indigo-600 p-2"><i class="fas fa-edit"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>
