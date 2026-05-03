<header class="border-b border-gray-800 bg-gray-900/80 backdrop-blur-sm sticky top-0 z-40">
    <div class="container mx-auto max-w-7xl min-w-0 px-3 sm:px-4">
        <div class="flex items-center justify-between h-16">
            {{-- Logo --}}
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 sm:gap-3 min-w-0">
                <div class="tj-logo-mark w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center shadow-lg shrink-0">
                    <i class="fas fa-chart-line text-white text-xs sm:text-sm font-bold"></i>
                </div>
                <div class="leading-none min-w-0">
                    <div class="font-bold text-white text-base sm:text-lg leading-tight truncate">Trading Journal</div>
                    <div class="text-[10px] sm:text-xs text-cyan-400 font-medium truncate">Club Buy EA Community</div>
                </div>
            </a>

            {{-- Right side --}}
            @auth
            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                {{-- Theme Toggle --}}
                <div class="relative">
                    <button id="theme-toggle-btn" class="theme-toggle-btn" title="Ganti Tema">
                        <i id="theme-icon" class="fas fa-moon"></i>
                    </button>
                    <div id="theme-dropdown" class="theme-dropdown">
                        <div class="theme-option selected" data-theme="dark" onclick="setTheme('dark')">
                            <span class="theme-swatch swatch-dark"></span> Dark
                        </div>
                        <div class="theme-option" data-theme="light" onclick="setTheme('light')">
                            <span class="theme-swatch swatch-light"></span> Light
                        </div>
                        <div class="theme-option" data-theme="ocean" onclick="setTheme('ocean')">
                            <span class="theme-swatch swatch-ocean"></span> Ocean
                        </div>
                        <div class="theme-option" data-theme="forest" onclick="setTheme('forest')">
                            <span class="theme-swatch swatch-forest"></span> Forest
                        </div>
                        <div class="theme-option" data-theme="neubrutalism" onclick="setTheme('neubrutalism')">
                            <span class="theme-swatch swatch-neubrutalism"></span> Neo Brutal
                        </div>
                    </div>
                </div>

                {{-- Notification Bell --}}
                @include('components.notification-bell')
                
                <div class="hidden md:flex items-center gap-3 text-sm">
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-800 rounded-lg">
                        <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                        <span class="text-gray-300 font-medium">{{ Auth::user()->name }}</span>
                    </div>
                    <span class="text-xs px-2 py-1 bg-cyan-500/10 text-cyan-400 rounded-md font-semibold border border-cyan-500/20">
                        {{ Auth::user()->currency }}
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2 text-sm text-gray-400 hover:text-red-400 transition-colors px-3 py-2 rounded-lg hover:bg-red-500/10">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="hidden md:inline">Logout</span>
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </div>
</header>
