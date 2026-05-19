(function () {
    'use strict';

    window.SideQuest = window.SideQuest || {};

    var meta = function (name) {
        var el = document.querySelector('meta[name="' + name + '"]');
        return el ? el.getAttribute('content') || '' : '';
    };

    var appBase = (meta('app-base-url') || window.location.origin).replace(/\/$/, '');

    window.SideQuest.appUrl = function (path) {
        var cleanPath = String(path || '').replace(/^\//, '');
        return cleanPath ? appBase + '/' + cleanPath : appBase;
    };

    window.SideQuest.csrfToken = function () {
        return meta('csrf-token');
    };

    window.SideQuest.csrfHeader = function () {
        return meta('csrf-header') || 'X-CSRF-TOKEN';
    };

    window.SideQuest.postWithCsrf = async function (url, payload) {
        payload = payload || {};
        var headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest',
        };
        headers[window.SideQuest.csrfHeader()] = window.SideQuest.csrfToken();
        return fetch(url, {
            method: 'POST',
            headers: headers,
            body: new URLSearchParams(payload),
            credentials: 'same-origin',
        });
    };

    window.SideQuest.SIDEBAR_COOKIE_NAME = 'sidebar_state';
    window.SideQuest.SIDEBAR_COOKIE_MAX_AGE = 60 * 60 * 24 * 7;
    window.SideQuest.SIDEBAR_SHORTCUT_KEY = 'b';
    window.SideQuest.SIDEBAR_COLLAPSE_BREAKPOINT = 920;

    window.SideQuest.getSidebarState = function () {
        return document.documentElement.dataset.sidebarState === 'collapsed' ? 'collapsed' : 'expanded';
    };

    window.SideQuest.setSidebarState = function (state) {
        var nextState = state === 'collapsed' ? 'collapsed' : 'expanded';
        document.documentElement.dataset.sidebarState = nextState;
        document.cookie = window.SideQuest.SIDEBAR_COOKIE_NAME + '=' + nextState + '; path=/; max-age=' + window.SideQuest.SIDEBAR_COOKIE_MAX_AGE;
    };

    window.SideQuest.syncSidebarTriggers = function () {
        var collapsed = window.SideQuest.getSidebarState() === 'collapsed';
        document.querySelectorAll('[data-sidebar-toggle]').forEach(function (button) {
            button.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
            button.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            button.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        });
    };

    window.SideQuest.toggleSidebarState = function () {
        window.SideQuest.setSidebarState(window.SideQuest.getSidebarState() === 'collapsed' ? 'expanded' : 'collapsed');
        window.SideQuest.syncSidebarTriggers();
    };

    /**
     * sqConfirm({ title, body, ok }) → Promise<boolean>
     * Shows the custom #sq-confirm-dialog and resolves true/false.
     */
    window.SideQuest.sqConfirm = function (opts) {
        opts = opts || {};
        return new Promise(function (resolve) {
            var dialog = document.getElementById('sq-confirm-dialog');
            if (!(dialog instanceof HTMLDialogElement)) {
                resolve(window.confirm(opts.title || 'Are you sure?'));
                return;
            }

            var titleEl  = document.getElementById('sq-confirm-title');
            var bodyEl   = document.getElementById('sq-confirm-body');
            var okBtn    = document.getElementById('sq-confirm-ok');
            var cancelBtn = document.getElementById('sq-confirm-cancel');

            if (titleEl)  titleEl.textContent  = opts.title || 'Delete?';
            if (bodyEl)   bodyEl.textContent   = opts.body  || 'This action cannot be undone.';
            if (okBtn)    okBtn.textContent    = opts.ok    || 'Delete';

            dialog.showModal();
            document.body.style.overflow = 'hidden';

            function finish(result) {
                dialog.close();
                document.body.style.overflow = '';
                okBtn.removeEventListener('click', onOk);
                cancelBtn.removeEventListener('click', onCancel);
                dialog.removeEventListener('cancel', onCancel);
                resolve(result);
            }

            function onOk()     { finish(true);  }
            function onCancel() { finish(false); }

            okBtn.addEventListener('click', onOk);
            cancelBtn.addEventListener('click', onCancel);
            dialog.addEventListener('cancel', onCancel);   // Escape key
        });
    };
})();
