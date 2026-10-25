<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['user']);

$worker_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if ($worker_id <= 0) {
    header("Location: search_workers.php");
    exit();
}

// Fetch worker profile
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.email, u.created_at as member_since,
           wp.full_name, wp.category_id, wp.skills, wp.experience_years, wp.experience_details,
           wp.location, wp.availability, wp.daily_rate, wp.hourly_rate, wp.phone, wp.bio, wp.rating,
           c.name as category_name, c.icon as category_icon, c.description as category_desc
    FROM users u
    JOIN worker_profiles wp ON u.id = wp.user_id
    LEFT JOIN categories c ON wp.category_id = c.id
    WHERE u.id = ?
");
$stmt->execute([$worker_id]);
$worker = $stmt->fetch();

if (!$worker) {
    die("Worker not found or profile is incomplete. <a href='search_workers.php'>Return to Search</a>");
}

$csrf_token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($worker['full_name'] ?: $worker['username']); ?> - Worker Profile | Work Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 font-sans">

<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <?php include '../includes/user_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-y-auto">
        <!-- Topbar -->
        <header class="bg-white border-b border-slate-200/80 p-4 md:px-8 flex justify-between items-center sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-700 text-xl focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="search_workers.php" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Search
                </a>
            </div>
            <div class="flex items-center space-x-3">
                <a href="chat.php?worker_id=<?php echo $worker['id']; ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center space-x-1.5">
                    <i class="fas fa-comments"></i>
                    <span>Chat</span>
                </a>
                <button onclick="openHireModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-600/20 flex items-center space-x-1.5">
                    <i class="fas fa-paper-plane"></i>
                    <span>Hire Worker</span>
                </button>
            </div>
        </header>

        <!-- Profile Content -->
        <div class="p-4 md:p-8 space-y-6 max-w-5xl mx-auto w-full">
            
            <!-- Hero Card -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-sm">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 pb-6 border-b border-slate-100">
                    <div class="flex items-center space-x-5">
                        <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-indigo-600 to-indigo-800 text-white font-extrabold flex items-center justify-center text-3xl shadow-lg shadow-indigo-600/30">
                            <?php echo strtoupper(substr($worker['username'], 0, 1)); ?>
                        </div>
                        <div>
                            <div class="flex items-center space-x-3">
                                <h1 class="text-2xl font-bold text-slate-800"><?php echo htmlspecialchars($worker['full_name'] ?: $worker['username']); ?></h1>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">
                                    <i class="fas <?php echo htmlspecialchars($worker['category_icon'] ?: 'fa-tools'); ?> mr-1"></i>
                                    <?php echo htmlspecialchars($worker['category_name'] ?: 'Worker'); ?>
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">@<?php echo htmlspecialchars($worker['username']); ?> &bull; Member since <?php echo date('M Y', strtotime($worker['member_since'])); ?></p>
                            
                            <div class="flex items-center space-x-4 text-xs text-slate-500 mt-3">
                                <span><i class="fas fa-location-dot text-indigo-500 mr-1.5"></i><?php echo htmlspecialchars($worker['location'] ?: 'Location not specified'); ?></span>
                                <span><i class="fas fa-star text-amber-400 mr-1"></i><?php echo $worker['rating']; ?> / 5.0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Availability & Pricing -->
                    <div class="flex md:flex-col items-center md:items-end justify-between w-full md:w-auto gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                            <?php 
                            if ($worker['availability'] === 'available') echo 'bg-emerald-50 text-emerald-600 border border-emerald-200';
                            elseif ($worker['availability'] === 'busy') echo 'bg-amber-50 text-amber-600 border border-amber-200';
                            else echo 'bg-slate-100 text-slate-500';
                            ?>">
                            <?php if ($worker['availability'] === 'available'): ?>
                                <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></span>
                            <?php endif; ?>
                            <?php echo ucfirst($worker['availability']); ?>
                        </span>
                        <div class="text-right">
                            <p class="text-[10px] text-slate-400 font-semibold uppercase">Daily Rate</p>
                            <p class="text-xl font-extrabold text-slate-800">
                                ₹ <?php echo htmlspecialchars($worker['daily_rate'] ?: 'Negotiable'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action CTA Bar -->
                <div class="pt-6 flex flex-wrap gap-3">
                    <button onclick="openHireModal()" class="flex-1 sm:flex-none bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-2xl text-sm font-bold transition-all shadow-md shadow-indigo-600/20 flex items-center justify-center space-x-2">
                        <i class="fas fa-paper-plane"></i>
                        <span>Send Work Request / Hire</span>
                    </button>
                    <a href="chat.php?worker_id=<?php echo $worker['id']; ?>" class="flex-1 sm:flex-none bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3 rounded-2xl text-sm font-bold transition-all flex items-center justify-center space-x-2">
                        <i class="fas fa-comments"></i>
                        <span>Message Worker</span>
                    </a>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Left Details (2 cols) -->
                <div class="md:col-span-2 space-y-6">
                    <!-- About / Bio -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3">About Worker</h3>
                        <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-wrap">
                            <?php echo htmlspecialchars($worker['bio'] ?: 'No detailed bio provided yet. Contact the worker directly for further details.'); ?>
                        </p>
                    </div>

                    <!-- Skills & Specializations -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3">Skills & Expertise</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php 
                            $skills = array_filter(array_map('trim', explode(',', $worker['skills'] ?? 'General Skills')));
                            foreach ($skills as $s): 
                            ?>
                                <span class="px-3.5 py-1.5 bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-xl text-xs font-semibold">
                                    <i class="fas fa-check-circle mr-1.5 text-indigo-500"></i><?php echo htmlspecialchars($s); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Experience History -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3">Experience History</h3>
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800"><?php echo $worker['experience_years']; ?> Years in Trade</p>
                                <p class="text-xs text-slate-400">Total hands-on professional background</p>
                            </div>
                        </div>
                        <?php if(!empty($worker['experience_details'])): ?>
                            <p class="text-xs text-slate-600 leading-relaxed mt-2 pt-2 border-t border-slate-100">
                                <?php echo htmlspecialchars($worker['experience_details']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Sidebar Details (1 col) -->
                <div class="space-y-6">
                    <!-- Contact Information -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Contact Info</h3>
                        <div class="space-y-3 text-xs text-slate-600">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-400">Phone</p>
                                    <p class="font-bold text-slate-800"><?php echo htmlspecialchars($worker['phone'] ?: 'Available upon request'); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="truncate">
                                    <p class="text-[10px] text-slate-400">Email</p>
                                    <p class="font-bold text-slate-800 truncate"><?php echo htmlspecialchars($worker['email']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-400">Location</p>
                                    <p class="font-bold text-slate-800"><?php echo htmlspecialchars($worker['location'] ?: 'Not specified'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Overview Card -->
                    <div class="bg-indigo-950 text-white rounded-3xl p-6 shadow-md relative overflow-hidden">
                        <div class="absolute -right-4 -bottom-4 opacity-10 text-7xl">
                            <i class="fas <?php echo htmlspecialchars($worker['category_icon'] ?: 'fa-tools'); ?>"></i>
                        </div>
                        <h4 class="text-xs uppercase tracking-widest text-indigo-400 font-bold mb-1">Category Info</h4>
                        <p class="text-base font-bold mb-2"><?php echo htmlspecialchars($worker['category_name'] ?: 'Trade Worker'); ?></p>
                        <p class="text-xs text-slate-300 leading-relaxed"><?php echo htmlspecialchars($worker['category_desc'] ?: 'Certified service worker registered on Work Connect.'); ?></p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Send Work Request / Hire Worker -->
<div id="hireModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 md:p-8 shadow-2xl relative">
        <button onclick="closeHireModal()" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 text-lg">
            <i class="fas fa-times"></i>
        </button>

        <div class="mb-5">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-600 uppercase">Contact / Hire Worker</span>
            <h3 class="text-xl font-bold text-slate-800 mt-1">Send Work Request</h3>
            <p class="text-xs text-slate-400">Request service directly from <?php echo htmlspecialchars($worker['full_name'] ?: $worker['username']); ?></p>
        </div>

        <div id="modalAlert" class="hidden mb-4 p-3 rounded-xl text-xs font-semibold"></div>

        <form id="hireForm" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Task / Job Title *</label>
                <input type="text" id="req_title" placeholder="e.g. Bathroom pipe leak repair" required 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Task Description *</label>
                <textarea id="req_desc" rows="3" placeholder="Describe what work needs to be done, timing, and requirements..." required 
                          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Work Location *</label>
                    <input type="text" id="req_location" value="<?php echo htmlspecialchars($worker['location'] ?? ''); ?>" placeholder="Flat/House no, Area, City" required 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Preferred Date</label>
                    <input type="date" id="req_date" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Offered Budget / Proposed Price (₹)</label>
                <input type="text" id="req_budget" value="<?php echo htmlspecialchars($worker['daily_rate'] ?? ''); ?>" placeholder="e.g. ₹800" 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div class="pt-3 flex items-center justify-end space-x-3">
                <button type="button" onclick="closeHireModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition-colors">
                    Cancel
                </button>
                <button type="submit" id="submitReqBtn" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-600/20 flex items-center space-x-1.5">
                    <i class="fas fa-paper-plane"></i>
                    <span>Send Request</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const workerId = <?php echo json_encode($worker['id']); ?>;
const categoryId = <?php echo json_encode($worker['category_id']); ?>;
const csrfToken = <?php echo json_encode($csrf_token); ?>;

function openHireModal() {
    document.getElementById('hireModal').classList.remove('hidden');
}

function closeHireModal() {
    document.getElementById('hireModal').classList.add('hidden');
}

document.getElementById('hireForm').onsubmit = async (e) => {
    e.preventDefault();
    const btn = document.getElementById('submitReqBtn');
    const alertBox = document.getElementById('modalAlert');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Sending...';

    const payload = {
        worker_id: workerId,
        category_id: categoryId,
        title: document.getElementById('req_title').value.trim(),
        description: document.getElementById('req_desc').value.trim(),
        location: document.getElementById('req_location').value.trim(),
        work_date: document.getElementById('req_date').value,
        budget: document.getElementById('req_budget').value.trim()
    };

    try {
        const res = await fetch('../includes/send_work_request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
            alertBox.className = 'mb-4 p-3 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 block';
            alertBox.innerText = data.message;
            setTimeout(() => {
                window.location.href = 'requests.php';
            }, 1200);
        } else {
            alertBox.className = 'mb-4 p-3 rounded-xl text-xs font-semibold bg-red-50 text-red-700 border border-red-200 block';
            alertBox.innerText = data.error || 'Failed to submit request.';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> <span>Send Request</span>';
        }
    } catch (err) {
        alertBox.className = 'mb-4 p-3 rounded-xl text-xs font-semibold bg-red-50 text-red-700 border border-red-200 block';
        alertBox.innerText = 'Network error. Please try again.';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> <span>Send Request</span>';
    }
};
</script>
</body>
</html>
