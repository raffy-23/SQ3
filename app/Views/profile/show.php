<?php
$activeTab      = $activeTab ?? 'posts';
$isOwn          = ! empty($isOwn);
$profileBaseUrl = site_url('u/' . rawurlencode((string) ($profileUser['username'] ?? '')));
$feedTitle      = $activeTab === 'saved'
    ? 'Saved posts'
    : ($isOwn ? 'My posts' : 'Recent posts');
$feedKicker     = $activeTab === 'saved' ? 'Bookmarks' : 'Timeline';
$feedTotal      = (int) ($feedCount ?? ($activeTab === 'saved' ? ($profileUser['saved_count'] ?? 0) : ($profileUser['posts_count'] ?? 0)));
$feedEndMessage = $activeTab === 'saved' ? 'No more saved posts to show.' : 'No more posts to show.';
?>
<div class="sq-profile-shell" data-profile-tab-active="<?= esc($activeTab) ?>">
    <section class="sq-post-card-v2 sq-profile-hero-card">
        <div class="sq-profile-cover<?= empty($profileUser['cover_photo_url']) ? ' is-fallback' : '' ?>">
            <?php if (! empty($profileUser['cover_photo_url'])): ?>
                <img
                    src="<?= esc($profileUser['cover_photo_url']) ?>"
                    alt="<?= esc($profileUser['full_name']) ?> cover photo"
                    class="sq-profile-cover-image"
                >
            <?php else: ?>
                <div class="sq-profile-cover-fallback" aria-hidden="true"></div>
            <?php endif; ?>

        </div>

        <div class="sq-profile-hero-body">
            <div class="sq-profile-identity-row">
                <div class="sq-profile-identity-main">
                    <div class="sq-profile-avatar-frame">
                        <?php if (! empty($profileUser['profile_picture_url'])): ?>
                            <img
                                src="<?= esc($profileUser['profile_picture_url']) ?>"
                                alt="<?= esc($profileUser['full_name']) ?>"
                                class="sq-profile-avatar-image"
                            >
                        <?php else: ?>
                            <span class="sq-profile-avatar-fallback">
                                <?= esc(user_initials($profileUser)) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="sq-profile-copy">
                        <h1 class="sq-profile-name"><?= esc($profileUser['full_name']) ?></h1>
                        <p class="sq-profile-handle">@<?= esc($profileUser['username']) ?></p>
                    </div>
                </div>

                <div class="sq-profile-hero-action">
                    <?php if ($isOwn): ?>
                        <a href="<?= esc(site_url('settings/profile')) ?>" class="sq-profile-edit-button">
                            <img src="<?= esc(base_url('edit-svgrepo-com.svg')) ?>" alt="" class="sq-profile-edit-icon-image" aria-hidden="true">
                            <span>Edit</span>
                        </a>
                    <?php else: ?>
                        <form method="post" action="<?= esc(site_url('users/' . (int) $profileUser['id'] . '/follow')) ?>">
                            <?= csrf_field() ?>
                            <?php if (! empty($isFollowing)): ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="sq-btn sq-btn-secondary">
                                    Unfollow
                                </button>
                            <?php else: ?>
                                <button type="submit" class="sq-btn sq-btn-primary">
                                    Follow
                                </button>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (! empty($profileUser['bio'])): ?>
                <p class="sq-profile-summary"><?= esc($profileUser['bio']) ?></p>
            <?php endif; ?>

            <div class="sq-profile-meta">
                <?php if (! empty($profileUser['created_at'])): ?>
                    <span class="sq-profile-meta-item">Joined <?= esc(human_time($profileUser['created_at'])) ?></span>
                <?php endif; ?>
                <span class="sq-profile-meta-item"><?= esc((string) ($profileUser['posts_count'] ?? 0)) ?> posts shared</span>
            </div>

            <div class="sq-profile-stat-grid">
                <div class="sq-profile-stat-card">
                    <p class="sq-profile-stat-label">Posts</p>
                    <p class="sq-profile-stat-value"><?= esc((string) ($profileUser['posts_count'] ?? 0)) ?></p>
                </div>
                <div class="sq-profile-stat-card">
                    <p class="sq-profile-stat-label">Followers</p>
                    <p class="sq-profile-stat-value"><?= esc((string) ($profileUser['followers_count'] ?? 0)) ?></p>
                </div>
                <div class="sq-profile-stat-card">
                    <p class="sq-profile-stat-label">Following</p>
                    <p class="sq-profile-stat-value"><?= esc((string) ($profileUser['following_count'] ?? 0)) ?></p>
                </div>
            </div>
        </div>
    </section>

    <section class="sq-post-card-v2 sq-profile-feed-intro">
        <div class="sq-profile-feed-heading">
            <div>
                <p class="sq-profile-section-kicker"><?= esc($feedKicker) ?></p>
                <h2 class="sq-profile-section-title"><?= esc($feedTitle) ?></h2>
            </div>
            <p class="sq-profile-section-meta"><?= esc((string) $feedTotal) ?> total</p>
        </div>

        <?php if ($isOwn): ?>
            <div class="sq-profile-feed-tabs">
                <a href="<?= esc($profileBaseUrl) ?>" class="sq-chip <?= $activeTab === 'posts' ? 'is-active' : '' ?>">My Posts</a>
                <a href="<?= esc($profileBaseUrl . '?tab=saved') ?>" class="sq-chip <?= $activeTab === 'saved' ? 'is-active' : '' ?>">Saved</a>
            </div>
        <?php endif; ?>
    </section>

    <section data-infinite-feed data-next-url="<?= esc((string) ($nextPageUrl ?? '')) ?>">
        <div id="profile-post-list" class="sq-profile-post-list" data-empty-message="<?= esc($emptyMessage ?? 'No posts yet.') ?>">
            <?= view('partials/profile_post_cards', [
                'posts'        => $posts ?? [],
                'authUser'     => $authUser ?? null,
                'emptyMessage' => $emptyMessage ?? 'No posts yet.',
            ]) ?>
        </div>

        <div class="sq-profile-feed-status" data-feed-sentinel>
            <?php if (! empty($nextPageUrl)): ?>
                <div class="flex items-center justify-center gap-2 text-sm text-muted-foreground">
                    <div class="h-4 w-4 animate-spin rounded-full border-2 border-primary border-t-transparent"></div>
                    Scroll to load more posts...
                </div>
            <?php else: ?>
                <p class="text-sm text-muted-foreground"><?= esc($feedEndMessage) ?></p>
            <?php endif; ?>
        </div>
    </section>
</div>
