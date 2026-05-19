(function () {
    'use strict';

    window.SideQuest.initSidebar = function () {
        var root = document.documentElement;
        var sidebar = document.querySelector('.sq-sidebar');
        var breakpoint = window.SideQuest.SIDEBAR_COLLAPSE_BREAKPOINT;
        var overlay = document.querySelector('[data-sidebar-overlay]');

        if (!(sidebar instanceof HTMLElement)) {
            return;
        }

        if (!(overlay instanceof HTMLElement)) {
            overlay = document.createElement('div');
            overlay.className = 'sq-sidebar-overlay';
            overlay.dataset.sidebarOverlay = 'true';
            document.body.appendChild(overlay);
        }

        var isMobileViewport = function () {
            return window.innerWidth <= breakpoint;
        };

        var isMobileSidebarOpen = function () {
            return root.dataset.mobileSidebar === 'open';
        };

        var syncMobileSidebarState = function (open) {
            root.dataset.mobileSidebar = open ? 'open' : 'closed';
            document.body.style.overflow = open && isMobileViewport() ? 'hidden' : '';
            document.querySelectorAll('[data-sidebar-toggle]').forEach(function (button) {
                button.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        };

        var closeMobileSidebar = function () {
            syncMobileSidebarState(false);
        };

        if (!root.dataset.mobileSidebar) {
            syncMobileSidebarState(false);
        }

        window.SideQuest.syncSidebarTriggers();

        document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
            if (button.dataset.bound === 'true') {
                return;
            }

            button.dataset.bound = 'true';
            button.addEventListener('click', () => {
                if (isMobileViewport()) {
                    syncMobileSidebarState(!isMobileSidebarOpen());
                    return;
                }

                window.SideQuest.toggleSidebarState();
            });
        });

        if (overlay.dataset.bound !== 'true') {
            overlay.dataset.bound = 'true';
            overlay.addEventListener('click', closeMobileSidebar);
        }

        if (sidebar.dataset.mobileCloseBound !== 'true') {
            sidebar.dataset.mobileCloseBound = 'true';
            sidebar.addEventListener('click', function (event) {
                if (!isMobileViewport()) {
                    return;
                }

                if (event.target.closest('.sq-sidebar-nav-link, .sq-sidebar-user-menu-item')) {
                    closeMobileSidebar();
                }
            });
        }

        if (document.body.dataset.sidebarMobileEscBound !== 'true') {
            document.body.dataset.sidebarMobileEscBound = 'true';
            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && isMobileSidebarOpen()) {
                    closeMobileSidebar();
                    return;
                }

                if (event.key.toLowerCase() !== window.SideQuest.SIDEBAR_SHORTCUT_KEY || (!event.ctrlKey && !event.metaKey)) {
                    return;
                }

                if (isMobileViewport()) {
                    return;
                }

                event.preventDefault();
                window.SideQuest.toggleSidebarState();
            });
        }

        if (window.SideQuest.sidebarResizeBound !== true) {
            window.SideQuest.sidebarResizeBound = true;
            window.addEventListener('resize', function () {
                if (!isMobileViewport()) {
                    closeMobileSidebar();
                }
            });
        }

        if (window.SideQuest.sidebarClickAwayBound !== true) {
            window.SideQuest.sidebarClickAwayBound = true;
            document.addEventListener('click', function (event) {
                if (!isMobileViewport() || !isMobileSidebarOpen()) {
                    return;
                }

                var toggle = event.target.closest('[data-sidebar-toggle]');
                if (toggle || sidebar.contains(event.target)) {
                    return;
                }

                closeMobileSidebar();
            });
        }

    };
})();
