<div x-data="notificationBell()" class="relative">
    <button @click="toggleNotifications" class="relative p-2 rounded-lg hover:bg-gray-800/50 transition">
        <i class="fas fa-bell text-gray-400 hover:text-white transition"></i>
        <template x-if="unreadCount > 0">
            <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center"
                  x-text="unreadCount"></span>
        </template>
    </button>

    {{-- Notification Dropdown --}}
    <div x-show="isOpen" 
         @click.away="closeNotifications"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 bg-gray-900 border border-gray-800 rounded-xl shadow-xl z-50 overflow-hidden"
         style="display: none;">
        
        {{-- Header --}}
        <div class="p-4 border-b border-gray-800">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-white">Notifications</h3>
                <button @click="markAllAsRead" 
                        class="text-sm text-cyan-400 hover:text-cyan-300 transition"
                        :disabled="unreadCount === 0">
                    Mark all as read
                </button>
            </div>
            <div class="text-xs text-gray-500 mt-1" x-text="unreadCount + ' unread'"></div>
        </div>

        {{-- Notifications List --}}
        <div class="max-h-96 overflow-y-auto">
            <template x-if="notifications.length === 0">
                <div class="p-6 text-center text-gray-500">
                    <i class="fas fa-bell-slash text-2xl mb-3 block"></i>
                    <p>No notifications yet</p>
                </div>
            </template>

            <template x-for="notification in notifications" :key="notification.id">
                <div class="p-4 border-b border-gray-800 hover:bg-gray-800/30 transition"
                     :class="{ 'bg-gray-800/20': !notification.read_at }">
                    <div class="flex items-start">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3 shrink-0"
                             :class="'bg-' + notification.color + '-500/10'">
                            <i class="fas" :class="'fa-' + notification.icon" 
                               :class="'text-' + notification.color + '-400'"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-white" x-text="notification.message"></p>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-xs text-gray-500" x-text="notification.created_at"></span>
                                <template x-if="notification.action_url">
                                    <a :href="notification.action_url" 
                                       class="text-xs text-cyan-400 hover:text-cyan-300">
                                        View
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div class="p-3 border-t border-gray-800 text-center">
            <a href="{{ route('notifications.index') }}" class="text-sm text-gray-400 hover:text-white transition">
                View all notifications
            </a>
        </div>
    </div>
</div>

<script>
function notificationBell() {
    return {
        isOpen: false,
        notifications: [],
        unreadCount: 0,
        
        init() {
            this.loadNotifications();
            // Refresh notifications every 30 seconds
            setInterval(() => this.loadNotifications(), 30000);
        },
        
        toggleNotifications() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.loadNotifications();
            }
        },
        
        closeNotifications() {
            this.isOpen = false;
        },
        
        async loadNotifications() {
            try {
                const response = await fetch('/api/notifications');
                if (!response.ok) {
                    // Auth failed or other error - silently ignore
                    this.notifications = [];
                    this.unreadCount = 0;
                    return;
                }
                const json = await response.json();
                
                this.notifications = json.data?.notifications || [];
                this.unreadCount = json.data?.unread_count || 0;
            } catch (error) {
                console.error('Failed to load notifications:', error);
            }
        },
        
        async markAllAsRead() {
            if (this.unreadCount === 0) return;
            
            try {
                const response = await fetch('/api/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    this.unreadCount = 0;
                    // Update notifications to show as read
                    this.notifications = this.notifications.map(notification => ({
                        ...notification,
                        read_at: new Date().toISOString()
                    }));
                }
            } catch (error) {
                console.error('Failed to mark notifications as read:', error);
            }
        }
    }
}
</script>