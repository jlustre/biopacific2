<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Alpine.js and Collapse plugin for x-collapse support (moved to end of body) -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @livewireScripts

    <!-- Alpine.js and Collapse plugin for x-collapse support (now loaded at end of body) -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Alpine.js debug: log when Alpine initializes
        document.addEventListener('alpine:init', () => {
            console.log('Alpine initialized');
        });
    </script>
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
    z-index: 1000;
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
    @auth
    @if(auth()->user()->hasRole('admin'))
    @include('layouts.sidebar')
    @else
    @include('layouts.sidebar_user')
    @endif
    @endauth
    @guest
    {{-- Optionally, show nothing or a guest sidebar --}}
    @endguest

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
    @stack('modals')
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

    // Alpine.js debug: log when Alpine initializes
    document.addEventListener('alpine:init', () => {
        console.log('Alpine initialized');
    });
    </script>

</body>

<!-- DEBUG: This is layouts/app.blade.php -->

</html>