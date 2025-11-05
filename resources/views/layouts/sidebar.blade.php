<div class="flex min-h-screen" x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
    @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
    x-init="window.addEventListener('resize', () => { sidebarOpen = window.innerWidth >= 1024 })">
    <!-- Sidebar (Fixed) -->
    <aside
        class="bg-white border-r border-gray-200 w-64 space-y-6 py-7 px-2 fixed top-16 left-0 h-[calc(100vh-4rem)] transition duration-200 ease-in-out z-30 transform"
        :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
        <nav class="flex flex-col space-y-2">
            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard.index') }}"
                class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('admin.dashboard.*') ? 'bg-gray-100 font-bold' : '' }}">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <!-- Facilities Menu -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @mouseenter="open = true" @mouseleave="open = false"
                    class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('admin.facilities.*') ? 'bg-gray-100 font-bold' : '' }}">
                    <i class="fas fa-building mr-2"></i> Facilities Mgmnt
                    <svg class="ml-auto h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" @mouseenter="open = true" @mouseleave="open = false"
                    class="absolute left-full top-0 mt-0 w-64 bg-white border border-gray-200 rounded shadow-lg z-50"
                    style="display: none;" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95">
                    <a href="{{ route('admin.facilities.index') }}"
                        class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-list mr-2"></i> All
                    </a>
                    <hr class="my-1">
                    <div style="max-height: 400px; overflow-y: auto;">
                        @php
                        $facilityList = isset($facilities) ? $facilities->sortBy('name') : [];
                        @endphp
                        <div style="overflow: visible; position: relative;">
                            @foreach($facilityList as $facility)
                            <div x-data="{ subOpen: false }" class="relative group" style="overflow: visible;"
                                @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                @php
                                $abbr = $facility->name;
                                if (\Str::contains($abbr, 'Driftwood') && \Str::contains($abbr, 'Hayward')) {
                                $abbr = 'Driftwood HCC - Hywd';
                                } elseif (\Str::contains($abbr, 'Driftwood') && \Str::contains($abbr, 'Santa Cruz'))
                                {
                                $abbr = 'Driftwood HCC - SCruz';
                                } elseif (\Str::contains($abbr, 'Glendale Transitional Care Center')) {
                                $abbr = 'Glendale TCC';
                                } else {
                                $abbr = str_replace('Health and Rehabilitation Center', 'HRC', $abbr);
                                $abbr = str_replace('Health Care and Rehabilitation Center', 'HRC', $abbr);
                                $abbr = str_replace('Health Care Center', 'HCC', $abbr);
                                $abbr = str_replace('Healthcare Center', 'HCC', $abbr);
                                }
                                @endphp
                                <a href="{{ route('facility.public', $facility) }}" target="_blank"
                                    class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-hospital mr-2"></i> {{ $abbr }}
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Web Contents Menu -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @mouseenter="open = true" @mouseleave="open = false"
                    class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">
                    <i class="fas fa-globe-americas mr-2"></i> Web Contents
                    <svg class="ml-auto h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" @mouseenter="open = true" @mouseleave="open = false"
                    class="absolute left-full top-0 mt-0 w-64 bg-white border border-gray-200 rounded shadow-lg z-50"
                    style="display: none;" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95">
                    <a href="{{ route('admin.facilities.webcontents.testimonials') }}"
                        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-quote-right mr-2"></i> Testimonials Management
                    </a>
                    <a href="{{ route('admin.facilities.webcontents.faqs') }}"
                        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-question-circle mr-2"></i> FAQs Management
                    </a>
                    <a href="{{ route('admin.galleries.index') }}"
                        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-images mr-2"></i> Galleries Management
                    </a>
                    <a href="{{ route('admin.news.index') }}"
                        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-newspaper mr-2"></i> News Management
                    </a>
                    <a href="{{ route('admin.facilities.webcontents.blogs') }}"
                        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-blog mr-2"></i> Blogs Management
                    </a>
                    <a href="{{ route('admin.facilities.webcontents.careers') }}"
                        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-briefcase mr-2"></i> Careers Management
                    </a>
                    <a href="{{ route('admin.services.index') }}"
                        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-cogs mr-2"></i> Services Management
                    </a>
                </div>
            </div>

            <!-- Communications Menu -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @mouseenter="open = true" @mouseleave="open = false"
                    class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">
                    <i class="fas fa-comments mr-2"></i> Communications
                    <svg class="ml-auto h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" @mouseenter="open = true" @mouseleave="open = false"
                    class="absolute left-full top-0 mt-0 w-64 bg-white border border-gray-200 rounded shadow-lg z-50"
                    style="display: none;" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95">
                    <a href="{{ route('admin.tour-requests.index') }}"
                        class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-calendar-alt mr-2"></i> Tour Requests
                    </a>
                    <a href="{{ route('admin.inquiries.index') }}"
                        class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-question-circle mr-2"></i> General Inquiries
                    </a>
                    <a href="{{ route('admin.job-applications.index') }}"
                        class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-briefcase mr-2"></i> Job Applications
                    </a>
                    <a href="{{ route('admin.email-recipients.index') }}"
                        class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-envelope mr-2"></i> Email Recipients
                    </a>
                    <a href="{{ route('admin.communications.employee-email-mappings') }}"
                        class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user-tie mr-2"></i> Employee Email Mappings
                    </a>
                </div>
            </div>

            <!-- Users Management -->
            <a href="{{ route('admin.users.index') }}"
                class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('admin.users.*') ? 'bg-gray-100 font-bold' : '' }}">
                <i class="fas fa-users mr-2"></i> Users Mgmnt
            </a>

        </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1">
        <!-- Add left margin and top padding for fixed sidebar and navbar -->
        <div :class="sidebarOpen ? 'pt-20 pl-64' : 'pt-20 pl-4'">
            <!-- Page Header -->
            @hasSection('header')
            <div class="bg-white/60 shadow-sm border-b border-gray-200 rounded-xl">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    @yield('header')
                </div>
            </div>
            @endif

            <!-- Main Content -->
            <main class="bg-white/60 rounded-xl max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                @yield('content')
            </main>
        </div>
    </div>
</div>