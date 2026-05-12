<?php $filters = ['all' => 'All', 'followers' => 'Followers', 'following' => 'Following']; ?>
<div class="sq-single-column">
    <div class="sq-post-card-v2 sq-search-card">
        <form method="get" action="<?= esc(site_url('search')) ?>" class="sq-search-form">
            <div class="sq-search-header">
                <h2 class="sq-search-title">Search users</h2>
                <input
                    id="search-query"
                    type="text"
                    name="q"
                    value="<?= esc($query ?? '') ?>"
                    class="sq-search-input"
                    placeholder="Search by name, username, or email…"
                    autocomplete="off"
                    data-live-search="<?= esc(site_url('search/live')) ?>"
                    data-live-search-target="#live-search-results"
                >
                <div id="live-search-results" class="sq-live-results" hidden></div>
            </div>
            <?php if (!empty($filters)): ?>
            <div class="sq-search-filters">
                <?php foreach ($filters as $key => $label): ?>
                    <button type="submit" name="filter" value="<?= esc($key) ?>" class="sq-chip <?= ($filter ?? '') === $key ? 'is-active' : '' ?>">
                        <?= esc($label) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($users['data'])): ?>
        <div class="sq-post-card-v2 sq-search-empty">
            <div class="sq-empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--sq-text-soft); margin: 0 auto 1rem;">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <h3><?= ! empty($query) ? 'No matching users found' : 'Start with a search' ?></h3>
                <p><?= ! empty($query) ? 'Try another name, username, or email filter.' : 'Use the field above to explore the community.' ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="sq-post-card-v2">
            <div class="sq-search-results-grid">
                <?php foreach ($users['data'] as $user): ?>
                    <div class="sq-recommendation-card">
                        <div class="sq-recommendation-body">
                            <a href="<?= esc(site_url('u/' . rawurlencode((string) $user['username']))) ?>" class="sq-recommendation-avatar-link">
                                <?php if (! empty($user['profile_picture_url'])): ?>
                                    <img src="<?= esc($user['profile_picture_url']) ?>" alt="<?= esc($user['full_name']) ?>" class="sq-recommendation-avatar">
                                <?php else: ?>
                                    <span class="sq-recommendation-avatar sq-avatar-fallback"><?= esc(user_initials($user)) ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="<?= esc(site_url('u/' . rawurlencode((string) $user['username']))) ?>" class="sq-recommendation-name">
                                <?= esc($user['full_name']) ?>
                            </a>
                            <p class="sq-recommendation-meta">
                                <?php if (!empty($user['mutual_count']) && $user['mutual_count'] > 0): ?>
                                    <span class="sq-recommendation-mutual">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        </svg>
                                        <?= esc($user['mutual_count']) ?> mutual
                                    </span>
                                <?php else: ?>
                                    <span class="sq-recommendation-username">@<?= esc($user['username']) ?></span>
                                <?php endif; ?>
                            </p>
                            <div class="sq-recommendation-actions">
                                <?php if (!empty($user['is_following'])): ?>
                                    <form method="post" action="<?= esc(site_url('users/' . $user['id'] . '/follow')) ?>" style="width: 100%;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="sq-recommendation-btn sq-recommendation-btn-secondary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="9" cy="7" r="4"></circle>
                                                <line x1="17" x2="22" y1="8" y2="8"></line>
                                            </svg>
                                            Unfollow
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" action="<?= esc(site_url('users/' . $user['id'] . '/follow')) ?>" style="width: 100%;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="sq-recommendation-btn sq-recommendation-btn-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="9" cy="7" r="4"></circle>
                                                <line x1="19" x2="19" y1="8" y2="14"></line>
                                                <line x1="22" x2="16" y1="11" y2="11"></line>
                                            </svg>
                                            Follow
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (($users['last_page'] ?? 1) > 1): ?>
            <div class="sq-post-card-v2 sq-search-pagination">
                <div class="sq-pagination">
                    <?php if (! empty($users['prev_page_url'])): ?>
                        <a href="<?= esc($users['prev_page_url']) ?>" class="sq-btn sq-btn-secondary">Previous</a>
                    <?php endif; ?>
                    <span class="sq-muted">Page <?= esc((string) $users['current_page']) ?> of <?= esc((string) $users['last_page']) ?></span>
                    <?php if (! empty($users['next_page_url'])): ?>
                        <a href="<?= esc($users['next_page_url']) ?>" class="sq-btn sq-btn-secondary">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
