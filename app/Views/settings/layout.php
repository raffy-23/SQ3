<?php
$tabs = [
    ['label' => 'Profile',    'url' => site_url('settings/profile'),    'active' => ($currentPath === 'settings/profile' || $currentPath === 'settings')],
    ['label' => 'Security',   'url' => site_url('settings/security'),   'active' => ($currentPath === 'settings/security')],
    ['label' => 'Appearance', 'url' => site_url('settings/appearance'), 'active' => ($currentPath === 'settings/appearance')],
];
?>

<!-- ── Mobile tab bar (hidden on desktop via CSS) ──────────────────────── -->
<div class="sq-settings-tabbar">
    <?php foreach ($tabs as $tab): ?>
        <a
            href="<?= esc($tab['url']) ?>"
            class="sq-settings-tab<?= $tab['active'] ? ' is-active' : '' ?>"
            <?= $tab['active'] ? 'aria-current="page"' : '' ?>
        >
            <?= esc($tab['label']) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- ── Main layout ────────────────────────────────────────────────────── -->
<div class="sq-settings-layout">

    <!-- Sidebar nav (desktop only) -->
    <aside class="sq-settings-sidebar">
        <nav class="sq-settings-sidebar-nav">
            <?php foreach ($tabs as $tab): ?>
                <a
                    href="<?= esc($tab['url']) ?>"
                    class="sq-nav-link<?= $tab['active'] ? ' is-active' : '' ?>"
                >
                    <?= esc($tab['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- Page content -->
    <div class="sq-settings-content">
        <?= view($settingsView, $pageData) ?>
    </div>

</div>
