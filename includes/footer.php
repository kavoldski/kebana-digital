<?php
/**
 * KEBANA Management System - Footer Component with Tailwind
 * File: includes/footer.php
 */
?>
            </div><!-- End Page Content Container -->
            
            <footer class="mt-auto py-8 bg-white border-t border-slate-200">
                <div class="px-4 lg:px-8 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <p class="text-sm text-slate-500">
                        &copy; <?php echo date('Y'); ?> KEBANA Management System.
                    </p>
                    <div class="flex space-x-6 text-sm text-slate-500">
                        <a href="#" class="hover:text-primary-600 transition-colors">Privacy Policy</a>
                        <a href="#" class="hover:text-primary-600 transition-colors">Terms of Service</a>
                        <span class="text-slate-300">v1.1.0-tailwind</span>
                    </div>
                </div>
            </footer>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const mobileToggle = document.getElementById('mobileToggle');
            
            function toggleSidebar() {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
                document.body.classList.toggle('overflow-hidden');
            }

            if (mobileToggle) {
                mobileToggle.addEventListener('click', toggleSidebar);
            }
            
            if (overlay) {
                overlay.addEventListener('click', toggleSidebar);
            }

            // Submenu logic (if any)
            document.querySelectorAll('.has-submenu').forEach(item => {
                item.addEventListener('click', (e) => {
                    // Implementation for multi-level menu if needed
                });
            });
            // Notifications Logic
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationMenu = document.getElementById('notificationMenu');
            const notificationBadge = document.getElementById('notificationBadge');
            const notificationList = document.getElementById('notificationList');

            if (notificationBtn) {
                notificationBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    notificationMenu.classList.toggle('hidden');
                });
                
                document.addEventListener('click', (e) => {
                    if (!notificationMenu.contains(e.target) && !notificationBtn.contains(e.target)) {
                        notificationMenu.classList.add('hidden');
                    }
                });
            }

            // Real-time via SSE
            if (!!window.EventSource && notificationBtn) {
                // Fetch immediately on load so we don't wait for SSE
                fetchInitial();
                
                const source = new EventSource('<?php echo URL_ROOT; ?>/modules/api/notifications_stream.php');
                console.log("SSE: Connecting to notifications stream...");

                source.onmessage = function(event) {
                    try {
                        const data = JSON.parse(event.data);
                        console.log("SSE: Received update", data);
                        updateNotificationUI(data);
                    } catch (e) {
                        console.error("Error parsing SSE data:", e);
                    }
                };
                
                source.onerror = function(err) {
                    console.error("SSE: Connection failed or timed out", err);
                };
            }

            function updateNotificationUI(data) {
                const { count, notifications } = data;
                
                // Update badge
                if (count > 0) {
                    notificationBadge.innerText = count > 9 ? '9+' : count;
                    notificationBadge.classList.remove('hidden');
                } else {
                    notificationBadge.classList.add('hidden');
                }

                // Update list
                if (notifications && notifications.length > 0) {
                    notificationList.innerHTML = notifications.map(notif => `
                        <div class="p-4 border-b border-slate-50 hover:bg-slate-50 transition-colors cursor-pointer ${notif.status === 'unread' ? 'bg-blue-50/30' : ''}" onclick="markRead(${notif.notification_id}, '${notif.action_url}')">
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-[10px] font-black text-kebana-blue uppercase tracking-widest">${notif.type.replace('_', ' ')}</span>
                                <span class="text-[9px] text-slate-400 font-bold uppercase">${formatTime(notif.created_at)}</span>
                            </div>
                            <h4 class="text-xs font-black text-slate-800 leading-tight">${notif.title}</h4>
                            <p class="text-[11px] text-slate-500 mt-1 line-clamp-2">${notif.message}</p>
                        </div>
                    `).join('');
                } else {
                    notificationList.innerHTML = `
                        <div class="p-8 text-center text-slate-300">
                            <i class="fa-regular fa-bell-slash text-2xl mb-2 block"></i>
                            <p class="text-[10px] font-bold uppercase">No new notifications</p>
                        </div>
                    `;
                }
            }

            window.markRead = function(id, url) {
                fetch(`<?php echo URL_ROOT; ?>/modules/api/notifications.php?action=mark_as_read&id=${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && url && url !== 'null' && url !== '') {
                            window.location.href = '<?php echo URL_ROOT; ?>/' + url;
                        } else {
                            // Just refresh count if no URL
                            fetchInitial();
                        }
                    });
            };

            window.markAllRead = function() {
                fetch(`<?php echo URL_ROOT; ?>/modules/api/notifications.php?action=mark_all_read`, { 
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'mark_all_read' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        notificationBadge.classList.add('hidden');
                        // Optimistic UI: Mark all notifications in the list as read
                        document.querySelectorAll('#notificationList > div').forEach(el => {
                            el.classList.remove('bg-blue-50/30');
                        });
                    }
                });
            };

            function fetchInitial() {
                fetch(`<?php echo URL_ROOT; ?>/modules/api/notifications.php?action=get_latest`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            updateNotificationUI(data);
                        }
                    });
            }

            function formatTime(dateStr) {
                const date = new Date(dateStr);
                const now = new Date();
                const isToday = date.toDateString() === now.toDateString();
                
                if (isToday) {
                    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                } else {
                    return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
                }
            }
        });
    </script>
</body>
</html>
