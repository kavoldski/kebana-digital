<?php
/**
 * KEBANA Digital Management System - Footer Component with Tailwind
 * File: includes/footer.php
 */
?>
            </div><!-- End Page Content Container -->
            
            <footer class="mt-auto py-8 bg-white border-t border-slate-200">
                <div class="px-4 lg:px-8 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <p class="text-sm text-slate-500">
                        &copy; <?php echo date('Y'); ?> KEBANA Digital Management System.
                    </p>
                    <div class="flex space-x-6 text-sm text-slate-500">
                        <a href="<?php echo URL_ROOT; ?>/manual" class="hover:text-primary-600 transition-colors font-bold text-kebana-blue"><i class="fa-solid fa-book mr-1"></i> User Manual</a>
                        <a href="#" class="hover:text-primary-600 transition-colors">Privacy Policy</a>
                        <a href="#" class="hover:text-primary-600 transition-colors">Terms of Service</a>
                        <span class="text-slate-300">v1.1.0-tailwind</span>
                    </div>
                </div>
            </footer>
        </main>
    </div>

    <script>
        function formatTime(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const mobileToggle = document.getElementById('mobileToggle');
            const clockElement = document.getElementById('realtime-clock');

            function updateClock() {
                if (!clockElement) return;
                const now = new Date();
                const day = now.getDate().toString().padStart(2, '0');
                const months = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
                const month = months[now.getMonth()];
                const year = now.getFullYear();
                
                let hours = now.getHours();
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12; // the hour '0' should be '12'
                const minutes = now.getMinutes().toString().padStart(2, '0');
                
                clockElement.innerText = `${day} ${month} ${year} • ${hours.toString().padStart(2, '0')}:${minutes} ${ampm}`;
            }

            if (clockElement) {
                setInterval(updateClock, 1000);
                updateClock();
            }
            
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
                                <span class="text-xs font-black text-kebana-blue uppercase tracking-widest">${notif.type.replace('_', ' ')}</span>
                                <span class="text-xs text-slate-500 font-bold uppercase">${formatTime(notif.created_at)}</span>
                            </div>
                            <h4 class="text-sm font-black text-slate-800 leading-tight">${notif.title}</h4>
                            <p class="text-sm text-slate-600 mt-1 line-clamp-2">${notif.message}</p>
                        </div>
                    `).join('');
                } else {
                    notificationList.innerHTML = `
                        <div class="p-8 text-center text-slate-300">
                            <i class="fa-regular fa-bell-slash text-2xl mb-2 block"></i>
                            <p class="text-sm font-bold uppercase text-slate-500">No new notifications</p>
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
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        if (notificationBadge) notificationBadge.classList.add('hidden');
                        // Optimistic UI: Mark all notifications in the list as read
                        document.querySelectorAll('#notificationList > div').forEach(el => {
                            el.classList.remove('bg-blue-50/30');
                        });
                    } else {
                        console.error('Failed to mark all as read:', data.message);
                    }
                })
                .catch(err => console.error('Error marking all as read:', err));
            };

            window.clearAllNotifications = function() {
                if (!confirm('Adakah anda pasti ingin memadam semua notifikasi?')) return;
                
                fetch(`<?php echo URL_ROOT; ?>/modules/api/notifications.php?action=clear_all`, { 
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'clear_all' })
                })
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        if (notificationBadge) notificationBadge.classList.add('hidden');
                        if (notificationList) {
                            notificationList.innerHTML = `
                                <div class="p-8 text-center text-slate-300">
                                    <i class="fa-regular fa-bell-slash text-2xl mb-2 block"></i>
                                    <p class="text-sm font-bold uppercase text-slate-500">Tiada Notifikasi Baru</p>
                                </div>
                            `;
                        }
                    } else {
                        alert('Gagal membersihkan notifikasi: ' + (data.message || 'Ralat tidak diketahui'));
                    }
                })
                .catch(err => {
                    console.error('Error clearing notifications:', err);
                    alert('Ralat sistem berlaku semasa membersihkan notifikasi.');
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

            // Live Search Implementation
            let searchTimeout;
            const resultsContainerId = 'live-search-results';

            function attachLiveSearch() {
                // Handle Search Inputs
                const searchInputs = document.querySelectorAll('input[name="search"], .live-search');
                searchInputs.forEach(input => {
                    input.removeEventListener('input', handleSearchInput);
                    input.addEventListener('input', handleSearchInput);
                    
                    // Prevent form submission refresh
                    const form = input.closest('form');
                    if (form) {
                        form.removeEventListener('submit', handleFormSubmit);
                        form.addEventListener('submit', handleFormSubmit);
                    }
                });

                // Intercept links (Pagination, Filters, Clear buttons)
                document.querySelectorAll('a').forEach(link => {
                    const isLocal = link.href && link.href.includes(window.location.pathname) && !link.href.includes('#');
                    const isAction = link.href && (link.href.includes('?search=') || link.href.includes('?page=') || link.href.includes('?status=') || link.href.includes('?type=') || link.href.endsWith(window.location.pathname));
                    
                    if (isLocal && isAction) {
                        link.removeEventListener('click', handleLinkClick);
                        link.addEventListener('click', handleLinkClick);
                    }
                });
            }

            function handleFormSubmit(e) {
                e.preventDefault();
                const formData = new FormData(e.target);
                const params = new URLSearchParams(formData);
                const url = new URL(window.location.href);
                params.forEach((value, key) => url.searchParams.set(key, value));
                fetchResults(url.toString());
            }

            function handleLinkClick(e) {
                // Only intercept if it doesn't have other actions like 'delete' or 'toggle'
                const url = new URL(e.currentTarget.href);
                const hasAction = url.searchParams.has('action') || url.searchParams.has('delete') || url.searchParams.has('toggle');
                
                if (!hasAction) {
                    e.preventDefault();
                    fetchResults(e.currentTarget.href);
                }
            }

            function handleSearchInput(e) {
                clearTimeout(searchTimeout);
                const query = e.target.value;
                const url = new URL(window.location.href);
                url.searchParams.set('search', query);
                if (url.searchParams.has('page')) url.searchParams.set('page', '1');

                searchTimeout = setTimeout(() => {
                    fetchResults(url.toString());
                }, 300);
            }

            function fetchResults(url) {
                const container = document.getElementById(resultsContainerId);
                if (!container) {
                    // Fallback to full page refresh if container missing
                    window.location.href = url;
                    return;
                }

                container.style.opacity = '0.5';
                container.style.pointerEvents = 'none';

                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newContent = doc.getElementById(resultsContainerId);

                        if (newContent) {
                            container.innerHTML = newContent.innerHTML;
                            container.style.opacity = '1';
                            container.style.pointerEvents = 'auto';
                            attachLiveSearch();
                            window.history.pushState({}, '', url);
                        } else {
                            window.location.href = url;
                        }
                    })
                    .catch(err => {
                        console.error('Live Search Error:', err);
                        window.location.href = url;
                    });
            }

            attachLiveSearch();
        });

        // Global Premium File Input Staging Widget
        window.setupFileInputWidget = function(inputId, zoneId, labelId, iconId, previewId, thumbnailId, nameId, sizeId, clearId) {
            const input = document.getElementById(inputId);
            const zone = document.getElementById(zoneId);
            const label = document.getElementById(labelId);
            const icon = document.getElementById(iconId);
            const preview = document.getElementById(previewId);
            const thumbnail = document.getElementById(thumbnailId);
            const nameSpan = document.getElementById(nameId);
            const sizeSpan = document.getElementById(sizeId);
            const clearBtn = document.getElementById(clearId);
            
            if (!input || !zone) return;

            // Handle Drag & Drop styles
            const addActiveStyles = () => {
                zone.classList.add('border-kebana-blue', 'bg-slate-100/50');
                if (icon) icon.classList.add('text-kebana-blue');
            };
            const removeActiveStyles = () => {
                zone.classList.remove('border-kebana-blue', 'bg-slate-100/50');
                if (icon) icon.classList.remove('text-kebana-blue');
            };

            input.addEventListener('dragenter', addActiveStyles);
            input.addEventListener('dragover', addActiveStyles);
            input.addEventListener('dragleave', removeActiveStyles);
            input.addEventListener('drop', removeActiveStyles);

            input.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
                    
                    // Populate details
                    if (nameSpan) nameSpan.textContent = file.name;
                    if (sizeSpan) sizeSpan.textContent = `${sizeInMB} MB`;
                    
                    // Set thumbnail preview
                    if (thumbnail) {
                        const ext = file.name.split('.').pop().toLowerCase();
                        if (['jpg', 'jpeg', 'png'].includes(ext)) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                thumbnail.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded">`;
                            }
                            reader.readAsDataURL(file);
                        } else if (ext === 'pdf') {
                            thumbnail.innerHTML = '<i class="fa-solid fa-file-pdf text-red-500 text-2xl"></i>';
                        } else {
                            thumbnail.innerHTML = '<i class="fa-solid fa-file text-slate-400 text-2xl"></i>';
                        }
                    }
                    
                    // Show preview box, hide dropzone
                    zone.classList.add('hidden');
                    if (preview) {
                        preview.classList.remove('hidden');
                        preview.classList.add('flex');
                    }
                }
            });

            if (clearBtn) {
                clearBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    input.value = ''; // Reset file input
                    zone.classList.remove('hidden');
                    if (preview) {
                        preview.classList.add('hidden');
                        preview.classList.remove('flex');
                    }
                });
            }
        };
    </script>
    <?php
    $current_route = $route ?? $_GET['route'] ?? '';
    $current_route = preg_replace('/\.php$/', '', $current_route);
    if (isset($_SESSION['user_id']) && $current_route !== 'chat'):
        require_once APP_ROOT . '/includes/chat_widget.php';
    endif;
    ?>
</body>
</html>
