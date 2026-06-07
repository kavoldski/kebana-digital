<?php
/**
 * KEBANA Digital Management System - Global Floating Chat Widget
 * File: includes/chat_widget.php
 */

if (!isset($_SESSION['user_id'])) {
    return;
}

$currentUserId = $_SESSION['user_id'];
?>
<!-- Floating Chat Widget Styles -->
<style>
    .chat-widget-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .chat-widget-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .chat-widget-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .chat-widget-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    .chat-widget-open {
        opacity: 1 !important;
        transform: scale(1) translateY(0) !important;
        pointer-events: auto !important;
    }
    /* Style overrides for list items in the widget to fit the smaller panel */
    #chat-widget-users-container .chat-item {
        padding: 0.75rem 1rem !important;
    }
    #chat-widget-users-container .chat-item .w-10 {
        width: 2rem !important;
        height: 2rem !important;
        font-size: 0.7rem !important;
    }
    #chat-widget-users-container .chat-item .ml-4 {
        margin-left: 0.75rem !important;
    }
    #chat-widget-users-container .chat-item p {
        font-size: 0.7rem !important;
    }
    #chat-widget-users-container .chat-item .text-xs {
        font-size: 0.75rem !important;
    }
    /* Style overrides for messages in the widget */
    #chat-widget-messages-container .p-4 {
        padding: 0.6rem 0.8rem !important;
        font-size: 0.75rem !important;
    }
    #chat-widget-messages-container .text-sm {
        font-size: 0.75rem !important;
    }
    #chat-widget-messages-container .mb-4 {
        margin-bottom: 0.5rem !important;
    }
</style>

<!-- Floating Chat Button (FAB) -->
<div id="chat-widget-button" class="fixed bottom-6 right-6 z-50 flex items-center justify-center w-14 h-14 bg-kebana-blue hover:bg-kebana-accent text-white rounded-full shadow-2xl cursor-pointer hover:scale-105 active:scale-95 transition-all duration-200">
    <i class="fa-solid fa-comments text-xl"></i>
    <span id="chat-widget-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-extrabold w-5 h-5 rounded-full flex items-center justify-center border-2 border-white hidden"></span>
</div>

<!-- Chat Widget Panel Window -->
<div id="chat-widget-window" class="fixed bottom-24 right-6 z-50 w-96 h-[500px] max-w-[calc(100vw-2rem)] bg-white rounded-2xl shadow-2xl border border-slate-200 flex flex-col overflow-hidden transition-all duration-300 transform scale-95 opacity-0 translate-y-4 pointer-events-none">
    
    <!-- Widget Header -->
    <div class="flex items-center justify-between px-4 py-3 bg-kebana-blue text-white font-black text-xs uppercase tracking-widest border-b border-white/10 relative shadow-md shrink-0">
        <div class="flex items-center min-w-0">
            <button id="chat-widget-back" class="mr-2 hover:text-kebana-yellow transition-colors hidden focus:outline-none">
                <i class="fa-solid fa-chevron-left text-sm"></i>
            </button>
            <span id="chat-widget-title" class="truncate font-black">Sembang KEBANA</span>
        </div>
        <button id="chat-widget-close" class="hover:text-kebana-yellow transition-colors focus:outline-none ml-2 shrink-0">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    </div>

    <!-- User List View -->
    <div id="chat-widget-list-view" class="flex-1 overflow-y-auto chat-widget-scrollbar bg-slate-50 flex flex-col">
        <div class="p-3 bg-white border-b border-slate-200 sticky top-0 z-10">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                <input type="text" id="chat-widget-search" placeholder="Cari nama..." 
                       class="w-full bg-slate-50 border border-slate-200 pl-8 pr-3 py-1.5 text-[10px] font-bold uppercase tracking-widest outline-none focus:border-kebana-blue/30 focus:bg-white transition-all rounded-md">
            </div>
        </div>
        <div id="chat-widget-users-container" class="divide-y divide-slate-100 flex-1">
            <!-- Populated via AJAX -->
            <div class="p-8 text-center text-slate-400 opacity-60">
                <i class="fa-solid fa-circle-notch animate-spin text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Chat Message View -->
    <div id="chat-widget-message-view" class="flex-1 overflow-y-auto chat-widget-scrollbar p-4 bg-slate-50 flex flex-col hidden">
        <div id="chat-widget-messages-container" class="space-y-4">
            <!-- Messages populated via AJAX -->
        </div>
    </div>

    <!-- Chat Input View -->
    <div id="chat-widget-input-view" class="p-3 bg-white border-t border-slate-200 hidden shrink-0">
        <form id="chat-widget-form" class="flex items-end gap-2">
            <input type="hidden" id="chat-widget-receiver-id" name="receiver_id">
            <div class="flex-1 relative">
                <textarea id="chat-widget-input" name="message" rows="1" placeholder="Mesej anda..." 
                          class="w-full bg-slate-50 border border-slate-250 focus:border-kebana-blue/30 focus:bg-white p-2.5 pr-8 outline-none text-xs font-medium rounded-lg transition-all resize-none overflow-hidden max-h-24"
                          oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
            </div>
            <button type="submit" class="bg-kebana-blue text-white w-9 h-9 rounded-lg flex items-center justify-center hover:bg-kebana-accent transition-all shrink-0">
                <i class="fa-solid fa-paper-plane text-xs"></i>
            </button>
        </form>
    </div>

</div>

<!-- Floating Chat Widget JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fab = document.getElementById('chat-widget-button');
    const badge = document.getElementById('chat-widget-badge');
    const windowEl = document.getElementById('chat-widget-window');
    const closeBtn = document.getElementById('chat-widget-close');
    const backBtn = document.getElementById('chat-widget-back');
    const title = document.getElementById('chat-widget-title');
    
    const listView = document.getElementById('chat-widget-list-view');
    const searchInput = document.getElementById('chat-widget-search');
    const usersContainer = document.getElementById('chat-widget-users-container');
    
    const messageView = document.getElementById('chat-widget-message-view');
    const messagesContainer = document.getElementById('chat-widget-messages-container');
    
    const inputView = document.getElementById('chat-widget-input-view');
    const chatForm = document.getElementById('chat-widget-form');
    const messageInput = document.getElementById('chat-widget-input');
    const receiverInput = document.getElementById('chat-widget-receiver-id');
    
    let activeChatId = 0;
    let lastMsgId = 0;
    let msgCount = 0;
    let isFetching = false;
    let isOpen = false;

    // Toggle widget window
    fab.addEventListener('click', function() {
        isOpen = !isOpen;
        if (isOpen) {
            windowEl.classList.add('chat-widget-open');
            fetchChatListWidget();
        } else {
            windowEl.classList.remove('chat-widget-open');
        }
    });

    closeBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        isOpen = false;
        windowEl.classList.remove('chat-widget-open');
    });

    // Back button behavior (return to user list)
    backBtn.addEventListener('click', function() {
        activeChatId = 0;
        lastMsgId = 0;
        msgCount = 0;
        
        backBtn.classList.add('hidden');
        title.innerHTML = 'Sembang KEBANA';
        
        messageView.classList.add('hidden');
        inputView.classList.add('hidden');
        listView.classList.remove('hidden');
        
        fetchChatListWidget();
    });

    // Search filter
    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toUpperCase();
        const items = usersContainer.getElementsByClassName('chat-item');
        for (let i = 0; i < items.length; i++) {
            const text = items[i].textContent || items[i].innerText;
            if (text.toUpperCase().indexOf(filter) > -1) {
                items[i].style.display = "";
            } else {
                items[i].style.display = "none";
            }
        }
    });

    // Listen to user selection in list
    usersContainer.addEventListener('click', function(e) {
        const chatItem = e.target.closest('.chat-item');
        if (chatItem) {
            e.preventDefault();
            const href = chatItem.getAttribute('href');
            // Get user ID from href attribute using simple Regex or URL API
            const match = href.match(/user_id=(\d+)/);
            if (match) {
                const userId = parseInt(match[1]);
                const nameEl = chatItem.querySelector('.text-kebana-blue');
                const username = nameEl ? nameEl.textContent.trim() : 'Sembang';
                openWidgetChat(userId, username);
            }
        }
    });

    // Load conversation with a user
    function openWidgetChat(userId, username) {
        activeChatId = userId;
        receiverInput.value = userId;
        lastMsgId = 0;
        msgCount = 0;
        
        title.innerHTML = `<span class="flex items-center min-w-0"><i class="fa-solid fa-circle text-[7px] text-green-500 mr-2 animate-pulse shrink-0"></i><span class="truncate">${username}</span></span>`;
        backBtn.classList.remove('hidden');
        
        listView.classList.add('hidden');
        messageView.classList.remove('hidden');
        inputView.classList.remove('hidden');
        
        messagesContainer.innerHTML = '<div class="p-8 text-center text-slate-400 opacity-60"><i class="fa-solid fa-circle-notch animate-spin text-xl"></i></div>';
        
        performFetchWidget(true);
    }

    // Fetch message history for active chat
    function fetchMessagesWidget(force = false) {
        if (!activeChatId || isFetching || !isOpen) return;
        
        if (!force) {
            fetch(`<?= URL_ROOT ?>/chat?user_id=${activeChatId}&check_update=1`)
                .then(res => res.json())
                .then(state => {
                    if (state.last_id != lastMsgId || state.msg_count != msgCount) {
                        performFetchWidget();
                    }
                });
        } else {
            performFetchWidget();
        }
    }

    function performFetchWidget(force = false) {
        if (!activeChatId) return;
        isFetching = true;
        
        fetch(`<?= URL_ROOT ?>/chat?user_id=${activeChatId}&fetch=1`)
            .then(res => res.json())
            .then(data => {
                const isAtBottom = messageView.scrollHeight - messageView.scrollTop <= messageView.clientHeight + 100;
                
                if (messagesContainer.innerHTML.length < 200 || data.msg_count !== msgCount || force) {
                    messagesContainer.innerHTML = data.html;
                    if (isAtBottom || force) {
                        messageView.scrollTop = messageView.scrollHeight;
                    }
                }
                
                lastMsgId = data.last_id;
                msgCount = data.msg_count;
                isFetching = false;
            })
            .catch(() => isFetching = false);
    }

    // Fetch conversation lists and global unread count
    function fetchChatListWidget() {
        // Only fetch list if open (unless it's just updating the global badge)
        const activeParam = activeChatId ? `&user_id=${activeChatId}` : '';
        fetch(`<?= URL_ROOT ?>/chat?fetch_list=1${activeParam}`)
            .then(res => res.json())
            .then(data => {
                // Update Badge on FAB
                if (data.unread_total > 0) {
                    badge.textContent = data.unread_total;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }

                // If widget is open and on the list view, update list HTML
                if (isOpen && activeChatId === 0) {
                    if (usersContainer.innerHTML !== data.html) {
                        usersContainer.innerHTML = data.html;
                        // Trigger search filter to maintain user search state
                        const filter = searchInput.value.toUpperCase();
                        const items = usersContainer.getElementsByClassName('chat-item');
                        for (let i = 0; i < items.length; i++) {
                            const text = items[i].textContent || items[i].innerText;
                            if (text.toUpperCase().indexOf(filter) > -1) {
                                items[i].style.display = "";
                            } else {
                                items[i].style.display = "none";
                            }
                        }
                    }
                }
            });
    }

    // Submit new message inside widget
    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message || !activeChatId) return;

            const formData = new FormData(chatForm);
            fetch(`<?= URL_ROOT ?>/chat?ajax=1`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    messageInput.style.height = 'auto';
                    performFetchWidget(true);
                }
            });
        });

        // Submit message on enter key press
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });
    }

    // Initial check for unread count
    fetchChatListWidget();

    // Poll for new updates every 4 seconds
    setInterval(() => {
        if (isOpen) {
            if (activeChatId) {
                fetchMessagesWidget();
            } else {
                fetchChatListWidget();
            }
        } else {
            // Keep fetching unread counts periodically to update the FAB badge
            fetchChatListWidget();
        }
    }, 4000);
});
</script>
