<?php
require_once '../includes/auth_middleware.php';
require_once '../includes/db_connect.php';
require_once '../includes/csrf.php';
checkRole(['user']);

$current_user_id = $_SESSION['user_id'];
$worker_id = isset($_GET['worker_id']) && is_numeric($_GET['worker_id']) ? (int)$_GET['worker_id'] : null;

$worker_name = "Worker";
$worker_category = "Trade Specialist";

if ($worker_id) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, wp.full_name, c.name as category_name 
        FROM users u 
        LEFT JOIN worker_profiles wp ON u.id = wp.user_id 
        LEFT JOIN categories c ON wp.category_id = c.id
        WHERE u.id = ?
    ");
    $stmt->execute([$worker_id]);
    $worker = $stmt->fetch();
    if ($worker) {
        $worker_name = $worker['full_name'] ?: $worker['username'];
        $worker_category = $worker['category_name'] ?: 'Worker';
    }
} else {
    // Pick the most recent conversation partner
    $stmt = $pdo->prepare("
        SELECT DISTINCT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as other_id
        FROM messages 
        WHERE sender_id = ? OR receiver_id = ? 
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$current_user_id, $current_user_id, $current_user_id]);
    $recent = $stmt->fetch();
    if ($recent && $recent['other_id']) {
        header("Location: chat.php?worker_id=" . $recent['other_id']);
        exit();
    }
}

// Fetch list of workers user has chatted with or hired
$stmtList = $pdo->prepare("
    SELECT DISTINCT u.id, u.username, wp.full_name, c.name as category_name
    FROM users u
    JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
    LEFT JOIN worker_profiles wp ON u.id = wp.user_id
    LEFT JOIN categories c ON wp.category_id = c.id
    WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?
    ORDER BY u.username ASC
");
$stmtList->execute([$current_user_id, $current_user_id, $current_user_id]);
$chat_list = $stmtList->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($worker_name); ?> - Work Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .chat-scroll::-webkit-scrollbar { width: 5px; }
        .chat-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 font-sans">

<div class="flex h-screen overflow-hidden">
    <!-- Main User Sidebar -->
    <?php include '../includes/user_sidebar.php'; ?>

    <!-- Secondary Conversation List Sidebar (Desktop) -->
    <div class="w-72 bg-white border-r border-slate-200 hidden lg:flex flex-col">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm">Conversations</h3>
            <a href="search_workers.php" class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold">+ New</a>
        </div>
        <div class="flex-1 overflow-y-auto p-3 space-y-1 chat-scroll">
            <?php if (empty($chat_list)): ?>
                <div class="text-center py-12 text-slate-400 text-xs">
                    No active chats yet. Find a worker to start chatting!
                </div>
            <?php else: ?>
                <?php foreach ($chat_list as $item): ?>
                    <a href="chat.php?worker_id=<?php echo $item['id']; ?>" 
                       class="flex items-center space-x-3 p-3 rounded-2xl transition-all <?php echo ($worker_id == $item['id']) ? 'bg-indigo-50 border border-indigo-100' : 'hover:bg-slate-50'; ?>">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-sm">
                            <?php echo strtoupper(substr($item['username'], 0, 1)); ?>
                        </div>
                        <div class="truncate flex-1">
                            <p class="font-bold text-slate-800 text-xs truncate"><?php echo htmlspecialchars($item['full_name'] ?: $item['username']); ?></p>
                            <p class="text-[10px] text-slate-400 truncate"><?php echo htmlspecialchars($item['category_name'] ?: 'Worker'); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Chat Window -->
    <div class="flex-1 flex flex-col bg-slate-50">
        <!-- Chat Header -->
        <header class="bg-white border-b border-slate-200/80 p-4 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-700 text-xl mr-1">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-bold flex items-center justify-center text-sm shadow-md shadow-indigo-600/20">
                    <?php echo strtoupper(substr($worker_name, 0, 1)); ?>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm md:text-base"><?php echo htmlspecialchars($worker_name); ?></h3>
                    <p class="text-[11px] text-indigo-600 font-semibold"><?php echo htmlspecialchars($worker_category); ?> &bull; <span class="text-emerald-500 font-bold">Online</span></p>
                </div>
            </div>

            <?php if($worker_id): ?>
                <a href="worker_profile.php?id=<?php echo $worker_id; ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-colors">
                    View Profile
                </a>
            <?php endif; ?>
        </header>

        <!-- Messages Feed Area -->
        <div id="chatMessages" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-3 chat-scroll">
            <div class="text-center py-10 text-slate-400 text-xs italic">
                Loading messages...
            </div>
        </div>

        <!-- Chat Input Bar -->
        <div class="p-4 bg-white border-t border-slate-200">
            <form id="chatForm" class="max-w-4xl mx-auto flex items-center space-x-3">
                <input type="text" id="messageInput" placeholder="Write a message to <?php echo htmlspecialchars($worker_name); ?>..." required
                       class="flex-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
                <button type="submit" class="w-11 h-11 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl flex items-center justify-center transition-all shadow-md shadow-indigo-600/20">
                    <i class="fas fa-paper-plane text-xs"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
const chatMessages = document.getElementById('chatMessages');
const chatForm = document.getElementById('chatForm');
const messageInput = document.getElementById('messageInput');
const currentUserId = <?php echo json_encode($current_user_id); ?>;
const targetWorkerId = <?php echo json_encode($worker_id); ?>;
const csrfToken = <?php echo json_encode(csrf_token()); ?>;
let lastId = 0;

if (targetWorkerId) {
    async function loadHistory() {
        try {
            const res = await fetch(`../includes/get_messages.php?other_id=${targetWorkerId}`);
            const data = await res.json();
            chatMessages.innerHTML = '';
            if (data.length === 0) {
                chatMessages.innerHTML = '<div class="text-center py-10 text-slate-400 text-xs italic">No messages yet. Send a message to start conversation!</div>';
            } else {
                data.forEach(msg => {
                    if (msg.id > lastId) lastId = msg.id;
                    appendMessage(msg);
                });
                scrollToBottom();
            }
        } catch (e) {
            console.error(e);
        }
    }

    function startSSE() {
        const evtSource = new EventSource(`../includes/chat_sse.php?other_id=${targetWorkerId}&last_id=${lastId}`);
        evtSource.onmessage = function(e) {
            const data = JSON.parse(e.data);
            if (data.id > lastId) {
                lastId = data.id;
                if (chatMessages.querySelector('.italic')) {
                    chatMessages.innerHTML = '';
                }
                appendMessage(data);
                scrollToBottom();
            }
        };
        evtSource.onerror = function() {
            evtSource.close();
            setTimeout(startSSE, 3000);
        };
    }

    function appendMessage(msg) {
        const isMe = msg.sender_id == currentUserId;
        const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const div = document.createElement('div');
        div.className = `flex ${isMe ? 'justify-end' : 'justify-start'}`;
        div.innerHTML = `
            <div class="max-w-[80%] md:max-w-[65%] p-3.5 rounded-2xl text-xs leading-relaxed ${isMe ? 'bg-indigo-600 text-white rounded-br-none shadow-md shadow-indigo-600/20' : 'bg-white text-slate-800 border border-slate-200 rounded-bl-none shadow-sm'}">
                <p>${escapeHtml(msg.message)}</p>
                <p class="text-[9px] mt-1.5 opacity-70 text-right">${time}</p>
            </div>
        `;
        chatMessages.appendChild(div);
    }

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    chatForm.onsubmit = async (e) => {
        e.preventDefault();
        const text = messageInput.value.trim();
        if (!text) return;
        messageInput.value = '';

        try {
            await fetch('../includes/send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    receiver_id: targetWorkerId,
                    message: text
                })
            });
        } catch (err) {
            console.error(err);
        }
    };

    loadHistory();
    startSSE();
} else {
    chatMessages.innerHTML = '<div class="text-center py-16 text-slate-400 text-xs">Select or search for a worker to start a conversation.</div>';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
</body>
</html>
