<div class="flex min-h-screen" x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
    @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
    x-init="window.addEventListener('resize', () => { sidebarOpen = window.innerWidth >= 1024 })">
    <!-- Sidebar (Fixed) -->
    <aside
        class="bg-white border-r border-gray-200 w-64 space-y-6 py-7 px-2 fixed top-16 left-0 h-[calc(100vh-4rem)] transition duration-200 ease-in-out z-30 transform"
        :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
        <nav class="flex flex-col space-y-2">
            <!-- Dashboard -->
            <a href="{{ route('dashboard.index') }}"
                class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('dashboard.index') ? 'bg-gray-100 font-bold' : '' }}">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <!-- Profile -->
            <a href="{{ route('settings.profile') }}"
                class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('settings.profile') ? 'bg-gray-100 font-bold' : '' }}">
                <i class="fas fa-user mr-2"></i> My Profile
            </a>
            <!-- Other user links can be added here -->
        </nav>
    </aside>
    <!-- Main Content Area -->
    <div class="flex-1">
        <div :class="sidebarOpen ? 'pt-20 pl-64' : 'pt-20 pl-4'">
            @hasSection('header')
            <div class="bg-white/60 shadow-sm border-b border-gray-200 rounded-xl">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    @yield('header')
                </div>
            </div>
            @endif
            <main class="bg-white/60 rounded-xl max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                @yield('content')
            </main>
        </div>
    </div>
</div>