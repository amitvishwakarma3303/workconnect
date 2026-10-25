<?php
$current_page = basename($_SERVER['PHP_SELF']);
$is_employee_area = strpos(str_replace('\\', '/', $_SERVER['PHP_SELF']), '/employee/') !== false;
$prefix = $is_employee_area ? '' : 'employee/';
?>
<aside id="sidebar"
    class="fixed md:static z-40 inset-y-0 left-0 w-64 bg-indigo-900 text-white transform -translate-x-full md:translate-x-0 transition-transform duration-300 flex flex-col">

    <div class="p-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold">Work Connect</h1>
            <p class="text-indigo-300 text-xs uppercase tracking-widest">Labour Portal</p>
        </div>
        <button onclick="toggleSidebar()" class="md:hidden">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="flex-grow mt-6 px-4 space-y-2">
        <a href="<?php echo $prefix; ?>dashboard.php"
           class="flex items-center space-x-3 p-3 rounded-xl <?php echo $current_page == 'dashboard.php' ? 'bg-indigo-800' : 'hover:bg-indigo-800'; ?> transition-colors">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?php echo $prefix; ?>jobs.php"
           class="flex items-center space-x-3 p-3 rounded-xl <?php echo $current_page == 'jobs.php' ? 'bg-indigo-800' : 'hover:bg-indigo-800'; ?> transition-colors">
            <i class="fas fa-search"></i>
            <span>Find Jobs</span>
        </a>
        <a href="<?php echo $prefix; ?>applications.php"
           class="flex items-center space-x-3 p-3 rounded-xl <?php echo $current_page == 'applications.php' ? 'bg-indigo-800' : 'hover:bg-indigo-800'; ?> transition-colors">
            <i class="fas fa-check-circle"></i>
            <span>My Applications</span>
        </a>
        <a href="<?php echo $prefix; ?>profile.php"
           class="flex items-center space-x-3 p-3 rounded-xl <?php echo $current_page == 'profile.php' ? 'bg-indigo-800' : 'hover:bg-indigo-800'; ?> transition-colors">
            <i class="fas fa-user"></i>
            <span>My Profile</span>
        </a>
        <a href="<?php echo $prefix; ?>chat.php"
           class="flex items-center space-x-3 p-3 rounded-xl <?php echo $current_page == 'chat.php' ? 'bg-indigo-800' : 'hover:bg-indigo-800'; ?> transition-colors">
            <i class="fas fa-comments"></i>
            <span>Messages</span>
        </a>
        <a href="<?php echo $prefix; ?>../notifications.php"
           class="flex items-center justify-between p-3 rounded-xl hover:bg-indigo-800 transition-colors">
            <div class="flex items-center space-x-3">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </div>
            <span id="notif-badge" class="hidden bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">0</span>
        </a>
    </nav>

    <div class="p-4 border-t border-indigo-800">
        <a href="<?php echo $prefix; ?>../logout.php"
           class="flex items-center space-x-3 p-3 rounded-xl hover:bg-red-600 transition-colors">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<div id="overlay" onclick="toggleSidebar()" 
     class="fixed inset-0 bg-black bg-opacity-40 hidden z-30 md:hidden"></div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
    document.getElementById('overlay').classList.toggle('hidden');
}

// Notification system
async function updateNotifBadge() {
    try {
        const path = window.location.pathname.includes('/employer/') || window.location.pathname.includes('/employee/') 
            ? '../includes/get_notifications.php' 
            : 'includes/get_notifications.php';
        const res = await fetch(path);
        const data = await res.json();
        if (data.success && data.unread_count > 0) {
            const badge = document.getElementById('notif-badge');
            badge.innerText = data.unread_count;
            badge.classList.remove('hidden');
        }
    } catch (err) { console.error(err); }
}
updateNotifBadge();
setInterval(updateNotifBadge, 30000); 
</script>
