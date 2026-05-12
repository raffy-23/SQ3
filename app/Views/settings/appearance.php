    <section class="sq-post-card-v2">
        <div style="padding: 1.25rem; font-size: .875rem;">
            <div class="sq-card-title" style="font-size: .875rem;">Appearance settings</div>
            <p class="text-sm text-muted-foreground">Choose how SideQuest should look while browsing the feed and profile pages.</p>
            <form method="post" action="<?= esc(site_url('appearance')) ?>" class="flex flex-col gap-6" style="margin-top: 1rem;">
                <?= csrf_field() ?>
                <div class="flex gap-3 items-center">
                    <?php foreach (['light' => 'Light mode', 'dark' => 'Dark mode', 'system' => 'Match system'] as $mode => $label): ?>
                        <button type="submit" name="appearance" value="<?= esc($mode) ?>" class="inline-flex items-center justify-center whitespace-nowrap rounded-full text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 <?= ($appearance ?? 'system') === $mode ? 'bg-primary/10 text-primary border-primary/20' : '' ?>">
                            <?= esc($label) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    </section>
