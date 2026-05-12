(() => {
    const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.content ?? '';
    const appBase = (meta('app-base-url') || window.location.origin).replace(/\/$/, '');
    const appUrl = (path = '') => {
        const cleanPath = String(path).replace(/^\//, '');
        return cleanPath ? `${appBase}/${cleanPath}` : appBase;
    };

    const csrfToken = () => meta('csrf-token');
    const csrfHeader = () => meta('csrf-header') || 'X-CSRF-TOKEN';

    const SIDEBAR_COOKIE_NAME = 'sidebar_state';
    const SIDEBAR_COOKIE_MAX_AGE = 60 * 60 * 24 * 7;
    const SIDEBAR_SHORTCUT_KEY = 'b';
    const SIDEBAR_COLLAPSE_BREAKPOINT = 920;
    const postWithCsrf = async (url, payload = {}) => fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest',
            [csrfHeader()]: csrfToken(),
        },
        body: new URLSearchParams(payload),
        credentials: 'same-origin',
    });
    const getSidebarState = () => document.documentElement.dataset.sidebarState === 'collapsed' ? 'collapsed' : 'expanded';
    const setSidebarState = (state) => {
        const nextState = state === 'collapsed' ? 'collapsed' : 'expanded';
        document.documentElement.dataset.sidebarState = nextState;
        document.cookie = `${SIDEBAR_COOKIE_NAME}=${nextState}; path=/; max-age=${SIDEBAR_COOKIE_MAX_AGE}`;
    };
    const syncSidebarTriggers = () => {
        const collapsed = getSidebarState() === 'collapsed';
        document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
            button.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
            button.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            button.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        });
    };
    const toggleSidebarState = () => {
        setSidebarState(getSidebarState() === 'collapsed' ? 'expanded' : 'collapsed');
        syncSidebarTriggers();
    };
    const initSidebar = () => {
        syncSidebarTriggers();

        document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
            if (button.dataset.bound === 'true') {
                return;
            }

            button.dataset.bound = 'true';
            button.addEventListener('click', () => {
                if (window.innerWidth <= SIDEBAR_COLLAPSE_BREAKPOINT) {
                    return;
                }

                toggleSidebarState();
            });
        });

        window.addEventListener('keydown', (event) => {
            if (event.key.toLowerCase() !== SIDEBAR_SHORTCUT_KEY || (!event.ctrlKey && !event.metaKey)) {
                return;
            }

            if (window.innerWidth <= SIDEBAR_COLLAPSE_BREAKPOINT) {
                return;
            }

            event.preventDefault();
            toggleSidebarState();
        });
    };



    const initInfiniteFeeds = () => {

        document.querySelectorAll('[data-infinite-feed]').forEach((root) => {
            const sentinel = root.querySelector('[data-feed-sentinel]');
            const list = root.querySelector('#dashboard-post-list, #profile-post-list');
            const scrollRoot = root.matches('[data-feed-scroll-root]') ? root : root.querySelector('[data-feed-scroll-root]');

            if (!sentinel || !list || root.dataset.bound === 'true') {

                return;
            }

            root.dataset.bound = 'true';
            let loading = false;

            const loadMore = async () => {
                const nextUrl = root.dataset.nextUrl;
                if (!nextUrl || loading) {
                    return;
                }

                loading = true;
                sentinel.textContent = 'Loading more…';
                try {
                    const separator = nextUrl.includes('?') ? '&' : '?';
                    const response = await fetch(`${nextUrl}${separator}partial=posts`, { credentials: 'same-origin' });
                    const data = await response.json();
                    if (data.html) {
                        list.insertAdjacentHTML('beforeend', data.html);
                    }
                    root.dataset.nextUrl = data.nextPageUrl || '';
                    sentinel.textContent = data.nextPageUrl ? 'Scroll to load more posts' : 'You’re all caught up.';
                } catch (error) {
                    console.error(error);
                    sentinel.textContent = 'Could not load more posts.';
                } finally {
                    loading = false;
                }
            };

            const observer = new IntersectionObserver((entries) => {
                if (entries[0]?.isIntersecting) {
                    loadMore();
                }
            }, {
                threshold: 0.15,
                root: scrollRoot instanceof HTMLElement ? scrollRoot : null,
            });


            observer.observe(sentinel);
        });
    };

    const initReactionPickers = () => {
        const controlSelector = '[data-reaction-control], [data-comment-reaction-control]';
        const pickerState = new WeakMap();

        const stateFor = (control) => {
            if (!pickerState.has(control)) {
                pickerState.set(control, { showTimeout: null, hideTimeout: null });
            }

            return pickerState.get(control);
        };

        const showPicker = (control, immediate = false) => {
            const picker = control?.querySelector('[data-reaction-picker]');
            if (!(picker instanceof HTMLElement)) {
                return;
            }

            const state = stateFor(control);
            clearTimeout(state.hideTimeout);
            clearTimeout(state.showTimeout);

            if (immediate) {
                picker.hidden = false;
                return;
            }

            state.showTimeout = setTimeout(() => {
                picker.hidden = false;
            }, 380);
        };

        const hidePicker = (control) => {
            const picker = control?.querySelector('[data-reaction-picker]');
            if (!(picker instanceof HTMLElement)) {
                return;
            }

            const state = stateFor(control);
            clearTimeout(state.showTimeout);
            clearTimeout(state.hideTimeout);
            state.hideTimeout = setTimeout(() => {
                picker.hidden = true;
            }, 180);
        };

        document.addEventListener('click', (event) => {
            const shareButton = event.target.closest('[data-share-url]');
            if (shareButton) {
                navigator.clipboard.writeText(shareButton.dataset.shareUrl || '');
                const label = shareButton.querySelector('span:last-child');
                if (label) {
                    label.textContent = 'Copied';
                    setTimeout(() => {
                        label.textContent = 'Share';
                    }, 1200);
                }
            }

            const commentsToggle = event.target.closest('[data-comments-toggle]');
            if (commentsToggle) {
                const target = document.getElementById(commentsToggle.dataset.target || '');
                target?.classList.toggle('is-hidden');
            }
        });

        document.addEventListener('mouseover', (event) => {
            const control = event.target.closest(controlSelector);
            if (!control || control.contains(event.relatedTarget)) {
                return;
            }

            showPicker(control, false);
        });

        document.addEventListener('mouseout', (event) => {
            const control = event.target.closest(controlSelector);
            if (!control || control.contains(event.relatedTarget)) {
                return;
            }

            hidePicker(control);
        });

        document.addEventListener('focusin', (event) => {
            const control = event.target.closest(controlSelector);
            if (!control) {
                return;
            }

            showPicker(control, true);
        });

        document.addEventListener('focusout', (event) => {
            const control = event.target.closest(controlSelector);
            if (!control || control.contains(event.relatedTarget)) {
                return;
            }

            hidePicker(control);
        });
    };

    const initAjaxForms = () => {
        const reactionIconUrls = {
            like: appUrl('reactions/like.svg'),
            love: appUrl('reactions/love.svg'),
            haha: appUrl('reactions/haha.svg'),
            wow: appUrl('reactions/wow.svg'),
            sad: appUrl('reactions/sad.svg'),
            angry: appUrl('reactions/angry.svg'),
        };

        const reactionLabels = {
            like: 'Like', love: 'Love', haha: 'Haha',
            wow: 'Wow', sad: 'Sad', angry: 'Angry',
        };

        const summaryIcon = (type) =>
            `<img src="${reactionIconUrls[type] || ''}" alt="" class="sq-reaction-badge">`;

        const updateReactionUI = (card, data) => {
            const breakdown = data.reactions_breakdown || {};
            const count = data.reactions_count || 0;
            const current = data.current_user_reaction || null;

            // Update main reaction button
            const actionBtn = card.querySelector('.sq-post-v2-action-btn[title]');
            if (actionBtn) {
                if (current && reactionIconUrls[current]) {
                    actionBtn.innerHTML = `<img src="${reactionIconUrls[current]}" alt="" class="sq-post-v2-action-icon-reaction"><span class="sq-post-v2-action-label" data-reaction="${current}">${reactionLabels[current] || current}</span>`;
                    actionBtn.setAttribute('title', reactionLabels[current] || current);
                    actionBtn.setAttribute('aria-label', reactionLabels[current] || current);
                } else {
                    actionBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="sq-post-v2-action-icon"><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3m7-2V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14Z"/></svg><span class="sq-post-v2-action-label">Like</span>`;
                    actionBtn.setAttribute('title', 'Like');
                    actionBtn.setAttribute('aria-label', 'Like');
                }
            }

            // Also update the hidden form's type input for the main reaction button
            const mainForm = card.querySelector('[data-reaction-control] > form:first-of-type');
            if (mainForm) {
                const typeInput = mainForm.querySelector('input[name="type"]');
                if (typeInput) {
                    typeInput.value = current || 'like';
                }
            }

            // Update summary bar
            const summary = card.querySelector('.sq-post-v2-summary');
            if (count === 0) {
                if (summary) summary.remove();
                return;
            }

            const summaryReactions = card.querySelector('.sq-post-v2-summary-reactions');
            const types = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];

            if (summary && summaryReactions) {
                const iconsHtml = types
                    .filter((t) => breakdown[t] > 0)
                    .map(summaryIcon)
                    .join('');
                summaryReactions.innerHTML = `<span class="sq-post-v2-summary-icons">${iconsHtml}</span><span class="sq-post-v2-summary-count">${count}</span>`;
            } else if (count > 0) {
                // Summary bar doesn't exist yet but now we have reactions — build one
                const divider = card.querySelector('.sq-post-v2-divider');
                const iconsHtml = types
                    .filter((t) => breakdown[t] > 0)
                    .map(summaryIcon)
                    .join('');
                const newSummary = document.createElement('div');
                newSummary.className = 'sq-post-v2-summary';
                newSummary.innerHTML = `<div class="sq-post-v2-summary-left"><button type="button" class="sq-post-v2-summary-reactions" data-reactors-open data-url="${appUrl(`api/posts/${card.dataset.postId}/reactions`)}" title="View reactions" aria-label="View reactions"><span class="sq-post-v2-summary-icons">${iconsHtml}</span><span class="sq-post-v2-summary-count">${count}</span></button></div><div class="sq-post-v2-summary-right"></div>`;
                if (divider) {
                    divider.parentNode.insertBefore(newSummary, divider);
                } else {
                    card.appendChild(newSummary);
                }
            }
        };

        const updateCommentsCount = (card, count, panel) => {
            const panelId = panel ? panel.id : '';
            const summaryRight = card.querySelector('.sq-post-v2-summary-right');
            const summary = card.querySelector('.sq-post-v2-summary');

            // Update or create summary right
            if (count > 0) {
                const linkHtml = `<button type="button" class="sq-post-v2-summary-link" data-comments-toggle data-target="${panelId}">${count} comment${count !== 1 ? 's' : ''}</button>`;
                if (summaryRight) {
                    const existingLink = summaryRight.querySelector('[data-comments-toggle]');
                    if (existingLink) {
                        existingLink.outerHTML = linkHtml;
                    } else {
                        summaryRight.insertAdjacentHTML('beforeend', linkHtml);
                    }
                } else if (!summary) {
                    const divider = card.querySelector('.sq-post-v2-divider');
                    const newSummary = document.createElement('div');
                    newSummary.className = 'sq-post-v2-summary';
                    newSummary.innerHTML = `<div class="sq-post-v2-summary-left"></div><div class="sq-post-v2-summary-right">${linkHtml}</div>`;
                    if (divider) {
                        divider.parentNode.insertBefore(newSummary, divider);
                    } else {
                        card.appendChild(newSummary);
                    }
                } else {
                    summary.insertAdjacentHTML('beforeend', `<div class="sq-post-v2-summary-right">${linkHtml}</div>`);
                }
            } else if (summaryRight) {
                const existingLink = summaryRight.querySelector('[data-comments-toggle]');
                if (existingLink) {
                    existingLink.remove();
                }
            }
        };

        const pushCommentToast = (message) => {
            if (!message) {
                return;
            }

            let host = document.querySelector('[data-comment-toast-host]');
            if (!(host instanceof HTMLElement)) {
                host = document.createElement('div');
                host.dataset.commentToastHost = 'true';
                host.className = 'sq-comment-toast-host';
                document.body.appendChild(host);
            }

            const item = document.createElement('div');
            item.className = 'sq-comment-toast';
            item.textContent = message;
            host.appendChild(item);

            requestAnimationFrame(() => item.classList.add('is-visible'));
            setTimeout(() => {
                item.classList.remove('is-visible');
                setTimeout(() => item.remove(), 180);
            }, 2200);
        };

        const closeAllCommentMenus = () => {
            document.querySelectorAll('[data-comment-menu-root]').forEach((root) => {
                if (!(root instanceof HTMLElement)) {
                    return;
                }

                root.classList.remove('is-open');
                delete root.dataset.commentMenuPlacement;
                const toggle = root.querySelector('[data-comment-menu-toggle]');
                const panel = root.querySelector('[data-comment-menu-panel]');
                if (toggle instanceof HTMLElement) {
                    toggle.setAttribute('aria-expanded', 'false');
                }
                if (panel instanceof HTMLElement) {
                    panel.hidden = true;
                    panel.style.visibility = '';
                    panel.style.maxWidth = '';
                }
            });
        };

        const closeAllEditAndReplyShells = () => {
            document.querySelectorAll('[data-comment-edit-shell], [data-comment-reply-shell]').forEach((shell) => {
                if (shell instanceof HTMLElement) {
                    shell.hidden = true;
                }
            });
        };

        const positionCommentMenu = (root, panel) => {
            if (!(root instanceof HTMLElement) || !(panel instanceof HTMLElement)) {
                return;
            }

            const card = root.closest('.sq-post-card-v2');
            const cardRect = card instanceof HTMLElement ? card.getBoundingClientRect() : document.body.getBoundingClientRect();
            const rootRect = root.getBoundingClientRect();
            const gap = 8;
            const inset = 12;

            panel.hidden = false;
            panel.style.visibility = 'hidden';
            panel.style.maxWidth = '';
            root.dataset.commentMenuPlacement = 'right';

            const naturalWidth = Math.ceil(panel.getBoundingClientRect().width || panel.scrollWidth || 0) || 200;
            const availableRight = Math.max(0, Math.floor(cardRect.right - rootRect.right - gap - inset));
            const availableLeft = Math.max(0, Math.floor(rootRect.left - cardRect.left - gap - inset));
            const placeRight = naturalWidth <= availableRight || availableRight >= availableLeft;
            const chosenAvailable = placeRight ? availableRight : availableLeft;
            const fallbackWidth = Math.max(120, Math.floor(cardRect.width - (inset * 2)));

            root.dataset.commentMenuPlacement = placeRight ? 'right' : 'left';
            panel.style.maxWidth = `${Math.max(120, Math.min(naturalWidth, chosenAvailable || fallbackWidth))}px`;
            panel.style.visibility = '';
        };

        const updateCommentReactionUI = (row, data) => {
            if (!(row instanceof HTMLElement)) {
                return;
            }

            const breakdown = data.reactions_breakdown || {};
            const current = data.current_user_reaction || null;
            const displayType = data.reaction_display_type || current || null;
            const button = row.querySelector('[data-comment-reaction-button]');


            const label = row.querySelector('[data-comment-reaction-label]');
            const typeInput = row.querySelector('[data-comment-reaction-type]');
            let countNode = row.querySelector('[data-comment-reaction-count]');

            row.querySelectorAll('[data-comment-reaction-icon]').forEach((icon) => icon.remove());

            if (button instanceof HTMLElement) {
                button.classList.toggle('is-active', !!current);
                button.setAttribute('title', current ? (reactionLabels[current] || current) : 'Like');
                button.setAttribute('aria-label', current ? (reactionLabels[current] || current) : 'Like');
            }

            if (label instanceof HTMLElement) {
                label.textContent = current ? (reactionLabels[current] || current) : 'Like';
                if (current) {
                    label.dataset.reaction = current;
                } else {
                    delete label.dataset.reaction;
                }
            }

            if (typeInput instanceof HTMLInputElement) {
                typeInput.value = current || 'like';
            }

            const nextCount = Number(data.reactions_count || 0);
            if (nextCount < 1) {
                countNode?.remove();
                return;
            }

            if (!(countNode instanceof HTMLElement)) {
                countNode = document.createElement('span');
                countNode.className = 'sq-post-v2-comment-reaction-count';
                countNode.dataset.commentReactionCount = 'true';
                const meta = row.querySelector('.sq-post-v2-comment-meta');
                meta?.appendChild(countNode);
            }

            if (countNode instanceof HTMLElement) {
                countNode.innerHTML = '';
                const types = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];
                const icons = document.createElement('span');
                icons.className = 'sq-post-v2-comment-reaction-icons';
                icons.dataset.commentReactionCountIcons = 'true';

                types
                    .filter((type) => Number(breakdown[type] || 0) > 0)
                    .forEach((type) => {
                        const icon = document.createElement('img');
                        icon.src = reactionIconUrls[type];
                        icon.alt = '';
                        icon.className = 'sq-reaction-badge sq-post-v2-comment-reaction-count-icon';
                        icon.dataset.commentReactionCountIcon = 'true';
                        icons.appendChild(icon);
                    });

                if (!icons.children.length && displayType && reactionIconUrls[displayType]) {
                    const icon = document.createElement('img');
                    icon.src = reactionIconUrls[displayType];
                    icon.alt = '';
                    icon.className = 'sq-reaction-badge sq-post-v2-comment-reaction-count-icon';
                    icon.dataset.commentReactionCountIcon = 'true';
                    icons.appendChild(icon);
                }

                const value = document.createElement('span');
                value.dataset.commentReactionCountValue = 'true';
                value.textContent = String(nextCount);
                countNode.appendChild(value);

                countNode.appendChild(icons);


            }
        };


        const setCommentContent = (row, content) => {
            if (!(row instanceof HTMLElement)) {
                return;
            }

            const contentNode = row.querySelector('[data-comment-content]');
            if (contentNode instanceof HTMLElement) {
                contentNode.textContent = content;
            }

            const editField = row.querySelector('[data-comment-edit-field]');
            if (editField instanceof HTMLInputElement) {
                editField.value = content;
            }
        };

        const removeCommentRow = (row, count, card) => {
            if (!(row instanceof HTMLElement) || !(card instanceof HTMLElement)) {
                return;
            }

            const panel = row.closest('.sq-post-v2-comments');
            const repliesRoot = row.closest('[data-comment-replies]');
            row.remove();

            if (repliesRoot instanceof HTMLElement && repliesRoot.children.length === 0) {
                repliesRoot.hidden = true;
            }

            const list = panel ? panel.querySelector('[data-comment-list], .sq-post-v2-comment-list') : null;
            const topLevelRows = list ? Array.from(list.children).filter((node) => node.classList?.contains('sq-post-v2-comment-row')) : [];
            if (list && topLevelRows.length === 0) {
                list.insertAdjacentHTML('afterend', '<p class="sq-post-v2-comments-empty" data-comments-empty-state>No comments yet. Start the conversation.</p>');
                list.remove();
            }

            updateCommentsCount(card, count, panel);
        };

        document.addEventListener('submit', async (event) => {
            const form = event.target.closest('form');
            if (!form) return;

            const card = form.closest('.sq-post-card-v2');
            if (!card) return;

            // ── Reaction form (main button or picker option) ──
            const reactionControl = form.closest('[data-reaction-control]');
            if (reactionControl) {
                event.preventDefault();
                const btn = form.querySelector('button');
                if (btn) btn.disabled = true;

                try {
                    const response = await postWithCsrf(form.action, {
                        type: form.querySelector('input[name="type"]')?.value || 'like',
                    });
                    const data = await response.json();
                    if (data.success) {
                        updateReactionUI(card, data);
                    }
                } catch (error) {
                    console.error('Reaction failed:', error);
                } finally {
                    if (btn) btn.disabled = false;
                }
                return;
            }

            // ── Comment reaction form ──
            if (form.matches('[data-comment-reaction-form]')) {
                event.preventDefault();
                const row = form.closest('.sq-post-v2-comment-row');
                const button = form.querySelector('button[type="submit"]');
                const type = form.querySelector('input[name="type"]')?.value || 'like';
                if (button) button.disabled = true;

                try {
                    const response = await postWithCsrf(form.action, { type });
                    const data = await response.json();
                    if (response.ok && data.success) {
                        updateCommentReactionUI(row, data);
                    } else if (data.errors) {
                        pushCommentToast(Object.values(data.errors)[0] || 'Could not update comment reaction.');
                    }
                } catch (error) {
                    console.error('Comment reaction failed:', error);
                    pushCommentToast('Could not update comment reaction.');
                } finally {
                    if (button) button.disabled = false;
                }
                return;
            }

            // ── Comment create / reply form ──
            if (form.matches('.sq-post-v2-comment-form')) {
                event.preventDefault();
                const input = form.querySelector('input[name="content"], textarea[name="content"]');
                if (!input || !input.value.trim()) return;

                const parentIdInput = form.querySelector('input[name="parent_id"]');
                const parentId = parentIdInput ? Number(parentIdInput.value || 0) : 0;
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                try {
                    const payload = {
                        content: input.value.trim(),
                    };
                    if (parentId > 0) {
                        payload.parent_id = String(parentId);
                    }

                    const response = await postWithCsrf(form.action, payload);
                    const data = await response.json();
                    if (data.success && data.html) {
                        const panel = card.querySelector('.sq-post-v2-comments');
                        if (panel) {
                            const emptyMsg = panel.querySelector('[data-comments-empty-state], .sq-post-v2-comments-empty');
                            if (emptyMsg) emptyMsg.remove();

                            if ((data.parent_id || 0) > 0) {
                                const parentRow = panel.querySelector(`[data-comment-id="${data.parent_id}"]`);
                                const repliesRoot = parentRow?.querySelector('[data-comment-replies]');
                                if (repliesRoot instanceof HTMLElement) {
                                    repliesRoot.hidden = false;
                                    repliesRoot.insertAdjacentHTML('beforeend', data.html);
                                }
                            } else {
                                let list = panel.querySelector('[data-comment-list], .sq-post-v2-comment-list');
                                if (!list) {
                                    list = document.createElement('div');
                                    list.className = 'sq-post-v2-comment-list';
                                    list.dataset.commentList = 'true';
                                    const inputSection = panel.querySelector('.sq-post-v2-comment-input');
                                    if (inputSection) {
                                        panel.insertBefore(list, inputSection);
                                    } else {
                                        panel.appendChild(list);
                                    }
                                }
                                list.insertAdjacentHTML('beforeend', data.html);
                            }

                            panel.classList.remove('is-hidden');
                            updateCommentsCount(card, data.comments_count, panel);
                        }

                        input.value = '';
                        const replyShell = form.closest('[data-comment-reply-shell]');
                        if (replyShell instanceof HTMLElement) {
                            replyShell.hidden = true;
                        }
                    } else if (data.errors) {
                        console.warn('Comment validation:', data.errors);
                        pushCommentToast(Object.values(data.errors)[0] || 'Could not post comment.');
                    }
                } catch (error) {
                    console.error('Comment failed:', error);
                    pushCommentToast('Could not post comment right now.');
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                }
                return;
            }

            // ── Comment edit form ──
            if (form.matches('[data-comment-edit-form]')) {
                event.preventDefault();
                const row = form.closest('.sq-post-v2-comment-row');
                const field = form.querySelector('[data-comment-edit-field]');
                const submitBtn = form.querySelector('button[type="submit"]');
                if (!(field instanceof HTMLInputElement)) {
                    return;
                }

                submitBtn?.setAttribute('disabled', 'true');
                try {
                    const response = await postWithCsrf(form.action, {
                        _method: 'PATCH',
                        content: field.value.trim(),
                    });
                    const data = await response.json();
                    if (response.ok && data.success) {
                        setCommentContent(row, data.content || '');
                        const shell = form.closest('[data-comment-edit-shell]');
                        if (shell instanceof HTMLElement) {
                            shell.hidden = true;
                        }
                    } else if (data.errors) {
                        pushCommentToast(Object.values(data.errors)[0] || 'Could not update comment.');
                    }
                } catch (error) {
                    console.error('Edit comment failed:', error);
                    pushCommentToast('Could not update comment right now.');
                } finally {
                    submitBtn?.removeAttribute('disabled');
                }
                return;
            }

            // ── Comment hide form ──
            if (form.matches('[data-comment-hide-form]')) {
                event.preventDefault();
                const row = form.closest('.sq-post-v2-comment-row');
                const hideBtn = form.querySelector('button[type="submit"]');
                hideBtn?.setAttribute('disabled', 'true');

                try {
                    const response = await postWithCsrf(form.action);
                    const data = await response.json();
                    if (response.ok && data.success) {
                        removeCommentRow(row, Number(data.comments_count || 0), card);
                        if (data.message) {
                            pushCommentToast(data.message);
                        }
                    } else if (data.errors) {
                        pushCommentToast(Object.values(data.errors)[0] || 'Could not hide comment.');
                    }
                } catch (error) {
                    console.error('Hide comment failed:', error);
                    pushCommentToast('Could not hide comment right now.');
                } finally {
                    hideBtn?.removeAttribute('disabled');
                }
                return;
            }

            // ── Comment delete form ──
            if (form.matches('[data-comment-delete-form]')) {
                const deleteBtn = form.querySelector('button[type="submit"]');
                if (!deleteBtn) return;

                event.preventDefault();
                if (!window.confirm('Delete this comment?')) {
                    return;
                }

                deleteBtn.disabled = true;

                try {
                    const response = await postWithCsrf(form.action, { _method: 'DELETE' });
                    const data = await response.json();
                    if (response.ok && data.success) {
                        const row = form.closest('.sq-post-v2-comment-row');
                        removeCommentRow(row, Number(data.comments_count || 0), card);
                    } else if (data.errors) {
                        pushCommentToast(Object.values(data.errors)[0] || 'Could not delete comment.');
                    }
                } catch (error) {
                    console.error('Delete comment failed:', error);
                    pushCommentToast('Could not delete comment right now.');
                } finally {
                    deleteBtn.disabled = false;
                }
                return;
            }
        });

        document.addEventListener('click', (event) => {
            const menuToggle = event.target.closest('[data-comment-menu-toggle]');
            if (menuToggle) {
                event.preventDefault();
                event.stopPropagation();
                const root = menuToggle.closest('[data-comment-menu-root]');
                const panel = root?.querySelector('[data-comment-menu-panel]');
                const willOpen = panel?.hidden ?? false;
                closeAllCommentMenus();
                if (root instanceof HTMLElement && panel instanceof HTMLElement && willOpen) {
                    positionCommentMenu(root, panel);
                    root.classList.add('is-open');
                    panel.hidden = false;
                    menuToggle.setAttribute('aria-expanded', 'true');
                }
                return;
            }

            const editStart = event.target.closest('[data-comment-edit-start]');
            if (editStart) {
                const row = editStart.closest('.sq-post-v2-comment-row');
                const shell = row?.querySelector('[data-comment-edit-shell]');
                const field = shell?.querySelector('[data-comment-edit-field]');
                closeAllCommentMenus();
                closeAllEditAndReplyShells();
                if (shell instanceof HTMLElement) {
                    shell.hidden = false;
                }
                if (field instanceof HTMLInputElement) {
                    requestAnimationFrame(() => {
                        field.focus();
                        field.setSelectionRange(field.value.length, field.value.length);
                    });
                }
                return;
            }

            const editCancel = event.target.closest('[data-comment-edit-cancel]');
            if (editCancel) {
                const shell = editCancel.closest('[data-comment-edit-shell]');
                if (shell instanceof HTMLElement) {
                    shell.hidden = true;
                }
                return;
            }

            const replyToggle = event.target.closest('[data-comment-reply-toggle]');
            if (replyToggle) {
                const row = replyToggle.closest('.sq-post-v2-comment-row');
                const shell = row?.querySelector('[data-comment-reply-shell]');
                closeAllCommentMenus();
                closeAllEditAndReplyShells();
                if (shell instanceof HTMLElement) {
                    shell.hidden = false;
                    const input = shell.querySelector('.sq-post-v2-comment-field');
                    if (input instanceof HTMLInputElement) {
                        requestAnimationFrame(() => input.focus());
                    }
                }
                return;
            }

            const replyCancel = event.target.closest('[data-comment-reply-cancel]');
            if (replyCancel) {
                const shell = replyCancel.closest('[data-comment-reply-shell]');
                if (shell instanceof HTMLElement) {
                    shell.hidden = true;
                }
                return;
            }

            if (!event.target.closest('[data-comment-menu-root]')) {
                closeAllCommentMenus();
            }
        });

        // Enter key on comment input
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAllCommentMenus();
                closeAllEditAndReplyShells();
                return;
            }

            if (event.key !== 'Enter' || event.shiftKey || event.ctrlKey || event.metaKey) return;
            const input = event.target.closest('.sq-post-v2-comment-field');
            if (!input) return;
            event.preventDefault();
            input.closest('form')?.requestSubmit();
        });

        window.addEventListener('resize', closeAllCommentMenus);
    };

    const syncFeedEmptyState = (list) => {
        if (!(list instanceof HTMLElement)) {
            return;
        }

        const emptyState = list.querySelector('[data-feed-empty-state]');
        const cards = list.querySelectorAll('.sq-post-card-v2');
        if (cards.length > 0) {
            emptyState?.remove();
            return;
        }

        if (emptyState) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.dataset.feedEmptyState = 'true';
        wrapper.className = 'rounded-xl border border-border bg-card py-12 text-center text-card-foreground';
        wrapper.innerHTML = `<div class="p-6"><p class="text-muted-foreground">${list.dataset.emptyMessage || 'No posts yet.'}</p></div>`;
        list.appendChild(wrapper);
    };

    const initPostMenus = () => {
        const closeAllMenus = () => {
            document.querySelectorAll('[data-post-menu-root]').forEach((root) => {
                const toggle = root.querySelector('[data-post-menu-toggle]');
                const panel = root.querySelector('[data-post-menu-panel]');
                if (toggle instanceof HTMLElement) {
                    toggle.setAttribute('aria-expanded', 'false');
                }
                if (panel instanceof HTMLElement) {
                    panel.hidden = true;
                }
            });
        };

        const setPostContent = (card, content) => {
            let contentNode = card.querySelector('[data-post-content]');
            if (!contentNode && content !== '') {
                contentNode = document.createElement('div');
                contentNode.className = 'sq-post-v2-content';
                contentNode.dataset.postContent = 'true';
                const media = card.querySelector('.sq-post-v2-media');
                const editShell = card.querySelector('[data-post-edit-shell]');
                const insertBefore = media || editShell?.nextElementSibling || card.querySelector('.sq-post-v2-summary') || card.querySelector('.sq-post-v2-divider');
                if (insertBefore) {
                    card.insertBefore(contentNode, insertBefore);
                } else {
                    card.appendChild(contentNode);
                }
            }

            if (contentNode) {
                contentNode.textContent = content;
                contentNode.hidden = content === '';
                contentNode.style.display = content === '' ? 'none' : '';
            }
        };

        const showHiddenComments = async (card, url) => {
            if (!(card instanceof HTMLElement) || !url) {
                return;
            }

            const panel = card.querySelector('.sq-post-v2-comments');
            const root = panel?.querySelector('[data-hidden-comments-root]');
            if (!(panel instanceof HTMLElement) || !(root instanceof HTMLElement)) {
                return;
            }

            panel.classList.remove('is-hidden');

            if (root.dataset.loaded === 'true') {
                root.hidden = false;
                return;
            }

            root.hidden = false;
            root.innerHTML = '<p class="sq-post-v2-hidden-comments-empty">Loading hidden comments…</p>';

            try {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                const data = await response.json();

                if (response.ok && data.success) {
                    root.innerHTML = data.html || '<p class="sq-post-v2-hidden-comments-empty">No hidden comments for this post.</p>';
                    root.dataset.loaded = 'true';
                } else {
                    root.innerHTML = '<p class="sq-post-v2-hidden-comments-empty">Could not load hidden comments.</p>';
                }
            } catch (error) {
                console.error('Load hidden comments failed:', error);
                root.innerHTML = '<p class="sq-post-v2-hidden-comments-empty">Could not load hidden comments.</p>';
            }
        };

        document.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-post-menu-toggle]');
            if (toggle) {
                event.preventDefault();
                event.stopPropagation();
                const root = toggle.closest('[data-post-menu-root]');
                const panel = root?.querySelector('[data-post-menu-panel]');
                const willOpen = panel?.hidden ?? false;
                closeAllMenus();
                if (root && panel instanceof HTMLElement && willOpen) {
                    panel.hidden = false;
                    toggle.setAttribute('aria-expanded', 'true');
                }
                return;
            }

            const editStart = event.target.closest('[data-post-edit-start]');
            if (editStart) {
                const card = editStart.closest('.sq-post-card-v2');
                const shell = card?.querySelector('[data-post-edit-shell]');
                const field = shell?.querySelector('[data-post-edit-field]');
                closeAllMenus();
                if (shell instanceof HTMLElement) {
                    shell.hidden = false;
                }
                if (field instanceof HTMLTextAreaElement) {
                    requestAnimationFrame(() => {
                        field.focus();
                        field.setSelectionRange(field.value.length, field.value.length);
                    });
                }
                return;
            }

            const hiddenCommentsView = event.target.closest('[data-post-hidden-comments-view]');
            if (hiddenCommentsView) {
                const card = hiddenCommentsView.closest('.sq-post-card-v2');
                closeAllMenus();
                showHiddenComments(card, hiddenCommentsView.dataset.url || '');
                return;
            }

            const editCancel = event.target.closest('[data-post-edit-cancel]');
            if (editCancel) {
                const shell = editCancel.closest('[data-post-edit-shell]');
                if (shell instanceof HTMLElement) {
                    shell.hidden = true;
                }
                return;
            }

            if (!event.target.closest('[data-post-menu-root]')) {
                closeAllMenus();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAllMenus();
                document.querySelectorAll('[data-post-edit-shell]').forEach((shell) => {
                    shell.hidden = true;
                });
            }
        });

        document.addEventListener('submit', async (event) => {
            const form = event.target.closest('form');
            if (!form) {
                return;
            }

            const card = form.closest('.sq-post-card-v2');
            if (!card) {
                return;
            }

            if (form.matches('[data-post-edit-form]')) {
                event.preventDefault();
                const field = form.querySelector('[data-post-edit-field]');
                const submitBtn = form.querySelector('button[type="submit"]');
                if (!(field instanceof HTMLTextAreaElement)) {
                    return;
                }

                submitBtn?.setAttribute('disabled', 'true');
                try {
                    const response = await postWithCsrf(form.action, {
                        _method: 'PATCH',
                        content: field.value.trim(),
                    });
                    const data = await response.json();
                    if (response.ok && data.success) {
                        setPostContent(card, data.content || '');
                        const shell = form.closest('[data-post-edit-shell]');
                        if (shell instanceof HTMLElement) {
                            shell.hidden = true;
                        }
                    }
                } catch (error) {
                    console.error('Edit post failed:', error);
                } finally {
                    submitBtn?.removeAttribute('disabled');
                }
                return;
            }

            if (form.matches('[data-post-save-form]')) {
                event.preventDefault();
                const methodInput = form.querySelector('[data-post-save-method]');
                const label = form.querySelector('[data-post-save-label]');
                const button = form.querySelector('[data-post-save-button]');
                const isSaved = card.dataset.postSaved === 'true';
                try {
                    const response = await postWithCsrf(form.action, isSaved ? { _method: 'DELETE' } : {});
                    const data = await response.json();
                    if (response.ok && data.success) {
                        const nextSaved = !!data.is_saved;
                        card.dataset.postSaved = nextSaved ? 'true' : 'false';
                        if (methodInput instanceof HTMLInputElement) {
                            methodInput.value = nextSaved ? 'DELETE' : '';
                        }
                        if (label instanceof HTMLElement) {
                            label.textContent = nextSaved ? 'Unsave post' : 'Save post';
                        }
                        if (button instanceof HTMLElement) {
                            button.classList.toggle('is-active', nextSaved);
                        }
                    }
                } catch (error) {
                    console.error('Save post failed:', error);
                }
                return;
            }

            if (form.matches('[data-post-hide-form]')) {
                event.preventDefault();
                try {
                    const response = await postWithCsrf(form.action);
                    const data = await response.json();
                    if (response.ok && data.success) {
                        const list = card.parentElement;
                        card.remove();
                        syncFeedEmptyState(list);
                    }
                } catch (error) {
                    console.error('Hide post failed:', error);
                }
                return;
            }

            if (form.matches('[data-post-delete-form]')) {
                event.preventDefault();
                if (!window.confirm('Delete this post?')) {
                    return;
                }

                try {
                    const response = await postWithCsrf(form.action, { _method: 'DELETE' });
                    const data = await response.json();
                    if (response.ok && data.success) {
                        const list = card.parentElement;
                        card.remove();
                        syncFeedEmptyState(list);
                    }
                } catch (error) {
                    console.error('Delete post failed:', error);
                }
            }
        });
    };

    const initComposerMediaPicker = () => {

        // Handle composer dialog open/close
        document.querySelectorAll('[data-composer-dialog-open]').forEach((button) => {
            button.addEventListener('click', () => {
                const dialog = document.getElementById('composer-dialog');
                const mediaType = button.dataset.mediaType;
                
                if (dialog instanceof HTMLDialogElement) {
                    // If button has media type, trigger file picker first
                    if (mediaType) {
                        const mediaInput = dialog.querySelector('[data-composer-media]');
                        if (mediaInput instanceof HTMLInputElement) {
                            // Set accept attribute based on media type
                            if (mediaType === 'image') {
                                mediaInput.accept = 'image/jpeg,image/png,image/gif,image/webp';
                            } else if (mediaType === 'video') {
                                mediaInput.accept = 'video/mp4,video/webm,video/quicktime';
                            }
                            
                            // Store that we should open dialog after file selection
                            mediaInput.dataset.openDialogAfterSelect = 'true';
                            mediaInput.click();
                            return;
                        }
                    }
                    
                    // Otherwise just open the dialog
                    dialog.showModal();
                    document.body.style.overflow = 'hidden';
                    const textarea = dialog.querySelector('[data-composer-input]');
                    if (textarea instanceof HTMLTextAreaElement) {
                        requestAnimationFrame(() => textarea.focus());
                    }
                }
            });
        });

        document.querySelectorAll('[data-composer-dialog-close]').forEach((button) => {
            button.addEventListener('click', () => {
                const dialog = document.getElementById('composer-dialog');
                if (dialog instanceof HTMLDialogElement) {
                    dialog.close();
                    document.body.style.overflow = '';
                }
            });
        });

        // Close dialog on backdrop click
        const composerDialog = document.getElementById('composer-dialog');
        if (composerDialog instanceof HTMLDialogElement) {
            composerDialog.addEventListener('click', (event) => {
                if (event.target === composerDialog) {
                    composerDialog.close();
                    document.body.style.overflow = '';
                }
            });
            
            // Also handle ESC key and other close events
            composerDialog.addEventListener('close', () => {
                document.body.style.overflow = '';
            });
        }

        document.querySelectorAll('[data-post-composer]').forEach((form) => {
            if (form.dataset.composerBound === 'true') {
                return;
            }

            const mediaInput = form.querySelector('[data-composer-media]');
            const textarea = form.querySelector('[data-composer-input]');
            const meta = form.querySelector('[data-composer-file-meta]');
            const preview = form.querySelector('[data-composer-preview]');
            const previewImage = form.querySelector('[data-composer-preview-image]');
            const previewVideo = form.querySelector('[data-composer-preview-video]');
            const removeMedia = form.querySelector('[data-composer-remove-media]');

            if (!(mediaInput instanceof HTMLInputElement) || !(textarea instanceof HTMLTextAreaElement)) {
                return;
            }

            form.dataset.composerBound = 'true';
            let previewUrl = '';

            const autoSize = () => {
                textarea.style.height = 'auto';
                textarea.style.height = `${Math.max(textarea.scrollHeight, 120)}px`;
            };

            const clearPreview = ({ clearInput = false } = {}) => {
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                    previewUrl = '';
                }

                if (previewImage instanceof HTMLImageElement) {
                    previewImage.hidden = true;
                    previewImage.removeAttribute('src');
                }

                if (previewVideo instanceof HTMLVideoElement) {
                    previewVideo.pause();
                    previewVideo.hidden = true;
                    previewVideo.removeAttribute('src');
                    previewVideo.load();
                }

                if (preview instanceof HTMLElement) {
                    preview.hidden = true;
                }

                if (clearInput) {
                    mediaInput.value = '';
                }

                if (meta instanceof HTMLElement) {
                    meta.textContent = 'No media selected';
                }
            };

            const renderPreview = () => {
                const file = mediaInput.files?.[0];
                if (!file) {
                    clearPreview();
                    return;
                }

                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                }
                previewUrl = URL.createObjectURL(file);
                
                if (meta instanceof HTMLElement) {
                    meta.textContent = file ? `Attached: ${file.name}` : 'No media selected';
                }

                if (preview instanceof HTMLElement) {
                    preview.hidden = false;
                }

                if (file.type.startsWith('video/')) {
                    if (previewImage instanceof HTMLImageElement) {
                        previewImage.hidden = true;
                        previewImage.removeAttribute('src');
                    }
                    if (previewVideo instanceof HTMLVideoElement) {
                        previewVideo.src = previewUrl;
                        previewVideo.hidden = false;
                        previewVideo.load();
                    }
                } else {
                    if (previewVideo instanceof HTMLVideoElement) {
                        previewVideo.pause();
                        previewVideo.hidden = true;
                        previewVideo.removeAttribute('src');
                        previewVideo.load();
                    }
                    if (previewImage instanceof HTMLImageElement) {
                        previewImage.src = previewUrl;
                        previewImage.hidden = false;
                    }
                }

                // If this was triggered from dashboard media buttons, open the dialog
                if (mediaInput.dataset.openDialogAfterSelect === 'true') {
                    delete mediaInput.dataset.openDialogAfterSelect;
                    const dialog = document.getElementById('composer-dialog');
                    if (dialog instanceof HTMLDialogElement && !dialog.open) {
                        dialog.showModal();
                        document.body.style.overflow = 'hidden';
                        const textarea = dialog.querySelector('[data-composer-input]');
                        if (textarea instanceof HTMLTextAreaElement) {
                            requestAnimationFrame(() => textarea.focus());
                        }
                    }
                }
            };

            textarea.addEventListener('input', autoSize);

            form.querySelectorAll('[data-media-trigger]').forEach((button) => {
                button.addEventListener('click', () => {
                    const kind = button.getAttribute('data-media-trigger') || 'all';
                    mediaInput.accept = kind === 'image'
                        ? 'image/jpeg,image/png,image/gif,image/webp'
                        : kind === 'video'
                            ? 'video/mp4,video/webm,video/quicktime'
                            : 'image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime';
                    mediaInput.click();
                });
            });

            mediaInput.addEventListener('change', renderPreview);
            removeMedia?.addEventListener('click', () => clearPreview({ clearInput: true }));

            autoSize();
        });

        // Handle form submission in dialog
        const composerForm = document.getElementById('composer-form');
        if (composerForm instanceof HTMLFormElement) {
            composerForm.addEventListener('submit', () => {
                // Form will submit normally, dialog will close on page reload
                // or we can handle it with AJAX if needed
            });
        }
    };

    const initRecommendations = () => {
        const panel = document.querySelector('[data-recommendations-panel]');
        if (!panel) {
            return;
        }

        fetch(appUrl('api/recommendations'), { credentials: 'same-origin' })
            .then((response) => response.json())
            .then((data) => {
                const recommendations = data.recommendations || [];
                if (!recommendations.length) {
                    panel.innerHTML = '<div class="p-6 pb-3"><h3 class="text-sm font-semibold tracking-tight">People you may know</h3></div><div class="p-6 pt-0"><p class="text-sm text-muted-foreground">No recommendations yet.</p></div>';
                    return;
                }

                panel.innerHTML = `
                    <div class="p-6 pb-3">
                        <h3 class="text-sm font-semibold tracking-tight">People you may know</h3>
                    </div>
                    <div class="p-6 pt-0 space-y-3">
                        ${recommendations.map((user) => `
                            <div class="flex items-center gap-3">
                                <a href="${appUrl(`u/${encodeURIComponent(user.username)}`)}">
                                    <span class="relative flex h-9 w-9 shrink-0 overflow-hidden rounded-full">
                                        ${user.profile_picture_url
                                            ? `<img src="${user.profile_picture_url}" alt="${user.full_name}" class="aspect-square h-full w-full object-cover">`
                                            : `<span class="flex h-full w-full items-center justify-center rounded-full bg-muted text-xs font-semibold">${user.full_name.split(' ').map((part) => part[0] || '').join('').slice(0, 2).toUpperCase()}</span>`}
                                    </span>
                                </a>
                                <div class="flex-1 min-w-0">
                                    <a href="${appUrl(`u/${encodeURIComponent(user.username)}`)}" class="text-sm font-medium hover:underline truncate block">${user.full_name}</a>
                                    <p class="text-xs text-muted-foreground truncate">${user.mutual_count > 0 ? `${user.mutual_count} mutual connection${user.mutual_count === 1 ? '' : 's'}` : `@${user.username}`}</p>
                                </div>
                                <button type="button" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-xs font-medium transition-colors hover:bg-primary/90 bg-primary text-primary-foreground shadow h-7 px-3" data-follow-recommendation="${user.id}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:.25rem; flex-shrink:0;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                                    Follow
                                </button>
                            </div>
                        `).join('')}
                    </div>
                `;
            })
            .catch(() => {
                panel.innerHTML = '<div class="p-6 pb-3"><h3 class="text-sm font-semibold tracking-tight">People you may know</h3></div><div class="p-6 pt-0"><p class="text-sm text-muted-foreground">Could not load recommendations.</p></div>';
            });

        panel.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-follow-recommendation]');
            if (!button) {
                return;
            }

            button.disabled = true;
            try {
                await postWithCsrf(appUrl(`users/${button.dataset.followRecommendation}/follow`));
                button.textContent = 'Following';
                button.className = 'inline-flex items-center justify-center whitespace-nowrap rounded-md text-xs font-medium transition-colors border border-input bg-background hover:bg-accent hover:text-accent-foreground shadow-sm h-7 px-3';
            } catch (error) {
                console.error(error);
                button.disabled = false;
            }
        });
    };

    const renderNotifications = (root, notifications, unreadCount) => {
        const count = root.querySelector('[data-notification-count]');
        const list = root.querySelector('[data-notification-list]');
        const markAll = root.querySelector('[data-mark-all-read]');
        if (!count || !list || !markAll) {
            return;
        }

        count.hidden = unreadCount < 1;
        count.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
        markAll.hidden = unreadCount < 1;

        if (!notifications.length) {
            list.innerHTML = '<p class="sq-muted">No notifications yet.</p>';
            return;
        }

        list.innerHTML = notifications.map((notification) => {
            const href = notification.type === 'NewFollowerNotification' && notification.data?.follower_username
                ? appUrl(`u/${encodeURIComponent(notification.data.follower_username)}`)
                : '';
            const content = `
                <div class="sq-notification-item ${notification.read_at ? '' : 'is-unread'}" data-notification-id="${notification.id}">
                    <strong>${notification.data?.message || 'New notification'}</strong>
                    <span class="sq-muted">${notification.created_at || ''}</span>
                    ${notification.read_at ? '' : '<button type="button" class="sq-inline-button" data-mark-read>Mark read</button>'}
                </div>
            `;

            return href ? `<a href="${href}">${content}</a>` : content;
        }).join('');
    };

    const initNotifications = () => {
        document.querySelectorAll('[data-notification-root]').forEach((root) => {
            const toggle = root.querySelector('[data-notification-toggle]');
            const panel = root.querySelector('[data-notification-panel]');
            if (!toggle || !panel) {
                return;
            }

            const fetchNotifications = async () => {
                const response = await fetch(appUrl('api/notifications'), { credentials: 'same-origin' });
                const data = await response.json();
                renderNotifications(root, data.notifications || [], data.unread_count || 0);
            };

            fetchNotifications().catch(() => {});
            setInterval(() => fetchNotifications().catch(() => {}), 30000);

            toggle.addEventListener('click', () => {
                panel.hidden = !panel.hidden;
                if (!panel.hidden) {
                    fetchNotifications().catch(() => {});
                }
            });

            document.addEventListener('click', (event) => {
                if (!root.contains(event.target)) {
                    panel.hidden = true;
                }
            });

            root.addEventListener('click', async (event) => {
                const markRead = event.target.closest('[data-mark-read]');
                const markAll = event.target.closest('[data-mark-all-read]');
                if (markAll) {
                    await postWithCsrf(appUrl('notifications/read-all'));
                    fetchNotifications().catch(() => {});
                    return;
                }

                if (!markRead) {
                    return;
                }

                const item = markRead.closest('[data-notification-id]');
                if (!item) {
                    return;
                }

                await postWithCsrf(appUrl(`notifications/${item.dataset.notificationId}/read`));
                fetchNotifications().catch(() => {});
            });
        });
    };

    const initReactorsDialog = () => {
        const dialog = document.getElementById('reactors-dialog');
        if (!(dialog instanceof HTMLDialogElement)) {
            return;
        }

        const filters = dialog.querySelector('[data-reactors-filters]');
        const body = dialog.querySelector('[data-reactors-body]');
        
        dialog.querySelector('[data-dialog-close]')?.addEventListener('click', () => {
            dialog.close();
            document.body.style.overflow = '';
        });
        
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
                document.body.style.overflow = '';
            }
        });
        
        dialog.addEventListener('close', () => {
            document.body.style.overflow = '';
        });

        document.addEventListener('click', async (event) => {
            const trigger = event.target.closest('[data-reactors-open]');
            if (!trigger || !filters || !body) {
                return;
            }

            filters.innerHTML = '';
            body.innerHTML = '<p class="sq-muted">Loading reactions…</p>';
            dialog.showModal();
            document.body.style.overflow = 'hidden';

            try {
                const response = await fetch(trigger.dataset.url, { credentials: 'same-origin' });
                const data = await response.json();
                const reactions = data.reactions || [];
                const grouped = reactions.reduce((acc, reaction) => {
                    acc[reaction.type] = acc[reaction.type] || 0;
                    acc[reaction.type] += 1;
                    return acc;
                }, {});

                const render = (type = 'all') => {
                    const filtered = type === 'all' ? reactions : reactions.filter((reaction) => reaction.type === type);
                    body.innerHTML = filtered.length
                        ? filtered.map((reaction) => `
                            <a class="sq-dialog-reactor" href="${appUrl(`u/${encodeURIComponent(reaction.user.username)}`)}">
                                ${reaction.user.profile_picture_url
                                    ? `<img src="${reaction.user.profile_picture_url}" alt="${reaction.user.full_name}" class="sq-avatar">`
                                    : `<span class="sq-avatar sq-avatar-fallback">${reaction.user.full_name.split(' ').map((part) => part[0] || '').join('').slice(0, 2).toUpperCase()}</span>`}
                                <div class="sq-user-meta">
                                    <div class="sq-user-name">${reaction.user.full_name}</div>
                                    <div class="sq-user-handle">@${reaction.user.username}</div>
                                </div>
                                <img src="${appUrl(`reactions/${reaction.type}.svg`)}" alt="${reaction.type}" class="sq-reaction-badge">
                            </a>
                        `).join('')
                        : '<p class="sq-muted">No reactions found for this filter.</p>';
                };

                filters.innerHTML = [`<button type="button" class="sq-chip is-active" data-reactor-filter="all">All ${reactions.length}</button>`]
                    .concat(Object.entries(grouped).map(([type, count]) => `<button type="button" class="sq-chip" data-reactor-filter="${type}">${type} ${count}</button>`))
                    .join('');

                filters.querySelectorAll('[data-reactor-filter]').forEach((button) => {
                    button.addEventListener('click', () => {
                        filters.querySelectorAll('[data-reactor-filter]').forEach((chip) => chip.classList.remove('is-active'));
                        button.classList.add('is-active');
                        render(button.dataset.reactorFilter || 'all');
                    });
                });

                render();
            } catch (error) {
                console.error(error);
                body.innerHTML = '<p class="sq-muted">Could not load reactions.</p>';
            }
        });
    };

    const initLiveSearch = () => {
        document.querySelectorAll('[data-live-search]').forEach((input) => {
            const target = document.querySelector(input.dataset.liveSearchTarget || '');
            if (!target) {
                return;
            }

            let timeout;
            input.addEventListener('input', () => {
                clearTimeout(timeout);
                const query = input.value.trim();
                if (query.length < 2) {
                    target.hidden = true;
                    target.innerHTML = '';
                    return;
                }

                timeout = setTimeout(async () => {
                    try {
                        const url = `${input.dataset.liveSearch}?q=${encodeURIComponent(query)}`;
                        const response = await fetch(url, { credentials: 'same-origin' });
                        const results = await response.json();
                        if (!results.length) {
                            target.hidden = false;
                            target.innerHTML = '<p class="sq-muted">No quick matches found.</p>';
                            return;
                        }

                        target.hidden = false;
                        target.innerHTML = results.map((user) => `
                            <a href="${appUrl(`u/${encodeURIComponent(user.username)}`)}" class="sq-live-result">
                                ${user.profile_picture_url
                                    ? `<img src="${user.profile_picture_url}" alt="${user.full_name}" class="sq-avatar sq-avatar-sm">`
                                    : `<span class="sq-avatar sq-avatar-sm sq-avatar-fallback">${user.full_name.split(' ').map((part) => part[0] || '').join('').slice(0, 2).toUpperCase()}</span>`}
                                <div>
                                    <div class="sq-user-name">${user.full_name}</div>
                                    <div class="sq-user-handle">@${user.username}</div>
                                </div>
                            </a>
                        `).join('');
                    } catch (error) {
                        console.error(error);
                    }
                }, 180);
            });
        });
    };

    class CustomDropdown {
        constructor(selectElement) {
            this.select = selectElement;
            this.options = Array.from(this.select.options);
            this.selectedIndex = this.select.selectedIndex;
            this.isOpen = false;
            this.createCustomDropdown();
            this.attachEvents();
            if (!window.customComponents) window.customComponents = [];
            window.customComponents.push(this);
        }

        createCustomDropdown() {
            this.wrapper = document.createElement('div');
            this.wrapper.className = 'custom-dropdown';
            if (this.select.dataset.dropdownSize) {
                this.wrapper.classList.add(`custom-dropdown-${this.select.dataset.dropdownSize}`);
            }

            this.backdrop = document.createElement('div');
            this.backdrop.className = 'custom-component-backdrop';
            this.backdrop.style.display = 'none';

            this.button = document.createElement('button');
            this.button.type = 'button';
            this.button.className = 'custom-dropdown-button';
            this.button.setAttribute('aria-haspopup', 'listbox');
            this.button.setAttribute('aria-expanded', 'false');
            this.button.innerHTML = `
                <span class="custom-dropdown-text">${this.options[this.selectedIndex]?.text || 'Select'}</span>
                <svg class="custom-dropdown-arrow" width="12" height="8" viewBox="0 0 12 8" fill="none">
                    <path d="M1 1.5L6 6.5L11 1.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            `;

            this.list = document.createElement('ul');
            this.list.className = 'custom-dropdown-list';
            this.list.setAttribute('role', 'listbox');

            this.options.forEach((option, index) => {
                if (option.hidden) {
                    return;
                }
                const li = document.createElement('li');
                li.className = 'custom-dropdown-option';
                li.textContent = option.text;
                li.dataset.value = option.value;
                li.dataset.index = String(index);
                li.setAttribute('role', 'option');
                if (option.disabled) li.classList.add('disabled');
                if (index === this.selectedIndex) li.classList.add('selected');
                li.setAttribute('aria-selected', index === this.selectedIndex ? 'true' : 'false');
                this.list.appendChild(li);
            });

            this.wrapper.appendChild(this.button);
            this.wrapper.appendChild(this.list);
            document.body.appendChild(this.backdrop);

            this.select.style.display = 'none';
            this.select.parentNode.insertBefore(this.wrapper, this.select.nextSibling);
        }

        attachEvents() {
            this.button.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggle();
            });

            this.list.addEventListener('click', (e) => {
                const option = e.target.closest('.custom-dropdown-option');
                if (!option || option.classList.contains('disabled')) return;
                this.selectOption(parseInt(option.dataset.index, 10));
            });

            this.backdrop.addEventListener('click', () => this.close());

            document.addEventListener('click', (e) => {
                if (this.isOpen && !this.wrapper.contains(e.target)) this.close();
            });
        }

        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                if (window.customComponents) {
                    window.customComponents.forEach((component) => {
                        if (component !== this && component.isOpen) component.close();
                    });
                }
                this.open();
            }
        }

        open() {
            this.wrapper.classList.add('open');
            this.backdrop.style.display = 'block';
            this.isOpen = true;
            this.button.setAttribute('aria-expanded', 'true');
            if (window.innerWidth <= 640) document.body.style.overflow = 'hidden';
        }

        close() {
            this.wrapper.classList.remove('open');
            this.backdrop.style.display = 'none';
            this.isOpen = false;
            this.button.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        selectOption(index) {
            this.select.selectedIndex = index;
            this.select.dispatchEvent(new Event('change', { bubbles: true }));
            this.selectedIndex = index;
            this.button.querySelector('.custom-dropdown-text').textContent = this.options[index].text;
            this.list.querySelectorAll('.custom-dropdown-option').forEach((opt, i) => {
                opt.classList.toggle('selected', i === index);
                opt.setAttribute('aria-selected', i === index ? 'true' : 'false');
            });
            this.close();
        }
    }

    class CustomCalendar {
        constructor(inputElement) {
            this.input = inputElement;
            this.selectedDate = this.input.value ? new Date(this.input.value) : null;
            this.currentMonth = this.selectedDate ? new Date(this.selectedDate) : new Date();
            this.isOpen = false;
            this.monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            this.dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
            this.createCustomCalendar();
            this.attachEvents();
            if (!window.customComponents) window.customComponents = [];
            window.customComponents.push(this);
        }

        createCustomCalendar() {
            this.wrapper = document.createElement('div');
            this.wrapper.className = 'custom-calendar';
            if (this.input.dataset.calendarSize) {
                this.wrapper.classList.add(`custom-calendar-${this.input.dataset.calendarSize}`);
            }

            this.backdrop = document.createElement('div');
            this.backdrop.className = 'custom-component-backdrop';
            this.backdrop.style.display = 'none';

            this.inputWrap = document.createElement('div');
            this.inputWrap.className = 'custom-calendar-input-wrap';

            this.trigger = document.createElement('button');
            this.trigger.type = 'button';
            this.trigger.className = 'custom-calendar-trigger';
            this.trigger.setAttribute('aria-label', 'Open calendar');
            this.trigger.setAttribute('aria-haspopup', 'dialog');
            this.trigger.setAttribute('aria-expanded', 'false');
            this.trigger.innerHTML = `
                <svg class="custom-calendar-icon" width="18" height="18" viewBox="0 0 20 20" fill="none">
                    <rect x="3" y="4" width="14" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M3 8H17" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M7 2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M13 2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            `;

            this.popup = document.createElement('div');
            this.popup.className = 'custom-calendar-popup';

            this.input.classList.add('custom-calendar-input');
            this.input.parentNode.insertBefore(this.wrapper, this.input);
            this.wrapper.appendChild(this.inputWrap);
            this.inputWrap.appendChild(this.input);
            this.inputWrap.appendChild(this.trigger);
            this.wrapper.appendChild(this.popup);
            document.body.appendChild(this.backdrop);

            this.syncFromInput();
            this.renderCalendar();
        }

        syncFromInput() {
            const value = (this.input.value || '').trim();
            if (!value) {
                this.selectedDate = null;
                return;
            }

            const parsed = new Date(`${value}T00:00:00`);
            if (!Number.isNaN(parsed.getTime())) {
                this.selectedDate = parsed;
                this.currentMonth = new Date(parsed);
            }
        }

        renderCalendar() {
            const year = this.currentMonth.getFullYear();
            const month = this.currentMonth.getMonth();
            this.popup.innerHTML = `
                <div class="custom-calendar-header">
                    <button type="button" class="custom-calendar-nav" data-action="prev-month">‹</button>
                    <div class="custom-calendar-title">${this.monthNames[month]} ${year}</div>
                    <button type="button" class="custom-calendar-nav" data-action="next-month">›</button>
                </div>
                <div class="custom-calendar-days">
                    ${this.dayNames.map((day) => `<div class="custom-calendar-day-name">${day}</div>`).join('')}
                </div>
                <div class="custom-calendar-dates">
                    ${this.renderDates(year, month)}
                </div>
                <div class="custom-calendar-pickers">
                    <div class="custom-picker-wrapper">
                        <div class="custom-picker-overlay"></div>
                        <div class="custom-picker month-picker" data-type="month">
                            ${this.renderMonthPicker(month)}
                        </div>
                    </div>
                    <div class="custom-picker-wrapper">
                        <div class="custom-picker-overlay"></div>
                        <div class="custom-picker year-picker" data-type="year">
                            ${this.renderYearPicker(year)}
                        </div>
                    </div>
                </div>
                <div class="custom-calendar-footer">
                    <button type="button" class="custom-calendar-action" data-action="clear">Clear</button>
                    <button type="button" class="custom-calendar-action" data-action="today">Today</button>
                </div>
            `;

            this.initializePickers();
        }

        renderMonthPicker(selectedMonth) {
            return this.monthNames.map((name, idx) => `<div class="picker-item ${idx === selectedMonth ? 'selected' : ''}" data-value="${idx}">${name}</div>`).join('');
        }

        renderYearPicker(selectedYear) {
            const currentYear = new Date().getFullYear();
            const years = [];
            for (let y = currentYear - 100; y <= currentYear + 10; y++) {
                years.push(`<div class="picker-item ${y === selectedYear ? 'selected' : ''}" data-value="${y}">${y}</div>`);
            }
            return years.join('');
        }

        initializePickers() {
            const monthPicker = this.popup.querySelector('.month-picker');
            const yearPicker = this.popup.querySelector('.year-picker');

            if (monthPicker) {
                this.setupPicker(monthPicker, 'month');
                this.scrollToSelected(monthPicker);
            }

            if (yearPicker) {
                this.setupPicker(yearPicker, 'year');
                this.scrollToSelected(yearPicker);
            }
        }

        setupPicker(picker, type) {
            let scrollTimeout;
            let isClickSelection = false;

            picker.addEventListener('scroll', () => {
                if (isClickSelection || picker.dataset.autoScrolling === 'true') {
                    return;
                }

                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    this.snapToNearest(picker, type, false);
                }, 150);
            });

            picker.addEventListener('click', (e) => {
                const item = e.target.closest('.picker-item');
                if (!item) {
                    return;
                }

                isClickSelection = true;
                const value = parseInt(item.dataset.value, 10);
                if (type === 'month') {
                    this.currentMonth.setMonth(value);
                } else {
                    this.currentMonth.setFullYear(value);
                }

                picker.querySelectorAll('.picker-item').forEach((i) => i.classList.remove('selected'));
                item.classList.add('selected');

                const offset = this.getPickerScrollTarget(picker, item);
                this.scrollPicker(picker, offset, 'smooth');
                this.updateHeaderTitle();

                setTimeout(() => {
                    isClickSelection = false;
                }, 500);
            });
        }

        snapToNearest(picker, type, shouldRender = true) {
            const items = picker.querySelectorAll('.picker-item');
            const pickerCenter = picker.scrollTop + (picker.clientHeight / 2);
            let closestItem = null;
            let closestDistance = Infinity;

            items.forEach((item) => {
                const itemCenter = item.offsetTop + (item.offsetHeight / 2);
                const distance = Math.abs(itemCenter - pickerCenter);
                if (distance < closestDistance) {
                    closestDistance = distance;
                    closestItem = item;
                }
            });

            if (!closestItem) {
                return;
            }

            const targetScrollTop = this.getPickerScrollTarget(picker, closestItem);
            if (Math.abs(picker.scrollTop - targetScrollTop) > 1) {
                this.scrollPicker(picker, targetScrollTop);
            }

            items.forEach((item) => item.classList.remove('selected'));
            closestItem.classList.add('selected');

            const value = parseInt(closestItem.dataset.value, 10);
            if (type === 'month') {
                this.currentMonth.setMonth(value);
            } else {
                this.currentMonth.setFullYear(value);
            }

            if (shouldRender) {
                this.renderCalendar();
            } else {
                this.updateHeaderTitle();
            }
        }

        updateHeaderTitle() {
            const titleElement = this.popup.querySelector('.custom-calendar-title');
            if (titleElement) {
                const year = this.currentMonth.getFullYear();
                const month = this.currentMonth.getMonth();
                titleElement.textContent = `${this.monthNames[month]} ${year}`;
            }

            const datesContainer = this.popup.querySelector('.custom-calendar-dates');
            if (datesContainer) {
                const year = this.currentMonth.getFullYear();
                const month = this.currentMonth.getMonth();
                datesContainer.innerHTML = this.renderDates(year, month);
            }
        }

        scrollToSelected(picker) {
            const selected = picker.querySelector('.picker-item.selected');
            if (!selected) {
                return;
            }
            const offset = this.getPickerScrollTarget(picker, selected);
            this.scrollPicker(picker, offset);
        }

        getPickerScrollTarget(picker, item) {
            return item.offsetTop - (picker.clientHeight / 2) + (item.offsetHeight / 2);
        }

        scrollPicker(picker, top, behavior = 'auto') {
            const maxScrollTop = Math.max(0, picker.scrollHeight - picker.clientHeight);
            const clampedTop = Math.max(0, Math.min(top, maxScrollTop));

            clearTimeout(picker._autoScrollTimer);
            picker.dataset.autoScrolling = 'true';
            picker.scrollTo({ top: clampedTop, behavior });

            const resetDelay = behavior === 'smooth' ? 350 : 0;
            picker._autoScrollTimer = setTimeout(() => {
                picker.dataset.autoScrolling = 'false';
            }, resetDelay);
        }

        renderDates(year, month) {
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrevMonth = new Date(year, month, 0).getDate();
            let html = '';

            for (let i = firstDay - 1; i >= 0; i--) {
                html += `<button type="button" class="custom-calendar-date other-month" data-date="${year}-${month}-${daysInPrevMonth - i}">${daysInPrevMonth - i}</button>`;
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dateStr = this.formatDateISO(date);
                const isSelected = this.selectedDate && this.formatDateISO(this.selectedDate) === dateStr;
                const isToday = this.formatDateISO(new Date()) === dateStr;
                let classes = 'custom-calendar-date';
                if (isSelected) classes += ' selected';
                if (isToday) classes += ' today';
                html += `<button type="button" class="${classes}" data-date="${dateStr}">${day}</button>`;
            }

            const totalCells = firstDay + daysInMonth;
            const remainingCells = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
            for (let i = 1; i <= remainingCells; i++) {
                html += `<button type="button" class="custom-calendar-date other-month" data-date="${year}-${month + 2}-${i}">${i}</button>`;
            }

            return html;
        }

        attachEvents() {
            this.trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggle();
            });

            this.input.addEventListener('input', () => {
                this.syncFromInput();
                if (this.isOpen) {
                    this.renderCalendar();
                }
            });

            this.input.addEventListener('change', () => {
                this.syncFromInput();
                if (this.isOpen) {
                    this.renderCalendar();
                }
            });

            this.popup.addEventListener('click', (e) => {
                e.stopPropagation();
                const target = e.target.closest('[data-action], [data-date]');
                if (!target) return;
                const action = target.dataset.action;
                const date = target.dataset.date;

                if (action === 'prev-month') {
                    this.currentMonth.setMonth(this.currentMonth.getMonth() - 1);
                    this.renderCalendar();
                } else if (action === 'next-month') {
                    this.currentMonth.setMonth(this.currentMonth.getMonth() + 1);
                    this.renderCalendar();
                } else if (action === 'clear') {
                    this.selectDate(null);
                } else if (action === 'today') {
                    this.selectDate(new Date());
                } else if (date) {
                    this.selectDate(new Date(date));
                }
            });

            this.backdrop.addEventListener('click', () => this.close());
            document.addEventListener('click', (e) => {
                if (this.isOpen && !this.wrapper.contains(e.target)) this.close();
            });
        }

        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                if (window.customComponents) {
                    window.customComponents.forEach((component) => {
                        if (component !== this && component.isOpen) component.close();
                    });
                }
                this.open();
            }
        }

        open() {
            this.syncFromInput();
            this.renderCalendar();
            this.wrapper.classList.add('open');
            this.backdrop.style.display = 'block';
            this.isOpen = true;
            this.trigger.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        close() {
            this.wrapper.classList.remove('open');
            this.backdrop.style.display = 'none';
            this.isOpen = false;
            this.trigger.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        selectDate(date) {
            this.selectedDate = date;
            this.input.value = date ? this.formatDateISO(date) : '';
            this.input.dispatchEvent(new Event('change', { bubbles: true }));
            if (date) this.currentMonth = new Date(date);
            this.renderCalendar();
            if (date) this.close();
        }

        formatDate(date) {
            if (!date) return '';
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        formatDateISO(date) {
            if (!date) return '';
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
    }

    const initCustomDropdowns = () => {
        document.querySelectorAll('select.custom-select').forEach((select) => {
            if (select.dataset.customDropdownBound === 'true') {
                return;
            }
            select.dataset.customDropdownBound = 'true';
            new CustomDropdown(select);
        });
    };

    const initCustomDatePickers = () => {
        document.querySelectorAll('input[type="date"].custom-date').forEach((input) => {
            if (input.dataset.customCalendarBound === 'true') {
                return;
            }
            input.dataset.customCalendarBound = 'true';
            new CustomCalendar(input);
        });
    };

    const initTwoFactorTabs = () => {
        document.querySelectorAll('[data-two-factor-tabs]').forEach((root) => {
            const triggers = root.querySelectorAll('[data-two-factor-trigger]');
            const panels = document.querySelectorAll('[data-two-factor-panel]');
            triggers.forEach((trigger) => {
                trigger.addEventListener('click', () => {
                    const mode = trigger.dataset.twoFactorTrigger;
                    triggers.forEach((button) => button.classList.toggle('is-active', button === trigger));
                    panels.forEach((panel) => {
                        panel.hidden = panel.dataset.twoFactorPanel !== mode;
                    });
                });
            });
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        initSidebar();
        initInfiniteFeeds();
        initPostMenus();
        initReactionPickers();
        initAjaxForms();
        initComposerMediaPicker();
        initRecommendations();
        initNotifications();
        initReactorsDialog();
        initLiveSearch();
        initCustomDropdowns();
        initCustomDatePickers();
        initTwoFactorTabs();
    });

})();
