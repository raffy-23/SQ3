<div class="sq-feed-layout">
    <section class="sq-feed-column">
        <div class="sq-feed-composer sq-post-card-v2">
            <div class="sq-composer-launcher">
                <?php if (! empty($authUser['profile_picture_url'])): ?>
                    <img src="<?= esc($authUser['profile_picture_url']) ?>" alt="<?= esc($authUser['full_name'] ?? 'You') ?>" class="sq-composer-avatar">
                <?php else: ?>
                    <span class="sq-composer-avatar sq-composer-avatar-fallback"><?= esc(user_initials($authUser ?? null)) ?></span>
                <?php endif; ?>

                <button type="button" class="sq-composer-trigger" data-composer-dialog-open>
                    <?= esc('What\'s on your mind, ' . (($authUser['first_name'] ?? $authUser['username'] ?? 'there')) . '?') ?>
                </button>

                <div class="sq-composer-actions" aria-label="Add media">
                    <button type="button" class="sq-composer-media-btn is-photo" data-composer-dialog-open data-media-type="image" aria-label="Add photo" title="Add photo">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                    </button>
                    <button type="button" class="sq-composer-media-btn is-video" data-composer-dialog-open data-media-type="video" aria-label="Add video" title="Add video">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M16.19 2H7.81C4.17 2 2 4.17 2 7.81V16.18C2 19.83 4.17 22 7.81 22H16.18C19.82 22 21.99 19.83 21.99 16.19V7.81C22 4.17 19.83 2 16.19 2ZM14.66 13.73L13.38 14.47L12.1 15.21C10.45 16.16 9.1 15.38 9.1 13.48V12V10.52C9.1 8.61 10.45 7.84 12.1 8.79L13.38 9.53L14.66 10.27C16.31 11.22 16.31 12.78 14.66 13.73Z"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="sq-feed-scroll" data-infinite-feed data-next-url="<?= esc((string) ($nextPageUrl ?? '')) ?>">
            <div id="feed-post-list" class="sq-feed-post-list" data-empty-message="Your feed is empty. Follow people to see their posts!" data-has-reco-widget="false">

                <?= view('partials/post_cards', ['posts' => $posts ?? [], 'authUser' => $authUser ?? null]) ?>

                <?php if (empty($posts)): ?>
                    <div class="sq-post-card-v2" data-feed-empty-state style="text-align:center;padding:2.5rem 1.5rem;">
                        <p class="sq-muted">Your feed is empty. Follow people to see their posts!</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="sq-feed-status" data-feed-sentinel>
                <?php if (! empty($nextPageUrl)): ?>
                    <div class="sq-feed-status-loading">
                        <div class="sq-feed-spinner"></div>
                        <span>Scroll to load more…</span>
                    </div>
                <?php else: ?>
                    <p class="sq-muted">You're all caught up.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <aside class="sq-feed-side-column">
        <div class="rounded-xl border bg-card text-card-foreground shadow" data-recommendations-panel>
            <div class="p-6">
                <h3 class="font-semibold tracking-tight text-sm mb-4">People you may know</h3>
                <p class="text-sm text-muted-foreground">Loading recommendations…</p>
            </div>
        </div>
    </aside>
</div>

