<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['user']);

// Get Filter Parameters
$categoryId = !empty($_GET['category']) ? (int)$_GET['category'] : null;
$location = trim($_GET['location'] ?? '');
$skills = trim($_GET['skills'] ?? '');
$availability = trim($_GET['availability'] ?? '');
$minExp = isset($_GET['exp']) && is_numeric($_GET['exp']) ? (int)$_GET['exp'] : null;

// Build Dynamic Search Query
$query = "
    SELECT u.id, u.username, u.email, 
           wp.full_name, wp.category_id, wp.skills, wp.experience_years, wp.experience_details, 
           wp.location, wp.availability, wp.daily_rate, wp.hourly_rate, wp.phone, wp.bio, wp.rating,
           c.name as category_name, c.icon as category_icon
    FROM users u
    JOIN worker_profiles wp ON u.id = wp.user_id
    LEFT JOIN categories c ON wp.category_id = c.id
    WHERE 1=1
";
$params = [];

if ($categoryId) {
    $query .= " AND wp.category_id = ?";
    $params[] = $categoryId;
}

if (!empty($location)) {
    $query .= " AND wp.location LIKE ?";
    $params[] = "%$location%";
}

if (!empty($skills)) {
    $query .= " AND (wp.skills LIKE ? OR wp.full_name LIKE ? OR u.username LIKE ?)";
    $params[] = "%$skills%";
    $params[] = "%$skills%";
    $params[] = "%$skills%";
}

if (!empty($availability)) {
    $query .= " AND wp.availability = ?";
    $params[] = $availability;
}

if ($minExp !== null && $minExp > 0) {
    $query .= " AND wp.experience_years >= ?";
    $params[] = $minExp;
}

$query .= " ORDER BY (wp.availability = 'available') DESC, wp.experience_years DESC, wp.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$workers = $stmt->fetchAll();

// Fetch all categories for filter dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Workers - Work Connect</title>
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
                <div>
                    <h2 class="text-lg md:text-xl font-bold text-slate-800">Worker Search Module</h2>
                    <p class="text-xs text-slate-400">Search and filter skilled labourers by trade & location</p>
                </div>
            </div>
            <div class="text-xs text-slate-500 font-semibold hidden sm:block">
                Matching Results: <span class="text-indigo-600 font-bold"><?php echo count($workers); ?> workers</span>
            </div>
        </header>

        <!-- Body Content -->
        <div class="p-4 md:p-8 space-y-6 max-w-7xl mx-auto w-full">
            
            <!-- Search & Filter Card -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                <form action="" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Category Filter -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                <i class="fas fa-layer-group text-indigo-500 mr-1"></i> Work Category
                            </label>
                            <select name="category" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="">All Categories (Plumber, Electrician...)</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($categoryId == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Location Filter -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                <i class="fas fa-location-dot text-indigo-500 mr-1"></i> Location / City
                            </label>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>" placeholder="e.g. Nallasopara, Vasai..." 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>

                        <!-- Skills / Keyword -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                <i class="fas fa-tools text-indigo-500 mr-1"></i> Skills / Name
                            </label>
                            <input type="text" name="skills" value="<?php echo htmlspecialchars($skills); ?>" placeholder="e.g. Welding, Wiring, Sunil..." 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>

                        <!-- Availability & Exp -->
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Availability</label>
                                <select name="availability" class="w-full px-2.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="">Any</option>
                                    <option value="available" <?php echo ($availability === 'available') ? 'selected' : ''; ?>>Available</option>
                                    <option value="busy" <?php echo ($availability === 'busy') ? 'selected' : ''; ?>>Busy</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Min Exp</label>
                                <select name="exp" class="w-full px-2.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="">Any</option>
                                    <option value="1" <?php echo ($minExp == 1) ? 'selected' : ''; ?>>1+ yrs</option>
                                    <option value="3" <?php echo ($minExp == 3) ? 'selected' : ''; ?>>3+ yrs</option>
                                    <option value="5" <?php echo ($minExp == 5) ? 'selected' : ''; ?>>5+ yrs</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <a href="search_workers.php" class="text-xs font-bold text-slate-400 hover:text-indigo-600 transition-colors">
                            Reset Filters
                        </a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-600/20 flex items-center space-x-2">
                            <i class="fas fa-search"></i>
                            <span>Search Workers</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Workers Grid -->
            <div>
                <?php if (empty($workers)): ?>
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center max-w-xl mx-auto shadow-sm">
                        <div class="w-20 h-20 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center text-3xl mx-auto mb-4">
                            <i class="fas fa-user-slash"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">No Workers Found</h3>
                        <p class="text-xs text-slate-500 mt-1">Try broadening your search criteria or resetting filters to find available workers.</p>
                        <a href="search_workers.php" class="inline-block mt-4 bg-indigo-600 text-white px-5 py-2 rounded-xl text-xs font-bold hover:bg-indigo-700 transition-colors">
                            Clear Filters
                        </a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($workers as $w): ?>
                            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between group">
                                <div>
                                    <!-- Header Row -->
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white font-extrabold flex items-center justify-center text-lg shadow-md shadow-indigo-500/20">
                                                <?php echo strtoupper(substr($w['username'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-slate-800 text-base leading-tight group-hover:text-indigo-600 transition-colors">
                                                    <?php echo htmlspecialchars($w['full_name'] ?: $w['username']); ?>
                                                </h3>
                                                <span class="inline-flex items-center text-xs font-semibold text-indigo-600 mt-0.5">
                                                    <i class="fas <?php echo htmlspecialchars($w['category_icon'] ?: 'fa-tools'); ?> mr-1 text-[10px]"></i>
                                                    <?php echo htmlspecialchars($w['category_name'] ?: 'General Worker'); ?>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Availability status -->
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                            <?php 
                                            if ($w['availability'] === 'available') echo 'bg-emerald-50 text-emerald-600 border border-emerald-200';
                                            elseif ($w['availability'] === 'busy') echo 'bg-amber-50 text-amber-600 border border-amber-200';
                                            else echo 'bg-slate-100 text-slate-500';
                                            ?>">
                                            <?php if ($w['availability'] === 'available'): ?>
                                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span>
                                            <?php endif; ?>
                                            <?php echo ucfirst($w['availability']); ?>
                                        </span>
                                    </div>

                                    <!-- Bio snippet -->
                                    <p class="text-xs text-slate-500 line-clamp-2 mb-4">
                                        <?php echo htmlspecialchars($w['bio'] ?: 'Reliable and experienced worker available for direct hiring and on-demand labor.'); ?>
                                    </p>

                                    <!-- Skills Tags -->
                                    <div class="flex flex-wrap gap-1.5 mb-4">
                                        <?php 
                                        $skillsArray = array_filter(array_map('trim', explode(',', $w['skills'] ?? 'General Labor')));
                                        foreach (array_slice($skillsArray, 0, 3) as $skill): 
                                        ?>
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-medium">
                                                <?php echo htmlspecialchars($skill); ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if(count($skillsArray) > 3): ?>
                                            <span class="px-1.5 py-0.5 text-slate-400 text-[10px] font-semibold">+<?php echo count($skillsArray) - 3; ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Location & Experience -->
                                    <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-500 py-3 border-t border-b border-slate-100 mb-4">
                                        <div class="flex items-center truncate">
                                            <i class="fas fa-location-dot text-slate-400 mr-1.5"></i>
                                            <span class="truncate"><?php echo htmlspecialchars($w['location'] ?: 'Location not set'); ?></span>
                                        </div>
                                        <div class="flex items-center justify-end">
                                            <i class="fas fa-briefcase text-slate-400 mr-1.5"></i>
                                            <span><?php echo $w['experience_years']; ?>+ Yrs Exp</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer Actions -->
                                <div class="flex items-center justify-between pt-1">
                                    <div>
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase">Daily Rate</p>
                                        <p class="text-sm font-extrabold text-slate-800">
                                            ₹ <?php echo htmlspecialchars($w['daily_rate'] ?: 'Negotiable'); ?>
                                        </p>
                                    </div>

                                    <div class="flex space-x-2">
                                        <a href="worker_profile.php?id=<?php echo $w['id']; ?>" 
                                           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm">
                                            View Profile & Hire
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

</body>
</html>
