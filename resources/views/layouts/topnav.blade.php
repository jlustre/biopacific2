<nav class="bg-white shadow-sm border-b border-gray-200 fixed top-0 left-0 w-full z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.dashboard.index') }}" class="flex items-center">
                    <img src="@secureAsset('images/bplogo.png')" alt="Logo" class="h-12 w-auto" />
                    <div class="flex flex-col items-start leading-tight -mt-1">
                        <span class="text-xl font-bold text-teal-800">Bio-Pacific</span>
                        <span class="text-sm text-gray-700 -mt-2">Administration</span>
                    </div>
                </a>

                <!-- Sidebar Toggle Button (Right of Logo/Name) -->
                <button x-data="{}" @click="window.dispatchEvent(new CustomEvent('toggle-sidebar'))"
                    class="ml-2 bg-teal-50 text-teal-700 rounded-full shadow-lg p-1 hover:bg-teal-200 transition-all w-8 h-8 flex items-center justify-center">
                    <i class="fas fa-bars text-sm"></i>
                </button>
            </div>
            <!-- Topbar Right Section -->
            @include('layouts.partials.profile_dropdown')
        </div>
    </div>
</nav>