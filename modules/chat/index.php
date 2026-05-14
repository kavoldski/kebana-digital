<?php
/**
 * KEBANA Digital Management System - Chat Interface
 * File: modules/chat/index.php
 */

use App\Helpers\ChatHelper;
use App\Core\Database;

$userId = $_SESSION['user_id'] ?? 0;
$activeChatId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Handle AJAX chat list fetching
if (isset($_GET['fetch_list'])) {
    $chatList = ChatHelper::getChatList($userId);
    $html = '';
    foreach ($chatList as $u) {
        $isActive = $u['user_id'] == $activeChatId;
        $roleNames = [888 => 'Super Admin', 1 => 'Presiden', 4 => 'Setiausaha Pusat', 11 => 'Pengerusi Cawangan', 33 => 'Setiausaha Cawangan'];
        $roleName = $roleNames[$u['role']] ?? 'Pegawai';
        
        $html .= '<a href="/kebana-digital/chat?user_id=' . $u['user_id'] . '" class="chat-item flex items-center p-5 border-b border-slate-50 transition-all hover:bg-white group ' . ($isActive ? 'bg-white border-l-4 border-l-kebana-blue' : '') . '">';
        $html .= '  <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 font-bold text-[10px] relative group-hover:bg-kebana-blue group-hover:text-white transition-all">';
        $html .= '    ' . strtoupper(substr($u['username'], 0, 2));
        if ($u['unread_count'] > 0) {
            $html .= '    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[8px] w-4 h-4 rounded-full flex items-center justify-center border-2 border-white">' . $u['unread_count'] . '</span>';
        }
        $html .= '  </div>';
        $html .= '  <div class="ml-4 flex-1 overflow-hidden">';
        $html .= '    <div class="flex justify-between items-start">';
        $html .= '      <p class="text-[10px] font-bold text-kebana-blue uppercase tracking-tight truncate">' . htmlspecialchars($u['username']) . '</p>';
        $html .= '      <p class="text-[8px] font-medium text-slate-300 uppercase">' . ($u['last_time'] ? date('H:i', strtotime($u['last_time'])) : '') . '</p>';
        $html .= '    </div>';
        $html .= '    <p class="text-[9px] font-medium text-slate-400 uppercase tracking-widest mt-0.5">' . $roleName . '</p>';
        $html .= '    <p class="text-[10px] text-slate-500 truncate mt-1 ' . ($u['unread_count'] > 0 ? 'font-bold text-slate-800' : 'opacity-70') . '">';
        $html .= '      ' . ($u['last_message'] ? htmlspecialchars($u['last_message']) : '<span class="italic opacity-30 text-[9px]">Tiada mesej</span>');
        $html .= '    </p>';
        $html .= '  </div>';
        $html .= '</a>';
    }
    echo json_encode(['html' => $html, 'unread_total' => ChatHelper::getTotalUnreadCount($userId)]);
    exit;
}

// Handle AJAX state check
if (isset($_GET['check_update']) && $activeChatId) {
    echo json_encode(ChatHelper::getConversationState($userId, $activeChatId));
    exit;
}

// Handle AJAX message fetching
if (isset($_GET['fetch']) && $activeChatId) {
    $messages = ChatHelper::getConversation($userId, $activeChatId);
    $html = '';
    if (empty($messages)) {
        $html = '<div class="flex flex-col justify-center items-center h-full opacity-10 space-y-4">
                    <i class="fa-solid fa-comments text-7xl"></i>
                    <p class="text-[10px] font-bold uppercase tracking-widest">Tiada sejarah sembang</p>
                 </div>';
    } else {
        foreach ($messages as $m) {
            $isMe = $m['sender_id'] == $userId;
            $align = $isMe ? 'justify-end' : 'justify-start';
            $bg = $isMe ? 'bg-kebana-blue text-white' : 'bg-white text-slate-700';
            $radius = $isMe ? 'rounded-2xl rounded-tr-none' : 'rounded-2xl rounded-tl-none';
            $shadow = $isMe ? 'shadow-blue-900/10' : 'shadow-slate-200/50';
            $html .= '<div class="flex ' . $align . ' mb-4 group" data-msg-id="' . $m['chat_id'] . '">';
            $html .= '  <div class="max-w-[80%]">';
            $html .= '    <div class="' . $bg . ' ' . $radius . ' p-4 shadow-sm border border-slate-100/50 ' . $shadow . '">';
            $html .= '      <p class="text-[11px] font-medium leading-relaxed">' . nl2br(htmlspecialchars($m['message'])) . '</p>';
            $html .= '    </div>';
            $html .= '    <div class="flex items-center mt-1.5 ' . ($isMe ? 'justify-end' : 'justify-start') . ' space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">';
            $html .= '      <p class="text-[8px] font-bold text-slate-300 uppercase tracking-widest">' . date('H:i', strtotime($m['created_at'])) . '</p>';
            if ($isMe) {
                $tickColor = $m['is_read'] ? 'text-blue-400' : 'text-slate-200';
                $html .= '      <i class="fa-solid fa-check-double text-[8px] ' . $tickColor . '"></i>';
            }
            $html .= '    </div>';
            $html .= '  </div>';
            $html .= '</div>';
        }
    }
    echo json_encode(['html' => $html, 'msg_count' => count($messages), 'last_id' => end($messages)['chat_id'] ?? 0]);
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

$chatList = ChatHelper::getChatList($userId, false);
$allUsers = ChatHelper::getAllUsers($userId);
$activeUser = null;

if ($activeChatId) {
    $db = Database::getInstance()->getConnection();
    $res = $db->query("SELECT user_id, username, role FROM tbl_user WHERE user_id = $activeChatId");
    $activeUser = $res ? $res->fetch_assoc() : null;
}

$page_title = 'PUSAT KOMUNIKASI';
?>

<div class="h-[calc(100vh-180px)] flex flex-col md:flex-row bg-white border border-slate-100 shadow-xl overflow-hidden rounded-2xl">
    <!-- Sidebar: User List -->
    <div class="w-full md:w-80 border-r border-slate-100 flex flex-col bg-slate-50/20">
        <div class="p-6 border-b border-slate-100 bg-white">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="text-[10px] font-black text-kebana-blue uppercase tracking-[0.2em]">Sembang</h2>
                    <p class="text-[8px] font-bold text-slate-300 uppercase tracking-widest mt-0.5">Komunikasi Pasukan</p>
                </div>
            </div>
            <!-- Sidebar Search -->
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-[9px]"></i>
                <input type="text" id="sidebarSearch" placeholder="CARI NAMA..." 
                       class="w-full bg-slate-50 border border-slate-100 pl-10 pr-4 py-2.5 text-[9px] font-bold uppercase tracking-widest outline-none focus:border-kebana-blue/30 focus:bg-white transition-all rounded-lg"
                       onkeyup="filterSidebarUsers()">
            </div>
        </div>
        
        <div id="chat-list" class="flex-1 overflow-y-auto custom-scrollbar">
            <?php foreach ($chatList as $u): 
                $isActive = $u['user_id'] == $activeChatId;
                $roleNames = [888 => 'Super Admin', 1 => 'Presiden', 4 => 'Setiausaha Pusat', 11 => 'Pengerusi Cawangan', 33 => 'Setiausaha Cawangan'];
                $roleName = $roleNames[$u['role']] ?? 'Pegawai';
            ?>
            <a href="/kebana-digital/chat?user_id=<?php echo $u['user_id']; ?>" 
               class="chat-item flex items-center p-5 border-b border-slate-50 transition-all hover:bg-white group <?php echo $isActive ? 'bg-white border-l-4 border-l-kebana-blue' : ''; ?>">
                <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 font-bold text-[10px] relative group-hover:bg-kebana-blue group-hover:text-white transition-all">
                    <?php echo strtoupper(substr($u['username'], 0, 2)); ?>
                    <?php if ($u['unread_count'] > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[8px] w-4 h-4 rounded-full flex items-center justify-center border-2 border-white">
                            <?php echo $u['unread_count']; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="ml-4 flex-1 overflow-hidden">
                    <div class="flex justify-between items-start">
                        <p class="text-[10px] font-bold text-kebana-blue uppercase tracking-tight truncate"><?php echo htmlspecialchars($u['username']); ?></p>
                        <p class="text-[8px] font-medium text-slate-300 uppercase"><?php echo $u['last_time'] ? date('H:i', strtotime($u['last_time'])) : ''; ?></p>
                    </div>
                    <p class="text-[9px] font-medium text-slate-400 uppercase tracking-widest mt-0.5"><?php echo $roleName; ?></p>
                    <p class="text-[10px] text-slate-500 truncate mt-1 <?php echo ($u['unread_count'] > 0) ? 'font-bold text-slate-800' : 'opacity-70'; ?>">
                        <?php echo $u['last_message'] ? htmlspecialchars($u['last_message']) : '<span class="italic opacity-30 text-[9px]">Tiada mesej</span>'; ?>
                    </p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Chat Window -->
    <div class="flex-1 flex flex-col bg-slate-50/30">
        <?php if ($activeUser): ?>
            <!-- Chat Header -->
            <div class="px-8 py-5 bg-white border-b border-slate-100 flex items-center justify-between shadow-sm relative z-10">
                <div class="flex items-center">
                    <div class="w-9 h-9 bg-kebana-blue text-white rounded-full flex items-center justify-center text-[10px] font-bold">
                        <?php echo strtoupper(substr($activeUser['username'], 0, 2)); ?>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-[10px] font-bold text-kebana-blue uppercase tracking-widest"><?php echo htmlspecialchars($activeUser['username']); ?></h3>
                        <p class="text-[8px] font-bold text-green-500/60 uppercase tracking-widest flex items-center mt-0.5">
                            <span class="w-1 h-1 bg-green-500 rounded-full mr-2"></span> Aktif
                        </p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <a href="?user_id=<?php echo $activeChatId; ?>&action=clear" onclick="return confirm('Kosongkan semua sejarah sembang dengan pengguna ini?')" 
                       class="text-[8px] font-bold text-slate-300 uppercase tracking-widest hover:text-red-500 px-3 py-1.5 border border-slate-100 hover:border-red-100 transition-all rounded">
                        <i class="fa-solid fa-trash-can mr-2"></i> Padam Sembang
                    </a>
                </div>
            </div>

            <!-- Messages Area -->
            <div id="chat-box" class="flex-1 overflow-y-auto p-8 custom-scrollbar scroll-smooth">
                <div class="flex justify-center items-center h-full opacity-5">
                    <i class="fa-solid fa-comments text-6xl"></i>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-6 bg-white border-t border-slate-100">
                <form id="chat-form" class="relative flex items-end gap-4">
                    <input type="hidden" name="receiver_id" value="<?php echo $activeChatId; ?>">
                    <div class="flex-1 relative">
                        <textarea id="message-input" name="message" rows="1" placeholder="Mesej anda..." 
                                  class="w-full bg-slate-50 border border-slate-200 focus:border-kebana-blue/30 focus:bg-white p-4 pr-12 outline-none text-[11px] font-medium rounded-xl transition-all resize-none overflow-hidden"
                                  oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                    </div>
                    <button type="submit" class="bg-kebana-blue text-white w-12 h-12 rounded-xl flex items-center justify-center hover:bg-kebana-accent transition-all shadow-lg shadow-blue-900/10">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                    </button>
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
    
    let lastMsgId = 0;
    let msgCount = 0;
    let isFetching = false;

    function fetchMessages(force = false) {
        if (!activeUserId || isFetching) return;
        
        // If not forced, check for updates first
        if (!force) {
            fetch(`/kebana-digital/chat?user_id=${activeUserId}&check_update=1`)
                .then(res => res.json())
                .then(state => {
                    if (state.last_id != lastMsgId || state.msg_count != msgCount) {
                        performFetch();
                    }
                });
        } else {
            performFetch();
        }
    }

    function performFetch(force = false) {
        isFetching = true;
        fetch(`/kebana-digital/chat?user_id=${activeUserId}&fetch=1`)
            .then(response => response.json())
            .then(data => {
                const isAtBottom = chatBox.scrollHeight - chatBox.scrollTop <= chatBox.clientHeight + 100;
                
                // If it's a completely new content (e.g. first load) or different message count
                if (chatBox.innerHTML.length < 500 || data.msg_count !== msgCount || force) {
                    chatBox.innerHTML = data.html;
                    if (isAtBottom || force) {
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }
                }
                
                lastMsgId = data.last_id;
                msgCount = data.msg_count;
                isFetching = false;
            })
            .catch(() => isFetching = false);
    }

    function fetchChatList() {
        fetch(`/kebana-digital/chat?user_id=${activeUserId}&fetch_list=1`)
            .then(response => response.json())
            .then(data => {
                if (chatList.innerHTML !== data.html) {
                    chatList.innerHTML = data.html;
                    filterSidebarUsers(); 
                }
                
                // Update global badge if exists
                const mainBadge = document.querySelector('a[href*="chat"] .bg-red-500');
                if (mainBadge) {
                    if (data.unread_total > 0) {
                        mainBadge.textContent = data.unread_total;
                        mainBadge.classList.remove('hidden');
                    } else {
                        mainBadge.classList.add('hidden');
                    }
                }
            });
    }

    window.filterSidebarUsers = function() {
        const input = document.getElementById('sidebarSearch');
        if (!input) return;
        const filter = input.value.toUpperCase();
        const items = chatList.getElementsByClassName('chat-item');
        
        for (let i = 0; i < items.length; i++) {
            const text = items[i].textContent || items[i].innerText;
            if (text.toUpperCase().indexOf(filter) > -1) {
                items[i].style.display = "";
            } else {
                items[i].style.display = "none";
            }
        }
    }

    // Initial fetch
    if (activeUserId) performFetch();
    fetchChatList();

    // Poll
    setInterval(() => {
        fetchMessages();
        fetchChatList();
    }, 4000);

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
                    performFetch();
                }
            });
        });

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
    width: 3px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #f1f5f9;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #e2e8f0;
}

@keyframes msg-slide-up {
    from { opacity: 0; transform: translateY(8px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.message-new {
    animation: msg-slide-up 0.4s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

/* Glassmorphism for the input area if desired, but keeping it light for now */
#chat-box {
    scroll-behavior: smooth;
}
</style>


<?php require_once APP_ROOT . '/includes/footer.php'; ?>
