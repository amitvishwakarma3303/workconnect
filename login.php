<?php
session_start();
require_once 'includes/auth_middleware.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    header("Location: " . dashboardUrlByRole($_SESSION['role']));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Connect - Login & Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="max-w-4xl w-full flex flex-col md:flex-row glass rounded-3xl overflow-hidden shadow-2xl">
        <!-- Info Section -->
        <div class="md:w-1/2 bg-indigo-600 p-12 text-white flex flex-col justify-center">
            <h1 class="text-4xl font-bold mb-6">Work Connect</h1>
            <p class="text-lg opacity-90 mb-8">The smartest way to connect skilled labour with opportunities. Join our community today!</p>
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg"><i class="fas fa-check"></i></div>
                    <span>Verified Employers</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg"><i class="fas fa-check"></i></div>
                    <span>Secure Payments</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg"><i class="fas fa-check"></i></div>
                    <span>Real-time Tracking</span>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="md:w-1/2 p-12 bg-white">
            <div id="login-form">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Welcome Back</h2>
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <form action="login_process.php" method="POST" class="space-y-6">
                    <div class="relative">
                        <i class="fas fa-user absolute left-4 top-4 text-gray-400"></i>
                        <input type="text" name="username" placeholder="Username" required class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-4 text-gray-400"></i>
                        <input type="password" name="password" placeholder="Password" required class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                    <!-- <div class="flex justify-end">
                        <a href="#" class="text-sm text-indigo-600 hover:underline">Forgot password?</a>
                    </div> -->
                    <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">Login</button>
                    <p class="text-center text-gray-600 mt-6">Don't have an account? <a href="javascript:void(0)" onclick="toggleForm()" class="text-indigo-600 font-semibold hover:underline">Register</a></p>
                </form>
            </div>

            <div id="register-form" class="hidden">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Create Account</h2>
                <form action="register.php" method="POST" class="space-y-6">
                    <div class="relative">
                        <i class="fas fa-user absolute left-4 top-4 text-gray-400"></i>
                        <input type="text" name="username" placeholder="Username" required class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-4 top-4 text-gray-400"></i>
                        <input type="email" name="email" placeholder="Email Address" required class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-4 text-gray-400"></i>
                        <input type="password" name="password" placeholder="Password" required class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Register as:</label>
                        <select name="role" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="employee">Employee</option>
                            <option value="employer">Employer</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">Create Account</button>
                    <p class="text-center text-gray-600 mt-6">Already have an account? <a href="javascript:void(0)" onclick="toggleForm()" class="text-indigo-600 font-semibold hover:underline">Login</a></p>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleForm() {
            document.getElementById('login-form').classList.toggle('hidden');
            document.getElementById('register-form').classList.toggle('hidden');
        }
    </script>
</body>
</html>
