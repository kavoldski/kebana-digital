<?php
/**
 * KEBANA Management System - Chat Interface
 * File: modules/chat/index.php
 */

use App\Helpers\ChatHelper;
use App\Core\Database;

$userId = $_SESSION['user_id'] ?? 0;
$activeChatId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Handle AJAX chat list fetching
if (isset($_GET['fetch_list'])) {
    $chatList = ChatHelper::getChatList($userId);
    foreach ($chatList as $u) {
        $isActive = $u['user_id'] == $activeChatId;
        $roleNames = [888 => 'Super Admin', 1 => 'Presiden', 4 => 'Setiausaha Pusat', 11 => 'Pengerusi Cawangan', 33 => 'Setiausaha Cawangan'];
        $roleName = $roleNames[$u['role']] ?? 'Pegawai';
        
        echo '<a href="/kebana-digital/chat?user_id=' . $u['user_id'] . '" class="flex items-center p-6 border-b border-slate-50 transition-all hover:bg-white group ' . ($isActive ? 'bg-white border-l-4 border-l-kebana-blue shadow-inner' : '') . '">';
        echo '  <div class="w-12 h-12 bg-slate-200 rounded-full flex items-center justify-center text-slate-400 font-black text-xs relative group-hover:bg-kebana-blue group-hover:text-white transition-all">';
        echo '    ' . strtoupper(substr($u['username'], 0, 2));
        if ($u['unread_count'] > 0) {
            echo '    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[8px] w-5 h-5 rounded-full flex items-center justify-center border-2 border-white animate-bounce">' . $u['unread_count'] . '</span>';
        }
        echo '  </div>';
        echo '  <div class="ml-4 flex-1 overflow-hidden">';
        echo '    <div class="flex justify-between items-start">';
        echo '      <p class="text-xs font-black text-kebana-blue uppercase tracking-tight truncate">' . htmlspecialchars($u['username']) . '</p>';
        echo '      <p class="text-[8px] font-bold text-slate-300 uppercase">' . ($u['last_time'] ? date('H:i', strtotime($u['last_time'])) : '') . '</p>';
        echo '    </div>';
        echo '    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">' . $roleName . '</p>';
        echo '    <p class="text-[10px] text-slate-500 truncate mt-2 ' . ($u['unread_count'] > 0 ? 'font-bold text-slate-800' : '') . '">';
        echo '      ' . ($u['last_message'] ? htmlspecialchars($u['last_message']) : '<span class="italic opacity-50">Mula sembang...</span>');
        echo '    </p>';
        echo '  </div>';
        echo '</a>';
    }
    exit;
}

// Handle AJAX message fetching - MUST BE BEFORE HEADER
if (isset($_GET['fetch']) && $activeChatId) {
    $messages = ChatHelper::getConversation($userId, $activeChatId);
    if (empty($messages)) {
        echo '<div class="flex justify-center items-center h-full opacity-20"><i class="fa-solid fa-comments text-8xl"></i></div>';
    } else {
        foreach ($messages as $m) {
            $isMe = $m['sender_id'] == $userId;
            $align = $isMe ? 'justify-end' : 'justify-start';
            $bg = $isMe ? 'bg-kebana-blue text-white' : 'bg-white text-slate-800';
            $radius = $isMe ? 'rounded-l-2xl rounded-tr-2xl' : 'rounded-r-2xl rounded-tl-2xl';
            echo '<div class="flex ' . $align . ' mb-6 animate-fade-in">';
            echo '  <div class="max-w-[75%]">';
            echo '    <div class="' . $bg . ' ' . $radius . ' p-4 shadow-sm border border-slate-100">';
            echo '      <p class="text-xs font-medium leading-relaxed">' . nl2br(htmlspecialchars($m['message'])) . '</p>';
            echo '    </div>';
            echo '    <div class="flex items-center mt-2 ' . ($isMe ? 'justify-end' : 'justify-start') . ' space-x-2">';
            echo '      <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">' . date('H:i', strtotime($m['created_at'])) . '</p>';
            if ($isMe) {
                $tickColor = $m['is_read'] ? 'text-blue-500' : 'text-slate-300';
                echo '      <i class="fa-solid fa-check-double text-[10px] ' . $tickColor . '"></i>';
            }
            echo '    </div>';
            echo '  </div>';
            echo '</div>';
        }
    }
    exit;
}

// Handle message sending via AJAX or fallback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    $to = (int)$_POST['receiver_id'];
    if ($msg && $to) {
        ChatHelper::sendMessage($userId, $to, $msg);
        if (!isset($_GET['ajax'])) {
            header("Location: /kebana-digital/chat?user_id=$to");
            exit;
        }
        echo json_encode(['success' => true]);
        exit;
    }
}

// Handle Clear Chat
if (isset($_GET['action']) && $_GET['action'] === 'clear' && $activeChatId) {
    ChatHelper::clearChat($userId, $activeChatId);
    header("Location: /kebana-digital/chat?user_id=$activeChatId");
    exit;
}

require_once APP_ROOT . '/includes/header.php';

$chatList = ChatHelper::getChatList($userId);
$activeUser = null;

if ($activeChatId) {
    $db = Database::getInstance()->getConnection();
    $res = $db->query("SELECT user_id, username, role FROM tbl_user WHERE user_id = $activeChatId");
    $activeUser = $res ? $res->fetch_assoc() : null;
}

$page_title = 'PUSAT KOMUNIKASI';
?>

<div class="h-[calc(100vh-180px)] flex flex-col md:flex-row bg-white border border-slate-100 shadow-2xl overflow-hidden">
    <!-- Sidebar: User List -->
    <div class="w-full md:w-96 border-r border-slate-100 flex flex-col bg-slate-50/30">
        <div class="p-8 border-b border-slate-100 bg-white">
            <h2 class="text-sm font-black text-kebana-blue uppercase tracking-[0.3em] italic">Mesej Terus</h2>
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Komunikasi Dalaman Organisasi</p>
        </div>
        
        <div id="chat-list" class="flex-1 overflow-y-auto custom-scrollbar">
            <?php foreach ($chatList as $u): 
                $isActive = $u['user_id'] == $activeChatId;
                $roleNames = [
                    888 => 'Super Admin', 1 => 'Presiden', 4 => 'Setiausaha Pusat', 
                    11 => 'Pengerusi Cawangan', 33 => 'Setiausaha Cawangan'
                ];
                $roleName = $roleNames[$u['role']] ?? 'Pegawai';
            ?>
            <a href="/kebana-digital/chat?user_id=<?php echo $u['user_id']; ?>" 
               class="flex items-center p-6 border-b border-slate-50 transition-all hover:bg-white group <?php echo $isActive ? 'bg-white border-l-4 border-l-kebana-blue shadow-inner' : ''; ?>">
                <div class="w-12 h-12 bg-slate-200 rounded-full flex items-center justify-center text-slate-400 font-black text-xs relative group-hover:bg-kebana-blue group-hover:text-white transition-all">
                    <?php echo strtoupper(substr($u['username'], 0, 2)); ?>
                    <?php if ($u['unread_count'] > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[8px] w-5 h-5 rounded-full flex items-center justify-center border-2 border-white animate-bounce">
                            <?php echo $u['unread_count']; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="ml-4 flex-1 overflow-hidden">
                    <div class="flex justify-between items-start">
                        <p class="text-xs font-black text-kebana-blue uppercase tracking-tight truncate"><?php echo htmlspecialchars($u['username']); ?></p>
                        <p class="text-[8px] font-bold text-slate-300 uppercase"><?php echo $u['last_time'] ? date('H:i', strtotime($u['last_time'])) : ''; ?></p>
                    </div>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5"><?php echo $roleName; ?></p>
                    <p class="text-[10px] text-slate-500 truncate mt-2 <?php echo ($u['unread_count'] > 0) ? 'font-bold text-slate-800' : ''; ?>">
                        <?php echo $u['last_message'] ? htmlspecialchars($u['last_message']) : '<span class="italic opacity-50">Mula sembang...</span>'; ?>
                    </p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Chat Window -->
    <div class="flex-1 flex flex-col bg-slate-50/50">
        <?php if ($activeUser): ?>
            <!-- Chat Header -->
            <div class="p-6 bg-white border-b border-slate-100 flex items-center justify-between shadow-sm relative z-10">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-kebana-blue text-white rounded-full flex items-center justify-center text-[10px] font-black">
                        <?php echo strtoupper(substr($activeUser['username'], 0, 2)); ?>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest"><?php echo htmlspecialchars($activeUser['username']); ?></h3>
                        <p class="text-[8px] font-bold text-green-500 uppercase tracking-widest flex items-center">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-2 animate-pulse"></span> Dalam Talian
                        </p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <a href="?user_id=<?php echo $activeChatId; ?>&action=clear" onclick="return confirm('Kosongkan semua sejarah sembang dengan pengguna ini?')" class="text-[9px] font-black text-red-500 uppercase tracking-widest hover:bg-red-50 px-4 py-2 border border-red-100 transition-all">
                        <i class="fa-solid fa-trash-can mr-2"></i> Kosongkan Sembang
                    </a>
                </div>
            </div>

            <!-- Messages Area -->
            <div id="chat-box" class="flex-1 overflow-y-auto p-10 custom-scrollbar scroll-smooth">
                <!-- Messages will be loaded here via AJAX -->
                <div class="flex justify-center items-center h-full opacity-20">
                    <i class="fa-solid fa-comments text-8xl"></i>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-8 bg-white border-t border-slate-100">
                <form id="chat-form" class="relative">
                    <input type="hidden" name="receiver_id" value="<?php echo $activeChatId; ?>">
                    <textarea id="message-input" name="message" rows="1" placeholder="Tulis mesej anda di sini..." 
                              class="w-full bg-slate-50 border-2 border-slate-100 focus:border-kebana-blue focus:bg-white p-5 pr-32 outline-none text-xs font-medium rounded-xl transition-all resize-none overflow-hidden"
                              oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 flex gap-4">
                        <button type="button" class="text-slate-300 hover:text-kebana-blue transition-colors">
                            <i class="fa-solid fa-paperclip text-lg"></i>
                        </button>
                        <button type="submit" class="bg-kebana-blue text-white px-8 py-3 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-kebana-accent transition-all shadow-xl">
                            HANTAR
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Welcome Screen -->
            <div class="flex-1 flex flex-col items-center justify-center p-12 text-center space-y-8">
                <div class="w-32 h-32 bg-slate-100 rounded-full flex items-center justify-center text-slate-300">
                    <i class="fa-solid fa-paper-plane text-5xl"></i>
                </div>
                <div class="max-w-md space-y-4">
                    <h2 class="text-xl font-black text-kebana-blue uppercase tracking-tight italic">Pusat Komunikasi KEBANA</h2>
                    <p class="text-xs font-bold text-slate-400 uppercase leading-loose tracking-widest">
                        Pilih rakan sejawat dari senarai di sebelah kiri untuk memulakan perbincangan strategik atau laporan pantas.
                    </p>
                </div>
                <div class="flex gap-4">
                    <div class="px-6 py-3 bg-white border border-slate-100 text-[8px] font-black uppercase tracking-widest text-slate-400">
                        <i class="fa-solid fa-shield-halved mr-2 text-green-500"></i> Disulitkan (End-to-End)
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatBox = document.getElementById('chat-box');
    const chatList = document.getElementById('chat-list');
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-input');
    const activeUserId = <?php echo $activeChatId; ?>;

    function fetchMessages() {
        if (!activeUserId) return;
        fetch(`/kebana-digital/chat?user_id=${activeUserId}&fetch=1`)
            .then(response => response.text())
            .then(html => {
                if (chatBox.innerHTML !== html) {
                    const isAtBottom = chatBox.scrollHeight - chatBox.scrollTop <= chatBox.clientHeight + 100;
                    chatBox.innerHTML = html;
                    if (isAtBottom) {
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }
                }
            });
    }

    function fetchChatList() {
        fetch(`/kebana-digital/chat?user_id=${activeUserId}&fetch_list=1`)
            .then(response => response.text())
            .then(html => {
                if (chatList.innerHTML !== html) {
                    chatList.innerHTML = html;
                }
            });
    }

    // Initial fetch
    fetchMessages();
    fetchChatList();
    // Poll every 3 seconds
    setInterval(() => {
        fetchMessages();
        fetchChatList();
    }, 3000);

    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;

            const formData = new FormData(chatForm);
            fetch(`/kebana-digital/chat?ajax=1`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    messageInput.style.height = 'auto';
                    fetchMessages();
                }
            });
        });

        // Enter to send
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });
    }
});
</script>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

@keyframes fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.3s ease-out forwards;
}
</style>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
