@php
    $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
    $canReceiveNotification = $admin && ($admin->isSuperAdmin() || $admin->hasPermission('notifications.index'));
@endphp
<header class="h-16 px-gutter flex justify-between items-center sticky top-0 z-40 bg-surface/95 backdrop-blur-md border-b border-outline-variant transition-all duration-300 shadow-xs">
    <div class="flex items-center gap-4">
        <!-- Quick Search Pill Bar (UX Enhancement) -->
        <div class="hidden sm:flex items-center gap-2.5 px-3.5 py-1.5 rounded-xl bg-surface-gray border border-outline-variant/70 text-xs text-on-surface-variant font-medium hover:border-primary/40 transition-colors shadow-2xs cursor-pointer">
            <span class="material-symbols-outlined text-[17px] text-primary">search</span>
            <span>Cari modul retail, katalog kasur, transaksi...</span>
            <span class="ml-2 px-1.5 py-0.5 rounded bg-surface border border-outline-variant/70 text-[10px] font-mono text-on-surface-variant shadow-2xs font-semibold">Ctrl K</span>
        </div>

        <div class="flex items-center gap-2">
            @if($canReceiveNotification)
                <!-- Notifications Dropdown Container (Role-Gated) -->
                <div class="relative" id="notificationDropdownContainer">
                    <button id="notificationDropdownBtn" type="button" class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container rounded-xl transition-all relative flex items-center justify-center focus:outline-none" title="Notifikasi Stok & Sistem">
                        <span class="material-symbols-outlined text-[22px]">notifications</span>
                        <!-- Dynamic Stock Alert Badge -->
                        <span id="notificationBadge" class="hidden absolute top-1.5 right-1.5 min-w-[16px] h-[16px] bg-red-600 text-white text-[9px] font-bold rounded-full px-1 flex items-center justify-center border-2 border-surface shadow-xs animate-pulse">0</span>
                    </button>

                    <!-- Notifications Dropdown Box -->
                    <div id="notificationDropdownMenu" class="hidden absolute left-0 mt-2 w-80 sm:w-96 bg-surface rounded-2xl shadow-2xl border border-outline-variant/60 overflow-hidden z-50 transition-all duration-200">
                        <!-- Dropdown Header -->
                        <div class="px-4 py-3 bg-surface-gray border-b border-outline-variant/40 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-[20px]">crisis_alert</span>
                                <span class="font-bold text-sm text-on-surface">Pemberitahuan Stok</span>
                                <span id="notificationHeaderCount" class="text-xs bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 font-semibold px-2 py-0.5 rounded-full">0 Baru</span>
                            </div>
                            <button type="button" id="markAllReadBtn" class="text-xs text-primary hover:underline font-semibold cursor-pointer">
                                Tandai semua dibaca
                            </button>
                        </div>

                        <!-- Notification Items Scroll Area -->
                        <div id="notificationList" class="max-h-[380px] overflow-y-auto divide-y divide-outline-variant/20">
                            <div id="notificationLoading" class="p-6 text-center text-on-surface-variant text-sm">
                                <span class="material-symbols-outlined animate-spin text-[24px] text-primary">progress_activity</span>
                                <p class="mt-1">Memuat notifikasi...</p>
                            </div>
                        </div>

                        <!-- Dropdown Footer -->
                        <div class="px-4 py-2.5 bg-surface-gray/50 border-t border-outline-variant/40 flex items-center justify-between text-xs text-on-surface-variant">
                            <a href="{{ route('inventory.index') }}" class="font-semibold text-primary hover:underline flex items-center gap-1">
                                <span>Buka Inventori</span>
                                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                            </a>
                            <button type="button" id="testAlertBtn" class="text-[11px] text-on-surface-variant/70 hover:text-primary transition-colors flex items-center gap-1" title="Kirim alert uji coba Pusher">
                                <span class="material-symbols-outlined text-[13px]">bolt</span> Test Push
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <button id="darkModeToggle" class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container rounded-xl transition-all" title="Toggle Dark Mode">
                <span class="material-symbols-outlined text-[22px]">dark_mode</span>
            </button>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <div class="h-6 w-[1px] bg-outline-variant mx-1"></div>
        <div class="relative" id="profileDropdownContainer">
            <button class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-surface-gray transition-colors group" id="profileDropdownBtn">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold leading-tight text-on-surface group-hover:text-primary transition-colors">{{ $admin?->name ?? 'User' }}</p>
                    <p class="text-[10px] font-medium text-on-surface-variant">{{ $admin?->email ?? 'admin@royal.co.id' }}</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-sky-500 to-blue-700 dark:from-amber-700 dark:to-stone-900 flex items-center justify-center text-white font-bold text-xs shadow-xs border border-white/20">
                    {{ strtoupper(substr($admin?->name ?? 'U', 0, 1)) }}
                </div>
                <span class="material-symbols-outlined text-[18px] text-on-surface-variant group-hover:text-primary transition-colors">expand_more</span>
            </button>
            <div id="profileDropdownMenu" class="hidden absolute right-0 top-full mt-2 border border-outline-variant/60 shadow-xl rounded-2xl overflow-hidden z-50 bg-surface" style="min-width: 220px;">
                <div class="px-4 py-3 bg-surface-gray border-b border-outline-variant/30">
                    <p class="text-xs font-bold text-on-surface">{{ $admin?->name ?? 'User' }}</p>
                    <p class="text-[11px] text-on-surface-variant">{{ $admin?->email ?? 'admin@royal.co.id' }}</p>
                </div>
                <div class="py-1.5">
                    <a href="{{ route('profile.show') }}" class="flex items-center px-4 py-2 text-xs font-medium text-on-surface hover:bg-surface-container hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[18px] mr-2.5">person</span> Edit Profil
                    </a>
                </div>
                <div class="border-t border-outline-variant/30"></div>
                <div class="py-1">
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="w-full text-left flex items-center px-4 py-2 text-xs font-semibold text-danger hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-[18px] mr-2.5">logout</span> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
</header>

@if($canReceiveNotification)
<!-- Pusher Client Script for Real-Time Notification (Only loaded for authorized roles) -->
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropdownContainer = document.getElementById('notificationDropdownContainer');
    const dropdownBtn = document.getElementById('notificationDropdownBtn');
    const dropdownMenu = document.getElementById('notificationDropdownMenu');
    const badge = document.getElementById('notificationBadge');
    const headerCount = document.getElementById('notificationHeaderCount');
    const notifList = document.getElementById('notificationList');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const testAlertBtn = document.getElementById('testAlertBtn');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // 1. Dropdown Toggle & Outside Click
    if (dropdownBtn && dropdownMenu) {
        dropdownBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', function (e) {
            if (!dropdownContainer.contains(e.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });
    }

    // 2. Audio Notification Chime (Web Audio API Synthesizer)
    function playNotificationSound(severity) {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);

            if (severity === 'critical') {
                // Two-tone alert for out of stock (0)
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(659.25, ctx.currentTime); // E5
                osc.frequency.setValueAtTime(880, ctx.currentTime + 0.12); // A5
                gain.gain.setValueAtTime(0.25, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
                osc.start();
                osc.stop(ctx.currentTime + 0.4);
            } else {
                // Single bright chime for low stock
                osc.type = 'sine';
                osc.frequency.setValueAtTime(783.99, ctx.currentTime); // G5
                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                osc.start();
                osc.stop(ctx.currentTime + 0.3);
            }
        } catch (err) {
            // Audio context silently ignored if user hasn't interacted yet
        }
    }

    // 3. Update Badge UI
    function updateBadge(count) {
        if (!badge || !headerCount) return;
        const num = parseInt(count, 10) || 0;
        if (num > 0) {
            badge.textContent = num > 99 ? '99+' : num;
            badge.classList.remove('hidden');
            headerCount.textContent = num + ' Baru';
            headerCount.classList.remove('hidden');
        } else {
            badge.textContent = '0';
            badge.classList.add('hidden');
            headerCount.textContent = '0 Baru';
        }
    }

    // 4. Render Notification Item HTML
    function buildNotificationItem(item) {
        const isRead = item.is_read;
        const isCritical = (item.title && item.title.includes('HABIS')) || item.type === 'stock_out';
        const iconName = isCritical ? 'error' : 'warning';
        const iconBg = isCritical ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400';
        const readClass = isRead ? 'opacity-70 bg-surface' : 'bg-surface-container/20 font-semibold';
        const dotBadge = !isRead ? '<span class="w-2 h-2 rounded-full bg-red-600 shrink-0"></span>' : '';

        return `
            <div data-id="${item.id}" data-url="${item.link_url || '#'}" class="notification-item flex items-start gap-3 p-3.5 hover:bg-surface-container transition-colors cursor-pointer ${readClass}">
                <div class="w-9 h-9 rounded-xl ${iconBg} flex items-center justify-center shrink-0 mt-0.5">
                    <span class="material-symbols-outlined text-[20px]">${iconName}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-1">
                        <p class="text-xs font-bold text-on-surface truncate">${item.title || 'Pemberitahuan Stok'}</p>
                        ${dotBadge}
                    </div>
                    <p class="text-[11px] text-on-surface-variant line-clamp-2 mt-0.5 leading-snug">${item.message || ''}</p>
                    <p class="text-[10px] text-on-surface-variant/70 mt-1">${item.created_at_human || 'Baru saja'}</p>
                </div>
            </div>
        `;
    }

    // 5. Fetch Initial Notifications
    function fetchNotifications() {
        fetch('{{ route('notifications.index') }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (res.status === 403) {
                // Not authorized to view notifications
                return null;
            }
            return res.json();
        })
        .then(data => {
            if (!data) return;
            if (data.success) {
                updateBadge(data.unread_count);
                if (data.notifications && data.notifications.length > 0) {
                    let html = '';
                    data.notifications.forEach(notif => {
                        html += buildNotificationItem(notif);
                    });
                    notifList.innerHTML = html;
                    attachItemClickListeners();
                } else {
                    notifList.innerHTML = `
                        <div class="p-8 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-[36px] text-success/80">check_circle</span>
                            <p class="text-xs font-medium mt-2">Semua stok aman dan terkendali.</p>
                            <p class="text-[11px] text-on-surface-variant/60 mt-0.5">Tidak ada peringatan stok saat ini.</p>
                        </div>
                    `;
                }
            }
        })
        .catch(err => {
            console.error('Failed to load notifications:', err);
            notifList.innerHTML = `
                <div class="p-6 text-center text-on-surface-variant text-xs">
                    <p>Gagal memuat notifikasi.</p>
                </div>
            `;
        });
    }

    // 6. Attach click listener to notification items
    function attachItemClickListeners() {
        document.querySelectorAll('.notification-item').forEach(el => {
            el.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const url = this.getAttribute('data-url');

                if (id) {
                    fetch(`{{ url('notifications') }}/${id}/read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            updateBadge(res.unread_count);
                        }
                    })
                    .catch(() => {});
                }

                if (url && url !== '#') {
                    window.location.href = url;
                }
            });
        });
    }

    // 7. Mark All As Read
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function () {
            fetch('{{ route('notifications.mark-all-read') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateBadge(0);
                    document.querySelectorAll('.notification-item').forEach(el => {
                        el.classList.remove('bg-surface-container/20', 'font-semibold');
                        el.classList.add('opacity-70', 'bg-surface');
                        const dot = el.querySelector('.bg-red-600');
                        if (dot) dot.remove();
                    });
                }
            })
            .catch(err => console.error('Failed to mark all as read:', err));
        });
    }

    // 8. Test Push Alert Button
    if (testAlertBtn) {
        testAlertBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            testAlertBtn.disabled = true;
            testAlertBtn.innerHTML = '<span class="material-symbols-outlined text-[13px] animate-spin">progress_activity</span> Mengirim...';

            fetch('{{ route('notifications.test-stock-alert') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(res => {
                testAlertBtn.disabled = false;
                testAlertBtn.innerHTML = '<span class="material-symbols-outlined text-[13px]">bolt</span> Test Push';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: res.success ? 'success' : 'error',
                        title: res.success ? 'Berhasil Mengirim!' : 'Gagal',
                        text: res.message || 'Pusher event telah dipicu.',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            })
            .catch(err => {
                testAlertBtn.disabled = false;
                testAlertBtn.innerHTML = '<span class="material-symbols-outlined text-[13px]">bolt</span> Test Push';
                console.error('Test alert error:', err);
            });
        });
    }

    // 9. Pusher Real-Time WebSocket Listener
    const pusherKey = "{{ config('broadcasting.connections.pusher.key') }}";
    const pusherCluster = "{{ config('broadcasting.connections.pusher.options.cluster', 'ap1') }}";

    if (pusherKey && typeof Pusher !== 'undefined') {
        try {
            const pusher = new Pusher(pusherKey, {
                cluster: pusherCluster,
                forceTLS: true
            });

            const channel = pusher.subscribe('admin.notifications');

            channel.bind('stock.alert', function (payload) {
                console.log('[Pusher] Real-time Stock Alert received:', payload);

                // Sound alert
                playNotificationSound(payload.severity);

                // Increment badge counter
                const currentCount = parseInt(badge.textContent, 10) || 0;
                updateBadge(currentCount + 1);

                // Prepend item to dropdown
                const itemHtml = buildNotificationItem({
                    id: payload.notification_id || '',
                    title: payload.title,
                    message: payload.message,
                    type: payload.severity === 'critical' ? 'stock_out' : 'stock_low',
                    link_url: payload.link_url,
                    is_read: false,
                    created_at_human: 'Baru saja'
                });

                // Clear empty state if present
                if (notifList.querySelector('.text-success')) {
                    notifList.innerHTML = '';
                }
                notifList.insertAdjacentHTML('afterbegin', itemHtml);
                attachItemClickListeners();

                // Display SweetAlert2 Toast
                if (typeof Swal !== 'undefined') {
                    const isCritical = payload.severity === 'critical';
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: isCritical ? 'error' : 'warning',
                        title: payload.title,
                        html: `<div class="text-left text-xs text-on-surface-variant font-medium mt-1">${payload.message}</div>`,
                        showConfirmButton: true,
                        confirmButtonText: 'Periksa Stok',
                        confirmButtonColor: isCritical ? '#dc2626' : '#d97706',
                        showCancelButton: true,
                        cancelButtonText: 'Tutup',
                        timer: 9000,
                        timerProgressBar: true,
                    }).then((result) => {
                        if (result.isConfirmed && payload.link_url) {
                            window.location.href = payload.link_url;
                        }
                    });
                }
            });
        } catch (e) {
            console.error('Failed to initialize Pusher in topbar:', e);
        }
    }

    // Initial load
    fetchNotifications();
});
</script>
@endif