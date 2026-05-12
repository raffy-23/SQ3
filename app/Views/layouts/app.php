<?php
$currentPath = $currentPath ?? '';
$navItems    = [
    [
        'label'  => 'Feed',
        'href'   => site_url('dashboard'),
        'active' => $currentPath === 'dashboard',
        'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>'
    ],
    [
        'label'  => 'Search',
        'href'   => site_url('search'),
        'active' => str_starts_with($currentPath, 'search'),
        'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>'
    ],
];
if (! empty($authUser['username'])) {
    $navItems[] = [
        'label'  => 'My Profile',
        'href'   => site_url('u/' . rawurlencode((string) $authUser['username'])),
        'active' => str_starts_with($currentPath, 'u/'),
        'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'
    ];
}
$bridgeCssPath = FCPATH . 'css/sidequest-bridge.css';
$bridgeCssUrl  = base_url('css/sidequest-bridge.css') . '?v=' . (is_file($bridgeCssPath) ? filemtime($bridgeCssPath) : time());
$sidequestJsPath = FCPATH . 'js/sidequest.js';
$sidequestJsUrl  = base_url('js/sidequest.js') . '?v=' . (is_file($sidequestJsPath) ? filemtime($sidequestJsPath) : time());
?>
<!DOCTYPE html>
<html lang="en" class="<?= ($appearance ?? 'system') === 'dark' ? 'dark' : '' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= esc(csrf_hash()) ?>">
    <meta name="csrf-header" content="X-CSRF-TOKEN">
    <meta name="csrf-token-name" content="<?= esc(csrf_token()) ?>">
    <meta name="app-base-url" content="<?= esc(rtrim(base_url(), '/')) ?>">

    <title><?= esc(($pageTitle ?? $appName) . ' · ' . $appName) ?></title>
    <link rel="icon" href="<?= esc(base_url('favicon-light.svg')) ?>" type="image/svg+xml" media="(prefers-color-scheme: light)">
    <link rel="icon" href="<?= esc(base_url('favicon-dark.svg')) ?>" type="image/svg+xml" media="(prefers-color-scheme: dark)">
    <script>
        (function () {
            const appearance = <?= json_encode($appearance ?? 'system', JSON_UNESCAPED_SLASHES) ?>;
            const root = document.documentElement;
            const sidebarStateMatch = document.cookie.match(/(?:^|;\s*)sidebar_state=(expanded|collapsed)/);
            root.dataset.sidebarState = sidebarStateMatch?.[1] === 'collapsed' ? 'collapsed' : 'expanded';

            if (appearance === 'dark') {

                root.classList.add('dark');
                return;
            }
            if (appearance === 'light') {
                root.classList.remove('dark');
                return;
            }
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
            if (prefersDark.matches) {
                root.classList.add('dark');
            }
            prefersDark.addEventListener('change', function (e) {
                if (appearance !== 'system') return;
                root.classList.toggle('dark', e.matches);
            });
        })();
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="<?= esc(base_url('build/assets/app-BwJKfC5G.css')) ?>">
    <link rel="stylesheet" href="<?= esc($bridgeCssUrl) ?>">

</head>
<body class="sq-page font-sans antialiased">
    <div class="sq-app-shell">
        <aside class="sq-sidebar">
            <div class="sq-sidebar-main">
                <a href="<?= esc(site_url('dashboard')) ?>" class="sq-brand sq-sidebar-brand" title="<?= esc($appName ?? 'SideQuest') ?>" aria-label="<?= esc($appName ?? 'SideQuest') ?>">
                    <?= view('partials/logo_mark', ['class' => 'sq-brand-mark']) ?>
                    <div class="sq-sidebar-brand-copy">
                        <div class="sq-brand-title"><?= esc($appName ?? 'SideQuest') ?></div>
                        <div class="sq-brand-subtitle">Connect, share, discover</div>
                    </div>
                </a>

                <nav class="sq-nav sq-sidebar-nav">
                    <?php foreach ($navItems as $item): ?>
                        <a href="<?= esc($item['href']) ?>" class="sq-nav-link sq-sidebar-nav-link <?= $item['active'] ? 'is-active' : '' ?>" title="<?= esc($item['label']) ?>" aria-label="<?= esc($item['label']) ?>">
                            <span class="sq-sidebar-nav-icon"><?= $item['icon'] ?></span>
                            <span class="sq-sidebar-label"><?= esc($item['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <?php if (! empty($authUser)): ?>
                <div class="sq-user-dropdown-root sq-sidebar-user" data-user-menu-root>
                    <button type="button" class="sq-nav-link sq-sidebar-user-trigger" data-user-menu-toggle aria-haspopup="menu" aria-expanded="false" aria-label="Account menu" title="Account menu">

                        <div class="sq-sidebar-user-summary">
                            <?php if (! empty($authUser['profile_picture_url'])): ?>
                                <img src="<?= esc($authUser['profile_picture_url']) ?>" alt="<?= esc($authUser['full_name']) ?>" class="sq-avatar sq-avatar-sm">
                            <?php else: ?>
                                <span class="sq-avatar sq-avatar-sm sq-avatar-fallback" style="font-size: 0.8rem;"><?= esc(user_initials($authUser)) ?></span>
                            <?php endif; ?>
                            <div class="sq-sidebar-user-meta">
                                <span class="sq-sidebar-user-name"><?= esc($authUser['full_name']) ?></span>
                                <span class="sq-muted sq-sidebar-user-handle">@<?= esc($authUser['username']) ?></span>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="sq-muted sq-sidebar-user-chevron"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                    </button>

                    <div class="sq-dropdown sq-sidebar-user-menu" data-user-menu-panel hidden>


                        <div class="sq-sidebar-user-menu-label">
                            <div class="sq-sidebar-user-menu-identity">
                                <?php if (! empty($authUser['profile_picture_url'])): ?>
                                    <img src="<?= esc($authUser['profile_picture_url']) ?>" alt="<?= esc($authUser['full_name']) ?>" class="sq-avatar sq-avatar-sm sq-sidebar-user-menu-avatar">
                                <?php else: ?>
                                    <span class="sq-avatar sq-avatar-sm sq-avatar-fallback sq-sidebar-user-menu-avatar"><?= esc(user_initials($authUser)) ?></span>
                                <?php endif; ?>
                                <div class="sq-sidebar-user-menu-meta">
                                    <span class="sq-sidebar-user-name"><?= esc($authUser['full_name']) ?></span>
                                    <span class="sq-sidebar-user-menu-email"><?= esc($authUser['email'] ?? '@' . $authUser['username']) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="sq-sidebar-user-menu-separator" aria-hidden="true"></div>
                        <div class="sq-sidebar-user-menu-group">
                            <a href="<?= esc(site_url('settings/profile')) ?>" class="sq-sidebar-user-menu-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                                <span>Settings</span>
                            </a>
                        </div>
                        <div class="sq-sidebar-user-menu-separator" aria-hidden="true"></div>
                        <form method="post" action="<?= esc(site_url('logout')) ?>" class="sq-sidebar-user-menu-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="sq-sidebar-user-menu-item sq-sidebar-user-menu-button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                                <span>Log out</span>
                            </button>
                        </form>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const root = document.querySelector('[data-user-menu-root]');
                        const toggle = root?.querySelector('[data-user-menu-toggle]');
                        const panel = root?.querySelector('[data-user-menu-panel]');
                        if (!(root instanceof HTMLElement) || !(toggle instanceof HTMLElement) || !(panel instanceof HTMLElement)) {
                            return;
                        }

                        const margin = 16;
                        const gap = 12;
                        const desktopBreakpoint = 920;

                        const closeMenu = () => {
                            panel.hidden = true;
                            panel.style.visibility = '';
                            toggle.classList.remove('is-active');
                            toggle.setAttribute('aria-expanded', 'false');
                        };

                        const positionMenu = () => {
                            const triggerRect = toggle.getBoundingClientRect();
                            const collapsed = document.documentElement.dataset.sidebarState === 'collapsed' && window.innerWidth > desktopBreakpoint;
                            const width = Math.min(224, window.innerWidth - (margin * 2));

                            panel.style.position = 'fixed';
                            panel.style.width = `${width}px`;
                            panel.style.maxWidth = `${window.innerWidth - (margin * 2)}px`;
                            panel.style.left = '0px';
                            panel.style.top = '0px';
                            panel.style.right = 'auto';
                            panel.style.bottom = 'auto';
                            panel.style.visibility = 'hidden';
                            panel.hidden = false;

                            const panelRect = panel.getBoundingClientRect();
                            let left = triggerRect.right - width;
                            let top = triggerRect.top - panelRect.height - gap;

                            if (collapsed) {
                                left = triggerRect.right + gap;
                                top = triggerRect.bottom - panelRect.height;

                                if (left + width > window.innerWidth - margin) {
                                    left = triggerRect.left - width - gap;
                                }
                            }

                            left = Math.min(Math.max(margin, left), window.innerWidth - width - margin);
                            top = Math.min(Math.max(margin, top), window.innerHeight - panelRect.height - margin);

                            panel.style.left = `${left}px`;
                            panel.style.top = `${top}px`;
                            panel.style.visibility = '';
                        };

                        toggle.addEventListener('click', (event) => {
                            event.stopPropagation();
                            const willOpen = panel.hidden;
                            closeMenu();
                            if (!willOpen) {
                                return;
                            }

                            positionMenu();
                            toggle.classList.add('is-active');
                            toggle.setAttribute('aria-expanded', 'true');
                        });

                        document.addEventListener('click', (event) => {
                            if (!root.contains(event.target) && !panel.contains(event.target)) {
                                closeMenu();
                            }
                        });

                        document.addEventListener('keydown', (event) => {
                            if (event.key === 'Escape') {
                                closeMenu();
                            }
                        });

                        window.addEventListener('resize', () => {
                            if (!panel.hidden) {
                                positionMenu();
                            }
                        });

                        document.addEventListener('scroll', () => {
                            if (!panel.hidden) {
                                positionMenu();
                            }
                        }, true);
                    });
                </script>
            <?php endif; ?>
        </aside>


        <main class="<?= esc(trim('sq-main ' . ($mainClass ?? ''))) ?>">
            <header class="<?= esc(trim('sq-topbar ' . ($topbarClass ?? ''))) ?>">
                <div class="sq-topbar-leading">
                    <button type="button" class="sq-icon-button sq-sidebar-trigger" data-sidebar-toggle aria-label="Toggle sidebar" title="Toggle sidebar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="sq-sidebar-trigger-icon sq-sidebar-trigger-icon-collapse"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/><path d="m16 15-3-3 3-3"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="sq-sidebar-trigger-icon sq-sidebar-trigger-icon-expand"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/><path d="m14 9 3 3-3 3"/></svg>
                    </button>
                    <div>
                        <div class="sq-topbar-label"><?= esc($topbarLabel ?? 'Social feed') ?></div>
                        <h1 class="sq-topbar-title"><?= esc($pageTitle ?? 'SideQuest') ?></h1>
                    </div>
                </div>

                <div class="sq-topbar-actions">
                    <a href="<?= esc(site_url('search')) ?>" class="sq-icon-button group" style="padding: 0; width: 2.25rem; height: 2.25rem;" aria-label="Search users">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.8; transition: opacity 0.2s;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </a>
                    <div class="sq-notifications" data-notification-root>
                        <button type="button" class="sq-icon-button group" style="position: relative; padding: 0; width: 2.25rem; height: 2.25rem;" data-notification-toggle aria-label="Notifications">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.8; transition: opacity 0.2s;"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                            <span class="sq-badge" data-notification-count hidden style="position: absolute; top: -2px; right: -2px; min-width: 1.1rem; height: 1.1rem; padding: 0 0.3rem; font-size: 0.65rem;">0</span>
                        </button>
                        <div class="sq-dropdown" data-notification-panel hidden>
                            <div class="sq-dropdown-header">
                                <span>Notifications</span>
                                <button type="button" class="sq-inline-button" data-mark-all-read hidden>Mark all read</button>
                            </div>
                            <div class="sq-dropdown-body" data-notification-list>
                                <p class="sq-muted">Loading notifications…</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="<?= esc(trim('sq-content ' . ($contentClass ?? ''))) ?>">
                <?= view('partials/flash', $pageData) ?>
                <?= view($contentView, $pageData) ?>
            </div>

        </main>
    </div>

    <dialog class="sq-dialog" id="reactors-dialog">
        <div class="sq-dialog-card">
            <div class="sq-dialog-header">
                <h2>Reactions</h2>
                <button type="button" class="sq-dialog-close-btn" data-dialog-close aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="sq-dialog-filters" data-reactors-filters></div>
            <div class="sq-dialog-body" data-reactors-body>
                <p class="sq-muted">Loading reactions…</p>
            </div>
        </div>
    </dialog>

    <dialog class="sq-dialog sq-composer-dialog" id="composer-dialog">
        <div class="sq-dialog-card">
            <div class="sq-dialog-header">
                <div>
                    <h2>Create Post</h2>
                    <p class="sq-dialog-subtitle">Share an update with your network</p>
                </div>
                <button type="button" class="sq-dialog-close-btn" data-composer-dialog-close aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="sq-dialog-body sq-composer-dialog-body">
                <form method="post" action="<?= esc(site_url('posts')) ?>" enctype="multipart/form-data" id="composer-form" data-post-composer>
                    <?= csrf_field() ?>

                    <div class="sq-composer-author-row">
                        <?php if (! empty($authUser['profile_picture_url'] ?? null)): ?>
                            <img src="<?= esc($authUser['profile_picture_url']) ?>" alt="<?= esc($authUser['full_name'] ?? 'You') ?>" class="sq-composer-avatar sq-composer-avatar-lg">
                        <?php else: ?>
                            <span class="sq-composer-avatar sq-composer-avatar-fallback sq-composer-avatar-lg"><?= esc(user_initials($authUser ?? null)) ?></span>
                        <?php endif; ?>
                        <div>
                            <strong class="sq-composer-author-name"><?= esc(($authUser['full_name'] ?? $authUser['username'] ?? 'You')) ?></strong>
                            <span class="sq-composer-author-meta">Posting publicly</span>
                        </div>
                    </div>

                    <textarea
                        id="post-content"
                        name="content"
                        placeholder="<?= esc('What\'s on your mind, ' . (($authUser['first_name'] ?? $authUser['username'] ?? 'there')) . '?') ?>"
                        class="sq-composer-editor"
                        maxlength="1000"
                        rows="6"
                        data-composer-input
                    ><?= esc(old('content') ?? '') ?></textarea>

                    <div class="sq-composer-preview" data-composer-preview hidden>
                        <div class="sq-composer-preview-media">
                            <img src="" alt="Selected media preview" class="sq-composer-preview-image" data-composer-preview-image hidden>
                            <video class="sq-composer-preview-video" controls preload="metadata" data-composer-preview-video hidden></video>
                        </div>
                        <div class="sq-composer-preview-footer">
                            <p class="sq-composer-file-meta" data-composer-file-meta>No media selected</p>
                            <button type="button" class="sq-composer-remove-media" data-composer-remove-media>Remove media</button>
                        </div>
                    </div>

                    <input id="post-media" type="file" name="media" accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime" class="sq-composer-file-input" data-composer-media>

                    <div class="sq-composer-dialog-actions">
                        <button type="button" class="sq-composer-add-media" data-media-trigger="all">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                            <span>Add photo/video</span>
                        </button>
                        <div class="sq-composer-submit-group">
                            <button type="button" class="sq-composer-cancel" data-composer-dialog-close>Cancel</button>
                            <button type="submit" class="sq-composer-submit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                                <span>Post</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </dialog>

    <script src="<?= esc($sidequestJsUrl) ?>" defer></script>

</body>
</html>
