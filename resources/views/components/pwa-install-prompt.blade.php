<div x-data="pwaInstall()" x-show="showPrompt" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4" class="fixed bottom-4 right-4 z-50 max-w-sm"
    style="display: none;">

    <div class="bg-gray-900 border border-gray-800 rounded-xl shadow-2xl overflow-hidden">
        <div class="p-4">
            <div class="flex items-start">
                <div
                    class="w-12 h-12 rounded-lg bg-gradient-to-br from-cyan-500 to-green-400 flex items-center justify-center mr-3 shrink-0">
                    <i class="fas fa-chart-line text-black text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-white">Install Trading Journal</h3>
                    <p class="text-sm text-gray-400 mt-1">
                        Install our app for better experience, offline access, and push notifications.
                    </p>
                </div>
            </div>

            <div class="flex gap-2 mt-4">
                <button @click="installApp"
                    class="flex-1 bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-semibold py-2.5 px-4 rounded-lg hover:opacity-90 transition">
                    <i class="fas fa-download mr-2"></i>Install App
                </button>
                <button @click="dismissPrompt"
                    class="px-4 py-2.5 text-gray-400 hover:text-white transition rounded-lg hover:bg-gray-800">
                    Not Now
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function pwaInstall() {
        return {
            showPrompt: false,
            deferredPrompt: null,

            init() {
                // Check if already installed
                if (this.isStandalone()) {
                    return;
                }

                // Listen for beforeinstallprompt event
                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.deferredPrompt = e;

                    // Show prompt after 5 seconds
                    setTimeout(() => {
                        if (this.deferredPrompt && !this.isDismissed()) {
                            this.showPrompt = true;
                        }
                    }, 5000);
                });

                // Check if installed after user gesture
                window.addEventListener('appinstalled', () => {
                    console.log('App installed successfully');
                    this.showPrompt = false;
                    this.deferredPrompt = null;
                    this.setDismissed(true);
                });
            },

            installApp() {
                if (!this.deferredPrompt) {
                    return;
                }

                this.deferredPrompt.prompt();

                this.deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted install');
                    } else {
                        console.log('User dismissed install');
                    }

                    this.deferredPrompt = null;
                    this.showPrompt = false;
                    this.setDismissed(true);
                });
            },

            dismissPrompt() {
                this.showPrompt = false;
                this.setDismissed(true);
            },

            isStandalone() {
                return window.matchMedia('(display-mode: standalone)').matches ||
                    window.navigator.standalone ||
                    document.referrer.includes('android-app://');
            },

            isDismissed() {
                return localStorage.getItem('pwaPromptDismissed') === 'true';
            },

            setDismissed(value) {
                localStorage.setItem('pwaPromptDismissed', value.toString());
            }
        }
    }
</script>