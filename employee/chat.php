<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['employee']);

$employer_id = $_GET['employer_id'] ?? null;
$employer_name = "Employer";

if ($employer_id) {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ? AND role_id = (SELECT id FROM roles WHERE role_name = 'employer')");
    $stmt->execute([$employer_id]);
    $employer = $stmt->fetch();
    if ($employer) {
        $employer_name = $employer['username'];
    } else {
        header("Location: dashboard.php");
        exit;
    }
} else {
    // Logic to pick most recent chat if no ID provided
    $stmt = $pdo->prepare("SELECT DISTINCT 
        CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as other_id 
        FROM messages WHERE sender_id = ? OR receiver_id = ? 
        ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $recent = $stmt->fetch();
    if ($recent) {
        header("Location: chat.php?employer_id=" . $recent['other_id']);
        exit;
    }
}

// Fetch chat list for the sidebar
$stmt = $pdo->prepare("SELECT DISTINCT users.id, users.username FROM users 
    JOIN roles ON users.role_id = roles.id
    JOIN messages ON (users.id = messages.sender_id OR users.id = messages.receiver_id)
    WHERE (messages.sender_id = ? OR messages.receiver_id = ?) AND users.id != ? AND roles.role_name = 'employer'");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$chat_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($employer_name); ?> - Work Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .messages-container::-webkit-scrollbar { width: 5px; }
        .messages-container::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-50 font-sans">

<div class="flex h-screen overflow-hidden">
    
    <!-- Desktop Sidebar (Internal Chat List) -->
    <?php include '../includes/employee_sidebar.php'; ?>

    <div class="w-80 bg-white border-r border-gray-100 flex-col hidden md:flex">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">Messages</h2>
            <a href="dashboard.php" class="text-indigo-600 hover:bg-indigo-50 p-2 rounded-lg transition-colors">
                <i class="fas fa-home"></i>
            </a>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-2">
            <?php foreach ($chat_list as $chat): ?>
                <a href="chat.php?employer_id=<?php echo $chat['id']; ?>" 
                   class="flex items-center space-x-3 p-3 rounded-xl <?php echo $employer_id == $chat['id'] ? 'bg-indigo-50 border border-indigo-100' : 'hover:bg-gray-50'; ?> transition-all">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold">
                        <?php echo strtoupper(substr($chat['username'], 0, 1)); ?>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($chat['username']); ?></p>
                        <p class="text-xs text-green-500">Connected</p>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if (empty($chat_list)): ?>
                <p class="text-center text-gray-400 text-sm mt-10">No active chats</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="flex-1 flex flex-col bg-gray-50">
        
        <!-- Header -->
        <header class="bg-white border-b border-gray-100 p-4 md:p-5 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-3">
                <a href="dashboard.php" class="md:hidden text-gray-500 mr-2"><i class="fas fa-arrow-left"></i></a>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                    <?php echo strtoupper(substr($employer_name, 0, 1)); ?>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-sm md:text-base"><?php echo htmlspecialchars($employer_name); ?></h3>
                    <p class="text-xs text-green-500 flex items-center">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span> Active Now
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <button class="text-gray-400 hover:text-indigo-600"><i class="fas fa-phone"></i></button>
                <button class="text-gray-400 hover:text-indigo-600"><i class="fas fa-video"></i></button>
                <button class="text-gray-400 hover:text-indigo-600"><i class="fas fa-info-circle"></i></button>
            </div>
        </header>

        <!-- Messages -->
        <div id="messages" class="flex-1 overflow-y-auto p-4 md:p-8 space-y-4 messages-container">
            <!-- Messages will be loaded here via SSE/Initial Fetch -->
            <div class="text-center py-10 opacity-50 italic text-sm">Loading conversation...</div>
        </div>

        <!-- Input Area -->
        <div class="p-4 md:p-6 bg-white border-t border-gray-100">
            <form id="chatForm" class="flex items-center space-x-3 max-w-5xl mx-auto">
                <button type="button" class="text-gray-400 hover:text-indigo-600 p-2"><i class="fas fa-paperclip text-xl"></i></button>
                <div class="flex-1 relative">
                    <input type="text" id="messageInput" placeholder="Type a message..." 
                           class="w-full pl-4 pr-12 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                    <button type="submit" class="absolute right-2 top-1.5 bg-indigo-600 text-white w-9 h-9 rounded-xl hover:bg-indigo-700 transition-all flex items-center justify-center">
                        <i class="fas fa-paper-plane text-xs"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const messagesDiv = document.getElementById('messages');
const chatForm = document.getElementById('chatForm');
const messageInput = document.getElementById('messageInput');
const currentUserId = <?php echo $_SESSION['user_id']; ?>;
const otherUserId = <?php echo $employer_id ?: 'null'; ?>;
const csrfToken = <?php echo json_encode(csrf_token()); ?>;
let lastId = 0;

if (otherUserId) {
    // 1. Initial Load of History
    fetch(`../includes/get_messages.php?other_id=${otherUserId}`)
        .then(res => res.json())
        .then(data => {
            messagesDiv.innerHTML = '';
            data.forEach(msg => {
                if (msg.id > lastId) lastId = msg.id;
                appendMessage(msg);
            });
            scrollToBottom();
        });

    // 2. Setup SSE for real-time updates
    const evtSource = new EventSource(`../includes/chat_sse.php?other_id=${otherUserId}&last_id=${lastId}`);
    evtSource.onmessage = function(event) {
        const data = JSON.parse(event.data);
        if (data.id > lastId) {
            lastId = data.id;
            appendMessage(data);
            scrollToBottom();
        }
    };
} else {
    messagesDiv.innerHTML = '<div class="text-center py-10 opacity-60 text-sm">No conversation selected. Open a chat from accepted applications or recent messages.</div>';
}

// 3. Send Message
chatForm.onsubmit = async (e) => {
    e.preventDefault();
    const text = messageInput.value.trim();
    if (!text || !otherUserId) return;

    messageInput.value = '';
    const res = await fetch('../includes/send_message.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({
            receiver_id: otherUserId,
            message: text
        })
    });
    
    // UI will update via SSE
};

function appendMessage(msg) {
    const isMe = msg.sender_id == currentUserId;
    const date = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    
    const div = document.createElement('div');
    div.className = `flex ${isMe ? 'justify-end' : 'justify-start'}`;
    div.innerHTML = `
        <div class="max-w-[80%] md:max-w-[70%] ${isMe ? 'bg-indigo-600 text-white rounded-l-2xl rounded-tr-2xl' : 'bg-white text-gray-800 border border-gray-100 rounded-r-2xl rounded-tl-2xl'} p-4 shadow-sm">
            <p class="text-sm leading-relaxed">${escapeHtml(msg.message)}</p>
            <p class="text-[10px] mt-2 ${isMe ? 'text-indigo-200' : 'text-gray-400'} text-right">${date}</p>
        </div>
    `;
    messagesDiv.appendChild(div);
}

function scrollToBottom() {
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

</body>
</html>
