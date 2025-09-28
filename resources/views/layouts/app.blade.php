<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Title --}}
    <title>{{ $metaTitle ?? ($title ?? 'Bio Pacific Facilities') }}</title>

    {{-- Basic SEO --}}
    <meta name="description" content="{{ $metaDescription ?? 'Compassionate senior care across California.' }}">
    @isset($metaKeywords)
    <meta name="keywords" content="{{ $metaKeywords }}">
    @endisset
    <meta name="robots" content="{{ $robots ?? 'index,follow' }}">

    {{-- Canonical --}}
    @isset($canonical)
    <link rel="canonical" href="{{ $canonical }}">
    @endisset

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $metaTitle ?? ($title ?? 'Bio Pacific Nursing Home Sites') }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Compassionate senior care across California.' }}">
    @isset($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
    @endisset
    @isset($canonical)
    <meta property="og:url" content="{{ $canonical }}">
    @endisset

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle ?? ($title ?? 'Bio Pacific Nursing Home Sites') }}">
    <meta name="twitter:description" content="{{ $metaDescription ?? 'Compassionate senior care across California.' }}">
    @isset($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
    @endisset

    {{-- Per-page extra head content (e.g., JSON-LD) --}}
    @stack('meta')

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 56px;
            left: 0;
            width: 280px;
            z-index: 1025;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            margin-left: -280px;
        }

        .sidebar-toggler {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .sidebar-toggler:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .sidebar-brand {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            border-radius: 10px;
            margin: 0.2rem 0;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            padding: 12px 16px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.3) !important;
            color: white !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .sidebar-footer {
            background: rgba(0, 0, 0, 0.1);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .content-wrapper {
            margin-left: 280px;
            margin-top: 56px;
            transition: margin-left 0.3s ease;
            min-height: calc(100vh - 56px);
        }

        .content-wrapper.expanded {
            margin-left: 0;
        }

        .main-content {
            background: #f8f9fa;
            min-height: calc(100vh - 56px);
        }

        .navbar {
            z-index: 1030;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1020;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -280px;
            }

            .sidebar.show {
                margin-left: 0;
            }

            .content-wrapper {
                margin-left: 0 !important;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        /* Additional improvements */
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
        }

        .dropdown-item {
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }

        /* Multi-level dropdown support */
        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu>.dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -6px;
            margin-left: 0.1rem;
            border-radius: 8px;
        }

        .dropdown-submenu>.dropdown-item:after {
            content: "\f105";
            font-family: 'FontAwesome';
            float: right;
        }
    </style>
</head>

<body>
    {{-- Top Navigation Bar --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <button class="btn sidebar-toggler text-white me-3" type="button" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-heartbeat me-2"></i>
                Bio-Pacific Facilities
            </a>

            {{-- Right side nav items --}}
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i>
                        Guest
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-sign-in-alt me-2"></i>Login</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    {{-- Sidebar Overlay for Mobile --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- Sidebar --}}
    <div class="sidebar" id="sidebar">
        {{-- Sidebar Brand --}}
        <div class="sidebar-brand p-3 text-center text-white">
            <i class="fas fa-heartbeat fa-2x mb-2"></i>
            <h5 class="mb-0">Bio Pacific</h5>
            <small class="text-white-50">Healthcare Management</small>
        </div>

        {{-- Navigation Menu --}}
        <nav class="navbar-nav px-3 py-2 flex-grow-1">
            {{-- Dashboard --}}
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('/') || request()->is('dashboard') ? 'active' : '' }}"
                    href="{{ url('/') }}">
                    <i class="fas fa-tachometer-alt me-3"></i>
                    Dashboard
                </a>
            </li>

            {{-- Facilities Dropdown --}}
            <li class="nav-item dropdown mb-1">
                <a class="nav-link dropdown-toggle {{ request()->is('facilities*') ? 'active' : '' }}" href="#"
                    id="facilitiesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-building me-3"></i>
                    Facilities
                </a>
                <ul class="dropdown-menu" aria-labelledby="facilitiesDropdown">
                    <li>
                        <a class="dropdown-item" href="{{ url('/facilities') }}">
                            <i class="fas fa-list me-2"></i> All
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    @php
                    $facilityList = isset($facilities) ? $facilities->sortBy('name') : [];
                    @endphp
                    @foreach($facilityList as $facility)
                    <li class="dropdown-submenu position-relative">
                        <a class="dropdown-item dropdown-toggle" href="#" id="facilityDropdown{{ $facility->id }}"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-hospital me-2"></i> {{ $facility->name }}
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="facilityDropdown{{ $facility->id }}">
                            <li><a class="dropdown-item" href="{{ route('facilities.show', $facility->slug) }}"><i
                                        class="fas fa-eye me-2"></i> Preview</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.facilities.edit', $facility->slug) }}"><i
                                        class="fas fa-edit me-2"></i> Edit</a></li>
                            @if($facility->domain)
                            <li><a class="dropdown-item" href="https://{{ $facility->domain }}" target="_blank"><i
                                        class="fas fa-external-link-alt me-2"></i> Visit Live</a></li>
                            @endif
                        </ul>
                    </li>
                    @endforeach
                </ul>
            </li>

            {{-- Settings --}}
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('settings*') ? 'active' : '' }}" href="{{ url('/settings') }}">
                    <i class="fas fa-cog me-3"></i>
                    Settings
                </a>
            </li>

            {{-- Audit Logs --}}
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->is('audit*') ? 'active' : '' }}" href="{{ url('/audit') }}">
                    <i class="fas fa-clipboard-list me-3"></i>
                    Audit Logs
                </a>
            </li>
        </nav>

        {{-- Sidebar Footer --}}
        <div class="sidebar-footer p-3 text-center text-white">
            <small class="text-white-50">
                <i class="fas fa-heart text-danger me-1"></i>
                Compassionate Care
            </small>
        </div>
    </div>

    {{-- Main Content Wrapper --}}
    <div class="content-wrapper" id="contentWrapper">
        <div class="main-content">
            <main class="py-4">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const contentWrapper = document.getElementById('contentWrapper');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            // Check if elements exist
            if (!sidebarToggle || !sidebar || !contentWrapper || !sidebarOverlay) {
                console.error('Required elements not found');
                return;
            }

            sidebarToggle.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    // Mobile behavior
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                    document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : 'auto';
                } else {
                    // Desktop behavior
                    sidebar.classList.toggle('collapsed');
                    contentWrapper.classList.toggle('expanded');
                }
            });

            // Close sidebar when clicking overlay (mobile)
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = 'auto';
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    document.body.style.overflow = 'auto';

                    // Reset desktop layout
                    if (sidebar.classList.contains('collapsed')) {
                        contentWrapper.classList.add('expanded');
                    } else {
                        contentWrapper.classList.remove('expanded');
                    }
                } else {
                    // Reset mobile layout
                    sidebar.classList.remove('collapsed');
                    contentWrapper.classList.remove('expanded');
                }
            });

            // Multi-level dropdown support for sidebar
            document.querySelectorAll('.dropdown-submenu > .dropdown-item').forEach(function(element) {
                element.addEventListener('mouseenter', function(e) {
                    if (window.innerWidth > 768) {
                        let submenu = element.nextElementSibling;
                        if (submenu && submenu.classList.contains('dropdown-menu')) {
                            submenu.classList.add('show');
                        }
                    }
                });
                element.addEventListener('mouseleave', function(e) {
                    if (window.innerWidth > 768) {
                        let submenu = element.nextElementSibling;
                        if (submenu && submenu.classList.contains('dropdown-menu')) {
                            submenu.classList.remove('show');
                        }
                    }
                });
                element.addEventListener('click', function(e) {
                    let submenu = element.nextElementSibling;
                    if (submenu && submenu.classList.contains('dropdown-menu')) {
                        submenu.classList.toggle('show');
                        e.preventDefault();
                    }
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                const dropdowns = document.querySelectorAll('.dropdown-menu.show');
                dropdowns.forEach(function(dropdown) {
                    if (!dropdown.contains(event.target) && !dropdown.previousElementSibling.contains(event.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            });
        });
    </script>
</body>

</html>