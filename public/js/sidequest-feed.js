(function () {
    'use strict';

    /* ─────────────────────────────────────────────────────────────────────────
       Skeleton card HTML — mirrors the visual structure of .sq-post-card-v2
       withMedia=true adds a 16:9 placeholder block (used for every 2nd card)
    ───────────────────────────────────────────────────────────────────────── */
    function buildSkeletonCard(withMedia) {
        return `
        <div class="sq-skeleton-card sq-post-card-v2" data-skeleton aria-hidden="true">
            <div class="sq-skeleton-header">
                <div class="sq-skeleton-avatar"></div>
                <div class="sq-skeleton-meta">
                    <div class="sq-skeleton-line is-name"></div>
                    <div class="sq-skeleton-line is-sub"></div>
                </div>
            </div>
            ${withMedia ? '<div class="sq-skeleton-media"></div>' : ''}
            <div style="display:flex;flex-direction:column;gap:.4rem;">
                <div class="sq-skeleton-line is-full"></div>
                <div class="sq-skeleton-line is-wide"></div>
                <div class="sq-skeleton-line is-mid"></div>
                <div class="sq-skeleton-line is-short"></div>
            </div>
            <div class="sq-skeleton-actions">
                <div class="sq-skeleton-action-btn"></div>
                <div class="sq-skeleton-action-btn"></div>
                <div class="sq-skeleton-action-btn"></div>
            </div>
        </div>`;
    }

    function buildSkeletons(count) {
        let html = '';
        for (let i = 0; i < count; i++) {
            html += buildSkeletonCard(i % 2 === 1); // every 2nd has a media block
        }
        return html;
    }

    function removeSkeletons(list) {
        list.querySelectorAll('[data-skeleton]').forEach((el) => el.remove());
    }

    /* ─────────────────────────────────────────────────────────────────────────
       Infinite scroll + skeleton loading
    ───────────────────────────────────────────────────────────────────────── */
    window.SideQuest.initInfiniteFeeds = function () {

        document.querySelectorAll('[data-infinite-feed]').forEach((root) => {
            const sentinel   = root.querySelector('[data-feed-sentinel]');
            const list       = root.querySelector('#feed-post-list, #profile-post-list');
            const scrollRoot = root.matches('[data-feed-scroll-root]') ? root : root.querySelector('[data-feed-scroll-root]');

            if (!sentinel || !list || root.dataset.bound === 'true') { return; }
            root.dataset.bound = 'true';

            let loading = false;

            const showSentinelLoading = () => {
                sentinel.innerHTML = `
                    <div class="sq-feed-status-loading">
                        <div class="sq-feed-spinner"></div>
                        <span>Loading more posts…</span>
                    </div>`;
            };

            const showSentinelCaughtUp = () => {
                sentinel.innerHTML = '<p class="sq-muted" style="padding:.5rem 0;">You\'re all caught up.</p>';
            };

            const showSentinelError = () => {
                sentinel.innerHTML = `
                    <div class="sq-feed-status-loading">
                        <span style="color:var(--sq-danger,#e53e3e);">Could not load more posts.</span>
                        <button type="button" class="sq-inline-button" data-feed-retry>Retry</button>
                    </div>`;
            };

            const showSentinelScroll = () => {
                sentinel.innerHTML = `
                    <div class="sq-feed-status-loading">
                        <div class="sq-feed-spinner"></div>
                        <span>Scroll to load more…</span>
                    </div>`;
            };

            const loadMore = async () => {
                const nextUrl = root.dataset.nextUrl;
                if (!nextUrl || loading) { return; }

                loading = true;
                showSentinelLoading();

                // Inject skeletons while fetching
                list.insertAdjacentHTML('beforeend', buildSkeletons(3));

                try {
                    const separator = nextUrl.includes('?') ? '&' : '?';
                    const response  = await fetch(`${nextUrl}${separator}partial=posts`, { credentials: 'same-origin' });
                    const data      = await response.json();

                    // Remove skeletons before inserting real content
                    removeSkeletons(list);

                    if (data.html) {
                        list.insertAdjacentHTML('beforeend', data.html);
                        // Re-init any SideQuest components in the new HTML
                        window.SideQuest.initPostInteractions?.();
                        window.SideQuest.initCustomDatePickers?.();
                        window.SideQuest.initVideoPauseOnScroll?.(list);
                    }

                    root.dataset.nextUrl = data.nextPageUrl || '';

                    if (data.nextPageUrl) {
                        showSentinelScroll();
                    } else {
                        showSentinelCaughtUp();
                        // Stop observing — no more pages
                        observer.disconnect();
                    }
                } catch (err) {
                    console.error('[SideQuest feed]', err);
                    removeSkeletons(list);
                    showSentinelError();
                } finally {
                    loading = false;
                }
            };

            // Retry button inside sentinel
            sentinel.addEventListener('click', (e) => {
                if (e.target.closest('[data-feed-retry]')) { loadMore(); }
            });

            const observer = new IntersectionObserver((entries) => {
                if (entries[0]?.isIntersecting) { loadMore(); }
            }, {
                threshold: 0.15,
                root: scrollRoot instanceof HTMLElement ? scrollRoot : null,
            });

            observer.observe(sentinel);
        });
    };

    /* ─────────────────────────────────────────────────────────────────────────
       Empty-state helper (unchanged logic, cleaner code)
    ───────────────────────────────────────────────────────────────────────── */
    window.SideQuest.syncFeedEmptyState = function (list) {
        if (!(list instanceof HTMLElement)) { return; }

        const cards = list.querySelectorAll('.sq-post-card-v2:not([data-skeleton]):not([data-feed-empty-state])');

        if (cards.length > 0) {
            list.querySelector('[data-feed-empty-state]')?.remove();
        }
    };

    /* ─────────────────────────────────────────────────────────────────────────
       Auto-pause feed videos when they scroll out of view
    ───────────────────────────────────────────────────────────────────────── */
    const videoPauseObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            const video = entry.target;
            if (!entry.isIntersecting && !video.paused) {
                video.pause();
            }
        });
    }, {
        threshold: 0.2, // pause when less than 20% visible
    });

    window.SideQuest.initVideoPauseOnScroll = function (root) {
        const scope = root instanceof HTMLElement ? root : document;
        scope.querySelectorAll(
            '.sq-post-v2-video, .sq-post-v2-media-item video, .sq-post-v2-shared-embed video'
        ).forEach((video) => {
            if (!video.dataset.pauseObserved) {
                video.dataset.pauseObserved = '1';
                videoPauseObserver.observe(video);
            }
        });
    };

    // Boot on initial page load
    window.SideQuest.initVideoPauseOnScroll();
})();
