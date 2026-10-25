<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['employer']);

$employee_id = $_GET['employee_id'] ?? null;
if (!$employee_id) {
    header("Location: workforce.php");
    exit();
}

// Fetch employee details
$stmt = $pdo->prepare("SELECT users.username, employee_profiles.skills FROM users LEFT JOIN employee_profiles ON users.id = employee_profiles.user_id WHERE users.id = ?");
$stmt->execute([$employee_id]);
$employee = $stmt->fetch();

if (!$employee) {
    die("Employee not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($employee['username']); ?> - Work Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .chat-container { height: calc(100vh - 180px); }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    <?php include '../includes/employer_sidebar.php'; ?>

    <main class="flex-grow flex flex-col overflow-hidden">
        <!-- Chat Header -->
        <header class="bg-white border-b border-gray-200 p-4 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center space-x-4">
                <button onclick="toggleSidebar()" class="md:hidden text-indigo-900">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold">
                    <?php echo strtoupper(substr($employee['username'], 0, 1)); ?>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($employee['username']); ?></h2>
                    <p class="text-xs text-green-500 font-medium"><i class="fas fa-circle text-[8px] mr-1"></i> Online</p>
                </div>
            </div>
            <a href="workforce.php" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></a>
        </header>

        <!-- Messages Area -->
        <div id="chatMessages" class="chat-container overflow-y-auto p-4 md:p-8 space-y-4 bg-[#f0f2f5]">
            <!-- Messages will be loaded here -->
            <div class="flex justify-center my-4">
                <span class="bg-white/80 text-gray-500 text-xs px-3 py-1 rounded-full shadow-sm">Starting conversation...</span>
            </div>
        </div>

        <!-- Message Input -->
        <div class="p-4 bg-white border-t border-gray-200 sticky bottom-0">
            <form id="chatForm" class="max-w-4xl mx-auto flex items-center space-x-4">
                <input type="text" id="messageInput" placeholder="Type a message..." 
                       class="flex-grow p-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                <button type="submit" class="w-12 h-12 bg-indigo-600 text-white rounded-full flex items-center justify-center hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </main>

    <script>
        const chatMessages = document.getElementById('chatMessages');
        const chatForm = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');
        const employeeId = <?php echo json_encode($employee_id); ?>;
        const currentUserId = <?php echo json_encode($_SESSION['user_id']); ?>;
        const csrfToken = <?php echo json_encode(csrf_token()); ?>;
        let lastId = 0;

        // Initialize Chat
        async function loadHistory() {
            try {
                const response = await fetch(`../includes/get_messages.php?other_id=${employeeId}`);
                const data = await response.json();
                chatMessages.innerHTML = '';
                if (data.length === 0) {
                    chatMessages.innerHTML = '<div class="text-center py-10 opacity-50 italic text-sm">No messages yet. Start the conversation!</div>';
                } else {
                    data.forEach(msg => {
                        if (msg.id > lastId) lastId = msg.id;
                        appendMessage(msg);
                    });
                }
            } catch (err) {
                console.error('Failed to load history:', err);
            }
        }

        function startSSE() {
            const eventSource = new EventSource(`../includes/chat_sse.php?last_id=${lastId}&other_id=${employeeId}`);
            
            eventSource.onmessage = function(event) {
                const data = JSON.parse(event.data);
                if (data.id > lastId) {
                    lastId = data.id;
                    // Remove "No messages" text if it exists
                    if (chatMessages.querySelector('.italic')) {
                        chatMessages.innerHTML = '';
                    }
                    appendMessage(data);
                }
            };

            eventSource.onerror = function() {
                eventSource.close();
                setTimeout(startSSE, 3000); // Reconnect after 3 seconds
            };
        }

        function appendMessage(msg) {
            const isMe = msg.sender_id == currentUserId;
            const msgDiv = document.createElement('div');
            msgDiv.className = `flex ${isMe ? 'justify-end' : 'justify-start'}`;
            
            msgDiv.innerHTML = `
                <div class="max-w-[80%] md:max-w-[60%] p-4 rounded-3xl shadow-sm ${isMe ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-white text-gray-800 rounded-tl-none border border-gray-100'}">
                    <p class="text-sm">${escapeHtml(msg.message)}</p>
                    <p class="text-[10px] mt-2 opacity-70 text-right">${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>
                </div>
            `;
            chatMessages.appendChild(msgDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Send Message
        chatForm.onsubmit = async (e) => {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;

            messageInput.value = '';
            
            try {
                const response = await fetch('../includes/send_message.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({
                        receiver_id: employeeId,
                        message: message
                    })
                });
                const result = await response.json();
                if (!result.success) alert('Failed to send message: ' + result.error);
            } catch (err) {
                console.error(err);
            }
        };

        // Load existing messages first (Optional, but good for UX)
        // For simplicity, we'll let SSE handle new ones, but usually you'd fetch history here.

        loadHistory();
        startSSE();

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }
    </script>
</body>
</html>
