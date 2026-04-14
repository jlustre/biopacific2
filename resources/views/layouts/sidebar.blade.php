@php
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
@endphp

<aside id="sidebar"
    class="bg-white border-r border-gray-200 w-64 space-y-6 py-7 px-2 fixed top-16 left-0 h-[calc(100vh-4rem)] transition duration-200 ease-in-out z-30 transform"
    :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
    <nav class="flex flex-col space-y-2">
        @php
        $user = auth()->user();
        $showPreEmploymentLink = false;

        if ($user) {
        $showPreEmploymentLink = \App\Models\JobApplication::where('user_id', $user->id)
        ->where('status', 'pre-employment')
        ->exists();
        }
        @endphp

        <!-- Bio-Pacific Facility Public Web Page -->
        <a href="{{ route('facility.public', ['facility' => 'bio-pacific-corporate']) }}" target="_blank"
            class="flex items-center px-4 py-2 text-teal-700 hover:bg-teal-50 rounded font-semibold">
            <i class="fas fa-globe mr-2"></i> Bio-Pacific Home
        </a>

        <!-- Always show personal dashboard -->
        <a href="{{ route('dashboard.index') }}"
            class="flex items-center px-4 py-2 text-blue-700 hover:bg-blue-50 rounded {{ request()->routeIs('dashboard.index') ? 'bg-blue-100 font-bold' : '' }}">
            <i class="fas fa-user mr-2"></i> My Dashboard
        </a>

        @if($showPreEmploymentLink)
        <a href="{{ route('pre-employment.portal') }}"
            class="flex items-center px-4 py-2 text-teal-700 hover:bg-teal-50 rounded {{ request()->routeIs('pre-employment.*') ? 'bg-teal-100 font-bold' : '' }}">
            <i class="fas fa-file-signature mr-2"></i> Pre-Employment
        </a>
        @endif

        <!-- Show HR Portal if user is admin, hrrd, facility-admin, or facility-dsd -->
        @if($user && $user->hasRole('admin') && $user->roles->count() === 1)
        <a href="{{ route('hr-portal.index') }}"
            class="flex items-center px-4 py-2 text-indigo-700 hover:bg-indigo-50 rounded {{ request()->routeIs('admin.hr-portal.*') ? 'bg-indigo-100 font-bold' : '' }}">
            <i class="fas fa-users-cog mr-2"></i> HR Portal
        </a>
        @elseif($user && $user->hasRole(['hrrd','facility-admin','facility-dsd']))
        <a href="{{ route('user.hr-portal') }}"
            class="flex items-center px-4 py-2 text-indigo-700 hover:bg-indigo-50 rounded {{ request()->routeIs('user.hr-portal') ? 'bg-indigo-100 font-bold' : '' }}">
            <i class="fas fa-users-cog mr-2"></i> HR Portal
        </a>
        @endif

        <!-- Admin Access Only Menu -->
        @if($user && $user->hasRole('admin') && $user->roles->count() === 1)

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @mouseenter="open = true" @mouseleave="open = false"
                class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('admin.positions.*') || request()->routeIs('admin.departments.*') || request()->routeIs('admin.events.*') || request()->routeIs('admin.email-recipients.*') || request()->routeIs('admin.email-templates.*') ? 'bg-gray-100 font-bold' : '' }}">
                <i class="fas fa-table mr-2"></i> Admin Management
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
                <!-- Facilities Menu -->
                <a href="{{ route('admin.arbitration-templates.index') }}"
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('admin.arbitration-templates.*') ? 'bg-gray-100 font-bold' : '' }}">
                    <i class="fas fa-file-contract mr-2"></i> Arbitration Templates
                </a>
                <a href="{{ route('admin.scheduled-report-runs.index') }}"
                   class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('admin.scheduled-report-runs.*') ? 'bg-gray-100 font-bold' : '' }}">
                   <i class="fas fa-file-alt mr-2"></i> Scheduled Report Runs
                </a>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @mouseenter="open = true" @mouseleave="open = false"
                        class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('admin.facilities.*') ? 'bg-gray-100 font-bold' : '' }}">
                        <i class="fas fa-building mr-2"></i> Facilities Mgmnt
                        <svg class="ml-auto h-4 w-4 text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
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
                            <i class="fas fa-list mr-2"></i> Manage All
                        </a>
                        <hr class="my-1">
                        <div style="max-height: 400px; overflow-y: auto;">
                            @php
                            $facilityList = [];
                            if (isset($facilities)) {
                                $facilityList = is_array($facilities) ? collect($facilities)->sortBy('name') : $facilities->sortBy('name');
                            }
                            @endphp
                            <div style="overflow: visible; position: relative;">
                                @foreach($facilityList as $facility)
                                @if(!empty($facility->slug))
                                <div x-data="{ subOpen: false }" class="relative group" style="overflow: visible;"
                                    @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                    @php
                                    $abbr = $facility->name;
                                    if (\Illuminate\Support\Str::contains($abbr, 'Driftwood') &&
                                    \Illuminate\Support\Str::contains($abbr, 'Hayward')) {
                                    $abbr = 'Driftwood HCC - Hywd';
                                    } elseif (\Illuminate\Support\Str::contains($abbr, 'Driftwood') &&
                                    \Illuminate\Support\Str::contains($abbr, 'Santa Cruz'))
                                    {
                                    $abbr = 'Driftwood HCC - SCruz';
                                    } elseif (\Illuminate\Support\Str::contains($abbr, 'Glendale Transitional Care
                                    Center'))
                                    {
                                    $abbr = 'Glendale TCC';
                                    } else {
                                    $abbr = str_replace('Health and Rehabilitation Center', 'HRC', $abbr);
                                    $abbr = str_replace('Health Care and Rehabilitation Center', 'HRC', $abbr);
                                    $abbr = str_replace('Health Care Center', 'HCC', $abbr);
                                    $abbr = str_replace('Healthcare Center', 'HCC', $abbr);
                                    }
                                    @endphp
                                    <a href="{{ route('facility.public', ['facility' => $facility->slug]) }}"
                                        target="_blank"
                                        class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-hospital mr-2"></i> {{ $abbr }}
                                    </a>
                                    @if($user && $user->hasRole('admin') && $user->roles->count() === 1)
                                    <a href="{{ route('admin.facility.dashboard', $facility) }}"
                                        class="flex items-center w-full px-4 py-2 text-blue-700 hover:bg-blue-50">
                                        <i class="fas fa-tachometer-alt mr-2"></i> Admin Dashboard
                                    </a>
                                    @endif
                                </div>
                                @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Users Management -->
                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('admin.users.*') ? 'bg-gray-100 font-bold' : '' }}">
                    <i class="fas fa-users mr-2"></i> Users Mgmnt
                </a>
                <!-- Role & Permission Management -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @mouseenter="open = true" @mouseleave="open = false"
                        class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') || request()->routeIs('admin.role-assignments.*') ? 'bg-gray-100 font-bold' : '' }}">
                        <i class="fas fa-shield-alt mr-2"></i> Role & Permissions
                        <svg class="ml-auto h-4 w-4 text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
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
                        <a href="{{ route('admin.roles.index') }}"
                            class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.roles.*') ? 'bg-blue-50 text-blue-700 font-medium' : '' }}">
                            <i class="fas fa-user-tag mr-2 text-blue-600"></i> Manage Roles
                        </a>
                        <a href="{{ route('admin.permissions.index') }}"
                            class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.permissions.*') ? 'bg-green-50 text-green-700 font-medium' : '' }}">
                            <i class="fas fa-key mr-2 text-green-600"></i> Manage Permissions
                        </a>
                        <a href="{{ route('admin.role-assignments.index') }}"
                            class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.role-assignments.*') ? 'bg-purple-50 text-purple-700 font-medium' : '' }}">
                            <i class="fas fa-user-cog mr-2 text-purple-600"></i> Role Assignments
                        </a>
                        <hr class="my-1">
                        <a href="{{ route('admin.role-assignments.statistics') }}"
                            class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-chart-pie mr-2 text-orange-600"></i> Statistics
                        </a>
                    </div>
                </div>

                <!-- Manage Tables Menu -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @mouseenter="open = true" @mouseleave="open = false"
                        class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('admin.positions.*') || request()->routeIs('admin.departments.*') || request()->routeIs('admin.events.*') || request()->routeIs('admin.email-recipients.*') || request()->routeIs('admin.email-templates.*') ? 'bg-gray-100 font-bold' : '' }}">
                        <i class="fas fa-table mr-2"></i> Manage Tables
                        <svg class="ml-auto h-4 w-4 text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
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
                        <a href="{{ route('admin.positions.index') }}"
                            class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.positions.*') ? 'bg-blue-50 text-blue-700 font-medium' : '' }}">
                            <i class="fas fa-briefcase mr-2 text-blue-600"></i> Positions
                        </a>
                        <a href="{{ route('admin.departments.index') }}"
                            class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.departments.*') ? 'bg-green-50 text-green-700 font-medium' : '' }}">
                            <i class="fas fa-sitemap mr-2 text-green-600"></i> Departments
                        </a>
                        <a href="{{ route('admin.events.index') }}"
                            class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.events.*') ? 'bg-purple-50 text-purple-700 font-medium' : '' }}">
                            <i class="fas fa-calendar mr-2 text-purple-600"></i> Events
                        </a>
                        <a href="{{ route('admin.email-recipients.index') }}"
                            class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.email-recipients.*') ? 'bg-orange-50 text-orange-700 font-medium' : '' }}">
                            <i class="fas fa-envelope mr-2 text-orange-600"></i> Email Recipients
                        </a>
                        <a href="{{ route('admin.email-templates.index') }}"
                            class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.email-templates.*') ? 'bg-amber-50 text-amber-700 font-medium' : '' }}">
                            <i class="fas fa-envelope-open-text mr-2 text-amber-600"></i> Email Templates
                        </a>
                    </div>
                </div>

                <!-- HIPAA Compliance Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @mouseenter="open = true" @mouseleave="open = false"
                        class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('admin.baa-registry.*') || request()->routeIs('admin.hipaa-checklist.*') ? 'bg-gray-100 font-bold' : '' }}">
                        <i class="fas fa-shield-alt mr-2"></i> HIPAA Compliance
                        <svg class="ml-auto h-4 w-4 text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
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
                        <a href="{{ route('admin.baa-registry.index') }}"
                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-contract mr-2"></i> BAA Vendor Registry
                        </a>
                        <a href="{{ route('admin.hipaa-checklist.index') }}"
                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-clipboard-check mr-2"></i> HIPAA Checklist (Facility)
                        </a>
                    </div>
                </div>

                <!-- Security Monitoring -->
                <a href="{{ route('admin.security.dashboard') }}"
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('admin.security.*') ? 'bg-gray-100 font-bold' : '' }}">
                    <i class="fas fa-shield-alt mr-2"></i> Security Monitoring
                </a>
            </div>
        </div>
        @endif

        @if($user && $user->hasRole(['admin','hrrd','facility-admin','facility-dsd']))
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
                @php
                $firstFacility = \App\Models\Facility::first();
                @endphp
                <a href="{{ $firstFacility ? route('admin.facilities.webcontents.careers', ['facility' => $firstFacility->id]) : '#' }}"
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
                <a href="{{ route('admin.inquiries.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
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
                <a href="{{ route('admin.email-templates.index') }}"
                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-envelope-open-text mr-2"></i> Email Templates
                </a>
                    <a href="{{ route('admin.scheduled-reports.index') }}"
                        class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-clock mr-2"></i> Scheduled Reports
                    </a>
                <a href="{{ route('admin.communications.employee-email-mappings') }}"
                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-user-tie mr-2"></i> Employee Email Mappings
                </a>
            </div>
        </div>
        @endif
    </nav>
</aside>