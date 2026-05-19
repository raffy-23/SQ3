(function () {
    'use strict';

    /* ═══════════════════════════════════════════════════════════════════════
       SideQuest Follow System — seamless follow/unfollow with optimistic UI
       ═══════════════════════════════════════════════════════════════════════ */

    /* ── Toast helper ────────────────────────────────────────────────────── */

    function showFollowToast(message, type) {
        var host = document.querySelector('[data-follow-toast-host]');
        if (!host) {
            host = document.createElement('div');
            host.dataset.followToastHost = 'true';
            host.className = 'sq-follow-toast-host';
            document.body.appendChild(host);
        }

        var toast = document.createElement('div');
        toast.className = 'sq-follow-toast' + (type === 'error' ? ' is-error' : '');
        toast.textContent = message;
        host.appendChild(toast);

        requestAnimationFrame(function () { toast.classList.add('is-visible'); });
        setTimeout(function () {
            toast.classList.remove('is-visible');
            setTimeout(function () { toast.remove(); }, 220);
        }, 2400);
    }

    /* ── Soft-refresh the feed ────────────────────────────────────────── */

    async function softRefreshFeed() {
        var feedList = document.getElementById('feed-post-list');
        if (!feedList) return;

        // Small delay to ensure the server has committed the follow
        await new Promise(function (r) { setTimeout(r, 300); });

        try {
            var feedUrl = window.SideQuest.appUrl('feed?partial=posts');
            var response = await fetch(feedUrl, { credentials: 'same-origin' });
            var data = await response.json();

            if (!data.html || !data.html.trim()) return;

            // Preserve the recommendation widget if it exists
            var recoWidget = feedList.querySelector('[data-feed-reco-widget]');
            var savedWidget = null;
            if (recoWidget) {
                savedWidget = recoWidget.cloneNode(true);
            }

            // Remove existing post cards and empty-state (keep skeletons out)
            var toRemove = feedList.querySelectorAll(
                '.sq-post-card-v2:not([data-skeleton]), [data-feed-empty-state], [data-feed-reco-widget]'
            );
            toRemove.forEach(function (el) { el.remove(); });

            // Insert fresh posts from server
            feedList.insertAdjacentHTML('beforeend', data.html);

            // Re-insert the recommendation widget after the 2nd post
            if (savedWidget) {
                var posts = Array.from(
                    feedList.querySelectorAll('.sq-post-card-v2:not([data-skeleton]):not([data-feed-empty-state])')
                );
                if (posts.length > 1) {
                    var idx = Math.min(2, posts.length - 1);
                    feedList.insertBefore(savedWidget, posts[idx].nextSibling || null);
                } else if (posts.length === 1) {
                    feedList.insertBefore(savedWidget, posts[0].nextSibling || null);
                } else {
                    feedList.appendChild(savedWidget);
                }
            }

            // Animate new cards in
            var newCards = feedList.querySelectorAll('.sq-post-card-v2:not([data-skeleton]):not([data-feed-empty-state])');
            newCards.forEach(function (card, i) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(-10px)';
                card.style.transition = 'opacity .3s ease, transform .3s ease';
                setTimeout(function () {
                    requestAnimationFrame(function () {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                        // Clean up inline styles after animation
                        setTimeout(function () {
                            card.style.removeProperty('opacity');
                            card.style.removeProperty('transform');
                            card.style.removeProperty('transition');
                        }, 350);
                    });
                }, i * 40); // staggered for a nice cascade effect
            });

            // Re-init all SideQuest interaction handlers on new HTML
            if (window.SideQuest.initPostMenus) window.SideQuest.initPostMenus();
            if (window.SideQuest.initReactionPickers) window.SideQuest.initReactionPickers();
            if (window.SideQuest.initAjaxForms) window.SideQuest.initAjaxForms();
            window.SideQuest.initPostInteractions?.();
            window.SideQuest.initCustomDatePickers?.();
            window.SideQuest.initVideoPauseOnScroll?.(feedList);
            window.SideQuest.syncFeedEmptyState?.(feedList);

            // Update next URL for infinite scroll
            var feedRoot = feedList.closest('[data-infinite-feed]');
            if (feedRoot) {
                feedRoot.dataset.nextUrl = data.nextPageUrl || '';
            }
        } catch (err) {
            console.error('[SideQuest feed refresh]', err);
        }
    }

    /* ── Profile page: follow / unfollow form ────────────────────────── */

    function initProfileFollowForms() {
        document.querySelectorAll('[data-follow-form]').forEach(function (form) {
            if (form.dataset.followBound === 'true') return;
            form.dataset.followBound = 'true';

            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                var btn = form.querySelector('[data-follow-btn]');
                if (!btn || btn.disabled) return;

                var userId = form.dataset.userId;
                var isFollowing = btn.dataset.following === 'true';

                // ── Optimistic UI ──────────────────────────────────────
                btn.disabled = true;
                var prevText = btn.textContent.trim();
                var prevClass = btn.className;
                var prevFollowing = btn.dataset.following;

                // Optimistic follower count
                var followerStat = document.querySelector('[data-stat-followers]');
                var prevFollowerCount = followerStat ? followerStat.textContent.trim() : null;

                if (isFollowing) {
                    btn.textContent = 'Follow';
                    btn.className = 'sq-btn sq-btn-primary';
                    btn.dataset.following = 'false';
                    var methodInput = form.querySelector('input[name="_method"]');
                    if (methodInput) methodInput.remove();
                    if (followerStat) {
                        followerStat.textContent = String(Math.max(0, (parseInt(followerStat.textContent, 10) || 0) - 1));
                    }
                } else {
                    btn.textContent = 'Unfollow';
                    btn.className = 'sq-btn sq-btn-secondary';
                    btn.dataset.following = 'true';
                    var hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = '_method';
                    hidden.value = 'DELETE';
                    form.appendChild(hidden);
                    if (followerStat) {
                        followerStat.textContent = String((parseInt(followerStat.textContent, 10) || 0) + 1);
                    }
                }

                try {
                    var url = window.SideQuest.appUrl('users/' + userId + '/follow');
                    var payload = isFollowing ? { _method: 'DELETE' } : {};
                    var response = await window.SideQuest.postWithCsrf(url, payload);
                    var data = await response.json();

                    if (data.success) {
                        // Apply server-authoritative counts
                        if (followerStat && data.followers_count !== undefined) {
                            followerStat.textContent = String(data.followers_count);
                        }
                        var followingStat = document.querySelector('[data-stat-following]');
                        if (followingStat && data.following_count !== undefined) {
                            followingStat.textContent = String(data.following_count);
                        }

                        showFollowToast(
                            data.is_following ? 'Now following this user' : 'Unfollowed this user',
                            'success'
                        );
                    } else {
                        revert();
                        showFollowToast(data.message || 'Something went wrong.', 'error');
                    }
                } catch (err) {
                    console.error('[SideQuest follow]', err);
                    revert();
                    showFollowToast('Could not update. Check your connection.', 'error');
                } finally {
                    btn.disabled = false;
                }

                function revert() {
                    btn.textContent = prevText;
                    btn.className = prevClass;
                    btn.dataset.following = prevFollowing;
                    if (followerStat && prevFollowerCount !== null) {
                        followerStat.textContent = prevFollowerCount;
                    }
                    if (isFollowing && !form.querySelector('input[name="_method"]')) {
                        var mi = document.createElement('input');
                        mi.type = 'hidden';
                        mi.name = '_method';
                        mi.value = 'DELETE';
                        form.appendChild(mi);
                    }
                    if (!isFollowing) {
                        var extra = form.querySelector('input[name="_method"]');
                        if (extra) extra.remove();
                    }
                }
            });
        });
    }

    /* ── Public API ──────────────────────────────────────────────────── */

    window.SideQuest.initFollowSystem = function () {
        initProfileFollowForms();
    };

    window.SideQuest.softRefreshFeed = softRefreshFeed;
    window.SideQuest.showFollowToast = showFollowToast;
})();
