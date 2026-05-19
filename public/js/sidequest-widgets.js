(function () {
    'use strict';

    // Map notification type → { icon (emoji/SVG string), colour class }
    const NOTIF_META = {
        PostReactionNotification:  { icon: '👍', cls: 'sq-notif-reaction' },
        PostCommentNotification:   { icon: '💬', cls: 'sq-notif-comment'  },
        CommentReplyNotification:  { icon: '↩️',  cls: 'sq-notif-reply'   },
        PostSharedNotification:    { icon: '🔁', cls: 'sq-notif-share'   },
        NewFollowerNotification:   { icon: '👤', cls: 'sq-notif-follow'  },
    };

    window.SideQuest.renderNotifications = function (root, notifications, unreadCount) {
        const countEl = root.querySelector('[data-notification-count]');
        const list    = root.querySelector('[data-notification-list]');
        const markAll = root.querySelector('[data-mark-all-read]');
        const toggle  = root.querySelector('[data-notification-toggle]');
        if (!countEl || !list || !markAll) { return; }

        // Animate bell when unread count increases
        const prevCount = parseInt(root.dataset.prevUnread || '0', 10);
        if (unreadCount > prevCount && toggle) {
            toggle.classList.remove('sq-bell-shake');
            // force reflow so animation restarts
            void toggle.offsetWidth;
            toggle.classList.add('sq-bell-shake');
            setTimeout(() => toggle.classList.remove('sq-bell-shake'), 800);
        }
        root.dataset.prevUnread = String(unreadCount);

        countEl.hidden      = unreadCount < 1;
        countEl.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
        markAll.hidden      = unreadCount < 1;

        if (!notifications.length) {
            list.innerHTML = '<p class="sq-muted" style="padding:.75rem 1rem;">No notifications yet.</p>';
            return;
        }

        list.innerHTML = notifications.map((n) => {
            const meta    = NOTIF_META[n.type] || { icon: '🔔', cls: '' };
            const isUnread = !n.read_at;
            const message  = n.data?.message || 'New notification';
            const time     = n.created_at || '';

            // Build destination URL based on notification type
            let destinationUrl = '';
            if (n.type === 'NewFollowerNotification' && n.data?.follower_username) {
                destinationUrl = window.SideQuest.appUrl(`u/${encodeURIComponent(n.data.follower_username)}`);
            } else if (n.data?.post_id) {
                destinationUrl = window.SideQuest.appUrl(`posts/${n.data.post_id}`);
            }

            // Avatar: real profile picture or initials fallback
            const senderName = (n.sender_name || '').trim();
            // Derive initials from sender_name; if empty, grab first two words of message as backup
            const nameForInitials = senderName || (n.data?.message || '').replace(/\s+(commented|reacted|started|shared|replied).*/i, '').trim();
            const initials = nameForInitials
                .split(/\s+/)
                .filter(Boolean)
                .map(w => w[0].toUpperCase())
                .slice(0, 2)
                .join('') || '?';
            const avatarHtml = n.sender_avatar
                ? `<img src="${n.sender_avatar}" alt="${senderName}" class="sq-notif-avatar">`
                : `<span class="sq-notif-avatar sq-notif-avatar-fb">${initials}</span>`;

            const inner = `
                <div class="sq-notification-item ${isUnread ? 'is-unread' : ''} ${meta.cls}" data-notification-id="${n.id}">
                    ${avatarHtml}
                    <div class="sq-notif-body">
                        <span class="sq-notif-message">${message}</span>
                        <span class="sq-notif-time sq-muted">${time}</span>
                    </div>
                    ${isUnread ? '<button type="button" class="sq-notif-mark-btn" data-mark-read aria-label="Mark as read">✓</button>' : ''}
                </div>`;

            return destinationUrl
                ? `<a href="${destinationUrl}" class="sq-notif-link">${inner}</a>`
                : inner;
        }).join('');
    };

    window.SideQuest.initNotifications = function () {
        document.querySelectorAll('[data-notification-root]').forEach((root) => {
            const toggle = root.querySelector('[data-notification-toggle]');
            const panel  = root.querySelector('[data-notification-panel]');
            if (!toggle || !panel) { return; }

            const fetchNotifications = async () => {
                try {
                    const res  = await fetch(window.SideQuest.appUrl('api/notifications'), { credentials: 'same-origin' });
                    const data = await res.json();
                    window.SideQuest.renderNotifications(root, data.notifications || [], data.unread_count || 0);
                } catch (_) { /* non-critical */ }
            };

            /* ── Auto-mark-as-read via IntersectionObserver ──────────────────
               When the panel is open, observe every unread notification item.
               After it's been ≥80% visible for 1 000 ms, silently POST a read
               receipt and remove the unread highlight.
            ─────────────────────────────────────────────────────────────────── */
            const seenTimers  = new Map();   // notificationId → timeoutId
            const readPending = new Set();   // notificationIds already queued

            const seenObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    const item = entry.target;
                    const id   = item.dataset.notificationId;
                    if (!id || readPending.has(id)) { return; }

                    if (entry.isIntersecting) {
                        // Start 1-second timer
                        if (!seenTimers.has(id)) {
                            const t = setTimeout(async () => {
                                readPending.add(id);
                                seenTimers.delete(id);
                                seenObserver.unobserve(item);

                                // Optimistic UI: remove unread state instantly
                                item.classList.remove('is-unread');
                                item.querySelector('[data-mark-read]')?.remove();

                                // Persist to server (fire-and-forget)
                                window.SideQuest.postWithCsrf(
                                    window.SideQuest.appUrl(`notifications/${id}/read`)
                                ).then(fetchNotifications).catch(() => {});
                            }, 1000);
                            seenTimers.set(id, t);
                        }
                    } else {
                        // Left viewport before 1 s — cancel timer
                        clearTimeout(seenTimers.get(id));
                        seenTimers.delete(id);
                    }
                });
            }, { threshold: 0.8 });

            /** Attach the observer to every currently-unread item in the list */
            const observeUnread = () => {
                const list = root.querySelector('[data-notification-list]');
                if (!list) { return; }
                list.querySelectorAll('.sq-notification-item.is-unread[data-notification-id]').forEach((el) => {
                    if (!readPending.has(el.dataset.notificationId)) {
                        seenObserver.observe(el);
                    }
                });
            };

            // Initial load + 20-second polling
            fetchNotifications().then(() => {
                if (!panel.hidden) { observeUnread(); }
            });
            setInterval(fetchNotifications, 20000);

            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') { fetchNotifications(); }
            });

            toggle.addEventListener('click', () => {
                const willOpen = panel.hidden;
                panel.hidden = !panel.hidden;
                if (willOpen) {
                    fetchNotifications().then(observeUnread);
                } else {
                    // Panel closed — cancel all pending timers
                    seenTimers.forEach((t) => clearTimeout(t));
                    seenTimers.clear();
                }
            });

            document.addEventListener('click', (e) => {
                if (!root.contains(e.target)) {
                    panel.hidden = true;
                    seenTimers.forEach((t) => clearTimeout(t));
                    seenTimers.clear();
                }
            });

            // Close on scroll (desktop) and touchmove (mobile)
            const closeOnScroll = () => {
                if (!panel.hidden) {
                    panel.hidden = true;
                    seenTimers.forEach((t) => clearTimeout(t));
                    seenTimers.clear();
                }
            };
            window.addEventListener('scroll', closeOnScroll, { passive: true, capture: true });
            window.addEventListener('touchmove', closeOnScroll, { passive: true });

            root.addEventListener('click', async (e) => {
                const markAll = e.target.closest('[data-mark-all-read]');
                if (markAll) {
                    await window.SideQuest.postWithCsrf(window.SideQuest.appUrl('notifications/read-all'));
                    fetchNotifications();
                    return;
                }
                // Individual manual ✓ button (still supported as fallback)
                const markRead = e.target.closest('[data-mark-read]');
                if (!markRead) { return; }
                const item = markRead.closest('[data-notification-id]');
                if (!item) { return; }
                await window.SideQuest.postWithCsrf(window.SideQuest.appUrl(`notifications/${item.dataset.notificationId}/read`));
                fetchNotifications();
            });
        });
    };

    window.SideQuest.initRecommendations = function () {
        const panel    = document.querySelector('[data-recommendations-panel]');
        const feedList = document.querySelector('#feed-post-list[data-has-reco-widget]');

        fetch(window.SideQuest.appUrl('api/recommendations'), { credentials: 'same-origin' })
            .then((r) => r.json())
            .then((data) => {
                const recommendations = data.recommendations || [];

                /* ── Sidebar panel (desktop) ─────────────────────────────── */
                if (panel) {
                    if (!recommendations.length) {
                        panel.innerHTML = '<div class="p-6 pb-3"><h3 class="text-sm font-semibold tracking-tight">People you may know</h3></div><div class="p-6 pt-0"><p class="text-sm text-muted-foreground">No recommendations yet.</p></div>';
                    } else {
                        panel.innerHTML = `
                            <div class="p-6 pb-3">
                                <h3 class="text-sm font-semibold tracking-tight">People you may know</h3>
                            </div>
                            <div class="p-6 pt-0 space-y-3">
                                ${recommendations.map((user) => `
                                    <div class="flex items-center gap-3">
                                        <a href="${window.SideQuest.appUrl('u/' + encodeURIComponent(user.username))}">
                                            <span class="relative flex h-9 w-9 shrink-0 overflow-hidden rounded-full">
                                                ${user.profile_picture_url
                                                    ? `<img src="${user.profile_picture_url}" alt="${user.full_name}" class="aspect-square h-full w-full object-cover">`
                                                    : `<span class="flex h-full w-full items-center justify-center rounded-full bg-muted text-xs font-semibold">${user.full_name.split(' ').map((p) => p[0] || '').join('').slice(0, 2).toUpperCase()}</span>`}
                                            </span>
                                        </a>
                                        <div class="flex-1 min-w-0">
                                            <a href="${window.SideQuest.appUrl('u/' + encodeURIComponent(user.username))}" class="text-sm font-medium hover:underline truncate block">${user.full_name}</a>
                                            <p class="text-xs text-muted-foreground truncate">${user.mutual_count > 0 ? user.mutual_count + ' mutual connection' + (user.mutual_count === 1 ? '' : 's') : '@' + user.username}</p>
                                        </div>
                                        <button type="button" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-xs font-medium transition-colors hover:bg-primary/90 bg-primary text-primary-foreground shadow h-7 px-3" data-follow-recommendation="${user.id}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:.25rem;flex-shrink:0;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                                            Follow
                                        </button>
                                    </div>
                                `).join('')}
                            </div>
                        `;
                    }

                    panel.addEventListener('click', async (e) => {
                        const button = e.target.closest('[data-follow-recommendation]');
                        if (!button || button.textContent.trim() === 'Following') { return; }
                        button.disabled = true;
                        try {
                            await window.SideQuest.postWithCsrf(window.SideQuest.appUrl('users/' + button.dataset.followRecommendation + '/follow'));
                            button.textContent = 'Following';
                            button.className   = 'inline-flex items-center justify-center whitespace-nowrap rounded-md text-xs font-medium transition-colors border border-input bg-background hover:bg-accent hover:text-accent-foreground shadow-sm h-7 px-3';
                            window.SideQuest.showFollowToast?.('Now following this user', 'success');
                            window.SideQuest.softRefreshFeed?.();
                        } catch (_) {
                            button.disabled = false;
                        }
                    });
                }

                /* ── Inline feed widget (mobile) ──────────────────────────
                   Injected after a random post (index 1–3) in the feed list.
                   Hidden on desktop via CSS so the sidebar handles it there.
                   We defer via rAF + setTimeout so PHP-rendered posts are in
                   the DOM before we try to find them.
                ─────────────────────────────────────────────────────────── */
                if (feedList && recommendations.length && feedList.dataset.hasRecoWidget !== 'true') {
                    feedList.dataset.hasRecoWidget = 'true';

                    const buildAvatar = (user) => user.profile_picture_url
                        ? '<img src="' + user.profile_picture_url + '" alt="' + user.full_name + '" class="sq-feed-reco-avatar">'
                        : '<span class="sq-feed-reco-avatar-fb">' + user.full_name.split(' ').map((p) => p[0] || '').join('').slice(0, 2).toUpperCase() + '</span>';

                    const buildSub = (user) => user.mutual_count > 0
                        ? user.mutual_count + ' mutual'
                        : '@' + user.username;

                    const cardsHtml = recommendations.map((user) =>
                        '<div class="sq-feed-reco-card">' +
                            '<a href="' + window.SideQuest.appUrl('u/' + encodeURIComponent(user.username)) + '">' + buildAvatar(user) + '</a>' +
                            '<a href="' + window.SideQuest.appUrl('u/' + encodeURIComponent(user.username)) + '" class="sq-feed-reco-name">' + user.full_name + '</a>' +
                            '<p class="sq-feed-reco-sub">' + buildSub(user) + '</p>' +
                            '<button type="button" class="sq-feed-reco-follow-btn" data-feed-reco-follow="' + user.id + '">Follow</button>' +
                        '</div>'
                    ).join('');

                    const widget = document.createElement('div');
                    widget.className = 'sq-feed-reco-widget';
                    widget.dataset.feedRecoWidget = '';
                    widget.innerHTML =
                        '<div class="sq-feed-reco-widget-header">' +
                            '<h3 class="sq-feed-reco-widget-title">People you may know</h3>' +
                        '</div>' +
                        '<div class="sq-feed-reco-scroll">' + cardsHtml + '</div>';

                    // Wait 350 ms so PHP-rendered post cards are fully painted
                    setTimeout(function () {
                        // Exclude the composer card and the empty-state card
                        var posts = Array.from(
                            feedList.querySelectorAll('.sq-post-card-v2')
                        ).filter(function (el) {
                            return !el.dataset.skeleton &&
                                   !el.dataset.feedEmptyState &&
                                   !el.classList.contains('sq-feed-composer');
                        });

                        if (posts.length > 1) {
                            // Insert after a random post between index 1-3
                            var idx = Math.min(
                                Math.floor(Math.random() * 3) + 1,
                                posts.length - 1
                            );
                            var anchor = posts[idx];
                            feedList.insertBefore(widget, anchor.nextSibling || null);
                        } else if (posts.length === 1) {
                            // Only one real post — insert right after it
                            feedList.insertBefore(widget, posts[0].nextSibling || null);
                        } else {
                            // No posts yet — prepend the widget so it's visible immediately
                            feedList.insertBefore(widget, feedList.firstChild);
                        }
                    }, 350);

                    // Follow handler for the feed widget
                    widget.addEventListener('click', async (e) => {
                        const btn = e.target.closest('[data-feed-reco-follow]');
                        if (!btn || btn.classList.contains('is-following')) { return; }
                        btn.disabled = true;
                        try {
                            await window.SideQuest.postWithCsrf(
                                window.SideQuest.appUrl('users/' + btn.dataset.feedRecoFollow + '/follow')
                            );
                            btn.textContent = 'Following';
                            btn.classList.add('is-following');
                            window.SideQuest.showFollowToast?.('Now following this user', 'success');
                            window.SideQuest.softRefreshFeed?.();
                        } catch (_) {
                            btn.disabled = false;
                        }
                    });
                }
            })
            .catch(() => {
                if (panel) {
                    panel.innerHTML = '<div class="p-6 pb-3"><h3 class="text-sm font-semibold tracking-tight">People you may know</h3></div><div class="p-6 pt-0"><p class="text-sm text-muted-foreground">Could not load recommendations.</p></div>';
                }
            });
    };
})();
