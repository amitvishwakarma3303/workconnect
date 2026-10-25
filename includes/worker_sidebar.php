<?php
// includes/worker_sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
$is_worker_area = strpos(str_replace('\\', '/', $_SERVER['PHP_SELF']), '/worker/') !== false;
$prefix = $is_worker_area ? '' : 'worker/';
?>
<!-- Mobile Overlay -->
<div id="overlay" onclick="toggleSidebar()" 
     class="fixed inset-0 bg-black bg-opacity-40 hidden z-30 md:hidden"></div>

<!-- Sidebar -->
<aside id="sidebar"
    class="fixed md:static z-40 inset-y-0 left-0 w-64 bg-slate-900 text-white transform -translate-x-full md:translate-x-0 transition-transform duration-300 flex flex-col h-screen border-r border-slate-800">

    <div class="p-6 flex justify-between items-center border-b border-slate-800/80">
        <a href="<?php echo $prefix; ?>dashboard.php" class="flex items-center space-x-3">
            <div style="background:#fff;border-radius:10px;padding:4px;display:flex;align-items:center;justify-content:center;width:44px;height:44px;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,0.25);">
                <img src="<?php echo $is_worker_area ? '../' : ''; ?>assets/logo.png" alt="Work Connect Logo" style="height:34px;width:34px;object-fit:contain;" />
            </div>
            <div>
                <h1 class="text-xl font-bold leading-none text-white">Work Connect</h1>
                <p class="text-emerald-400 text-[10px] uppercase tracking-widest font-bold mt-1">Worker Portal</p>
            </div>
        </a>
        <button onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-white">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <nav class="flex-grow mt-4 px-3 space-y-1.5 overflow-y-auto">
        <a href="<?php echo $prefix; ?>dashboard.php"
           class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo ($current_page == 'dashboard.php') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?> transition-all">
            <i class="fas fa-chart-line w-5"></i>
            <span>Dashboard</span>
        </a>

        <a href="<?php echo $prefix; ?>requests.php"
           class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo ($current_page == 'requests.php') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?> transition-all">
            <i class="fas fa-inbox w-5"></i>
            <span>Work Requests</span>
        </a>

        <a href="<?php echo $prefix; ?>profile.php"
           class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo ($current_page == 'profile.php') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?> transition-all">
            <i class="fas fa-user-gear w-5"></i>
            <span>My Profile & Skills</span>
        </a>

        <a href="<?php echo $prefix; ?>chat.php"
           class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo ($current_page == 'chat.php') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?> transition-all">
            <i class="fas fa-comments w-5"></i>
            <span>Messages / Chat</span>
        </a>

        <a href="<?php echo $prefix; ?>../notifications.php"
           class="flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-all">
            <div class="flex items-center space-x-3">
                <i class="fas fa-bell w-5"></i>
                <span>Notifications</span>
            </div>
            <span id="notif-badge" class="hidden bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">0</span>
        </a>
    </nav>

    <div class="p-4 border-t border-slate-800/80">
        <div class="mb-3 px-3 py-2 bg-slate-800/60 rounded-xl flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs uppercase">
                <?php echo strtoupper(substr($_SESSION['username'] ?? 'W', 0, 1)); ?>
            </div>
            <div class="truncate flex-1">
                <p class="text-xs font-bold text-white truncate"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Worker'); ?></p>
                <p class="text-[10px] text-emerald-400 font-semibold">Skilled Worker</p>
            </div>
        </div>
        <a href="<?php echo $prefix; ?>../logout.php"
           class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-red-400 hover:bg-red-500/10 transition-colors">
            <i class="fas fa-sign-out-alt w-5"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

async function updateNotifBadge() {
    try {
        const path = window.location.pathname.includes('/user/') || window.location.pathname.includes('/worker/') || window.location.pathname.includes('/admin/')
            ? '../includes/get_notifications.php' 
            : 'includes/get_notifications.php';
        const res = await fetch(path);
        const data = await res.json();
        if (data.success && data.unread_count > 0) {
            const badge = document.getElementById('notif-badge');
            if (badge) {
                badge.innerText = data.unread_count;
                badge.classList.remove('hidden');
            }
        }
    } catch (err) { console.error(err); }
}
updateNotifBadge();
setInterval(updateNotifBadge, 30000);
</script>
