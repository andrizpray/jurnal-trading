<nav class="tj-nav bg-gray-900/50 border-b border-gray-800/60">
    <div class="container mx-auto max-w-7xl min-w-0 px-3 sm:px-4">
        <div class="tj-nav-scroll flex items-center gap-1 overflow-x-auto py-2 scrollbar-hide -mx-1 px-1">
            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }} whitespace-nowrap">
                <i class="fas fa-tachometer-alt text-xs"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('trading-plan.index') }}"
               class="nav-link {{ request()->routeIs('trading-plan.*') ? 'active' : '' }} whitespace-nowrap">
                <i class="fas fa-calculator text-xs"></i>
                <span>Trading Plan</span>
            </a>
            <a href="{{ route('challenge.index') }}"
               class="nav-link {{ request()->routeIs('challenge.*') ? 'active' : '' }} whitespace-nowrap">
                <i class="fas fa-trophy text-xs"></i>
                <span>Challenge 30 Hari</span>
            </a>
            <a href="{{ route('journal.index') }}"
               class="nav-link {{ request()->routeIs('journal.*') ? 'active' : '' }} whitespace-nowrap">
                <i class="fas fa-book text-xs"></i>
                <span>Trading Journal</span>
            </a>
            <a href="{{ route('settings') }}"
               class="nav-link {{ request()->routeIs('settings*') ? 'active' : '' }} whitespace-nowrap">
                <i class="fas fa-cog text-xs"></i>
                <span>Settings</span>
            </a>
        </div>
    </div>
</nav>
