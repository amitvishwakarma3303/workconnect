<?php
$current_page = basename($_SERVER['PHP_SELF']);
$is_admin_area = strpos(str_replace('\\', '/', $_SERVER['PHP_SELF']), '/admin/') !== false;
$prefix = $is_admin_area ? '' : 'admin/';
?>
<aside id="sidebar"
    class="fixed md:static z-40 inset-y-0 left-0 w-64 bg-slate-900 text-white transform -translate-x-full md:translate-x-0 transition-transform duration-300 flex flex-col h-screen border-r border-slate-800">

    <div class="p-6 flex justify-between items-center border-b border-slate-800/80">
        <a href="<?php echo $prefix; ?>dashboard.php" class="flex items-center space-x-3">
            <div style="background:#fff;border-radius:10px;padding:4px;display:flex;align-items:center;justify-content:center;width:44px;height:44px;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,0.25);">
                <img src="<?php echo $is_admin_area ? '../' : ''; ?>assets/logo.png" alt="Work Connect Logo" style="height:34px;width:34px;object-fit:contain;" />
            </div>
            <div>
                <h1 class="text-xl font-bold leading-none text-white">Work Connect</h1>
                <p class="text-indigo-400 text-[10px] uppercase tracking-widest font-bold mt-1">Admin Control</p>
            </div>
        </a>
        <button onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-white">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <nav class="flex-grow mt-4 px-3 space-y-1.5 overflow-y-auto">
        <a href="<?php echo $prefix; ?>dashboard.php"
           class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo $current_page == 'dashboard.php' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?> transition-all">
            <i class="fas fa-chart-line w-5"></i>
            <span>Overview</span>
        </a>

        <a href="<?php echo $prefix; ?>users.php"
           class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo $current_page == 'users.php' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?> transition-all">
            <i class="fas fa-users w-5"></i>
            <span>Manage Users</span>
        </a>

        <a href="<?php echo $prefix; ?>workers.php"
           class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo $current_page == 'workers.php' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?> transition-all">
            <i class="fas fa-hard-hat w-5"></i>
            <span>Manage Workers</span>
        </a>

        <a href="<?php echo $prefix; ?>categories.php"
           class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo $current_page == 'categories.php' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?> transition-all">
            <i class="fas fa-layer-group w-5"></i>
            <span>Work Categories</span>
        </a>

        <a href="<?php echo $prefix; ?>requests.php"
           class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo $current_page == 'requests.php' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?> transition-all">
            <i class="fas fa-clipboard-list w-5"></i>
            <span>Work Requests</span>
        </a>

        <a href="<?php echo $prefix; ?>forms.php"
           class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo $current_page == 'forms.php' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?> transition-all">
            <i class="fas fa-envelope-open-text w-5"></i>
            <span>Contact Inquiries</span>
        </a>
    </nav>

    <div class="p-4 border-t border-slate-800/80">
        <a href="<?php echo $prefix; ?>../logout.php"
           class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-red-400 hover:bg-red-500/10 transition-colors">
            <i class="fas fa-sign-out-alt w-5"></i>
            <span>System Logout</span>
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
</script>
