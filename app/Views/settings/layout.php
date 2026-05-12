<div class="sq-settings-layout" style="display: flex; gap: 3rem; max-width: 64rem; margin: 0 auto; align-items: flex-start; padding: 1rem 0;">
    <aside class="sq-settings-nav" style="width: 14rem; flex-shrink: 0; position: sticky; top: 5rem; align-self: flex-start;">
        <nav style="display: flex; flex-direction: column; gap: 0.25rem;">
            <a href="<?= esc(site_url('settings/profile')) ?>" class="sq-nav-link <?= $currentPath === 'settings/profile' || $currentPath === 'settings' ? 'is-active' : '' ?>" style="border-radius: 0.5rem; padding: 0.6rem 1rem; font-size: .875rem;">Profile</a>
            <a href="<?= esc(site_url('settings/security')) ?>" class="sq-nav-link <?= $currentPath === 'settings/security' ? 'is-active' : '' ?>" style="border-radius: 0.5rem; padding: 0.6rem 1rem; font-size: .875rem;">Security</a>
            <a href="<?= esc(site_url('settings/appearance')) ?>" class="sq-nav-link <?= $currentPath === 'settings/appearance' ? 'is-active' : '' ?>" style="border-radius: 0.5rem; padding: 0.6rem 1rem; font-size: .875rem;">Appearance</a>
        </nav>
    </aside>
    <div class="sq-settings-content" style="flex: 1; display: flex; flex-direction: column; gap: 1.5rem; max-width: 48rem;">
        <?= view($settingsView, $pageData) ?>
    </div>
</div>
