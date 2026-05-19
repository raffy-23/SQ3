(function () {
    'use strict';

    // ─── Share Modal ────────────────────────────────────────────────────────────
    var shareModal = null;

    function ensureShareModal() {
        if (shareModal) return shareModal;

        shareModal = document.createElement('div');
        shareModal.id = 'sq-share-modal';
        shareModal.className = 'sq-share-modal';
        shareModal.hidden = true;
        shareModal.setAttribute('role', 'dialog');
        shareModal.setAttribute('aria-modal', 'true');
        shareModal.setAttribute('aria-label', 'Share post');
        shareModal.innerHTML = `
            <div class="sq-share-backdrop" data-share-close></div>
            <div class="sq-share-shell">
                <div class="sq-share-header">
                    <span class="sq-share-title">Share post</span>
                    <button type="button" class="sq-share-close-btn" data-share-close aria-label="Close">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <div class="sq-share-composer">
                    <div class="sq-share-composer-top">
                        <div class="sq-share-user-avatar" data-share-modal-avatar></div>
                        <textarea class="sq-share-caption" data-share-caption placeholder="Say something about this..." rows="3" maxlength="1000"></textarea>
                    </div>
                    <div class="sq-share-post-preview" data-share-post-preview>
                        <div class="sq-share-preview-header" data-share-preview-header></div>
                        <p class="sq-share-preview-text" data-share-preview-text></p>
                        <div class="sq-share-preview-media" data-share-preview-media hidden></div>
                    </div>
                </div>
                <div class="sq-share-footer">
                    <button type="button" class="sq-share-cancel-btn" data-share-close>Cancel</button>
                    <button type="button" class="sq-share-submit-btn" data-share-submit>
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                        Share now
                    </button>
                </div>
            </div>`;
        document.body.appendChild(shareModal);

        // Close on backdrop / close buttons
        shareModal.addEventListener('click', (e) => {
            if (e.target.closest('[data-share-close]')) closeShareModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && shareModal && !shareModal.hidden) closeShareModal();
        });

        return shareModal;
    }

    function closeShareModal() {
        if (shareModal) {
            shareModal.hidden = true;
            document.body.style.overflow = '';
        }
    }

    function openShareModal(trigger) {
        var modal = ensureShareModal();

        // Populate preview
        var author      = trigger.dataset.shareAuthor || '';
        var preview     = trigger.dataset.sharePreview || '';
        var avatar      = trigger.dataset.shareAvatar || '';
        var mediaUrl    = trigger.dataset.shareMedia || '';
        var shareUrl    = trigger.dataset.shareUrl || '';
        var postId      = trigger.dataset.postId || '';

        // User's own avatar (from nav bar if present)
        var selfAvatar = document.querySelector('.sq-sidebar-avatar, .sq-topbar-avatar, [data-self-avatar]');
        var selfAvatarEl = modal.querySelector('[data-share-modal-avatar]');
        if (selfAvatarEl) {
            if (selfAvatar instanceof HTMLImageElement) {
                selfAvatarEl.innerHTML = `<img src="${selfAvatar.src}" alt="" class="sq-share-modal-avatar-img">`;
            } else {
                var initials = (selfAvatar?.textContent || '?').trim().slice(0, 2).toUpperCase();
                selfAvatarEl.innerHTML = `<span class="sq-share-modal-avatar-fb">${initials}</span>`;
            }
        }

        // Preview header
        var previewHeader = modal.querySelector('[data-share-preview-header]');
        if (previewHeader) {
            previewHeader.innerHTML = avatar
                ? `<img src="${avatar}" alt="" class="sq-share-preview-avatar"><span class="sq-share-preview-author">${author}</span>`
                : `<span class="sq-share-preview-avatar-fb">${(author[0] || '?').toUpperCase()}</span><span class="sq-share-preview-author">${author}</span>`;
        }

        var previewText = modal.querySelector('[data-share-preview-text]');
        if (previewText) previewText.textContent = preview;

        var previewMedia = modal.querySelector('[data-share-preview-media]');
        if (previewMedia) {
            if (mediaUrl) {
                previewMedia.hidden = false;
                previewMedia.innerHTML = `<img src="${mediaUrl}" alt="" class="sq-share-preview-img" loading="lazy">`;
            } else {
                previewMedia.hidden = true;
                previewMedia.innerHTML = '';
            }
        }

        // Reset caption
        var caption = modal.querySelector('[data-share-caption]');
        if (caption) { caption.value = ''; }

        // Store references
        modal.dataset.shareUrl = shareUrl;
        modal.dataset.postId   = postId;
        modal._trigger         = trigger;

        // Show
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        setTimeout(() => caption?.focus(), 80);

        // Submit handler (bind fresh each open to avoid stale references)
        var submitBtn = modal.querySelector('[data-share-submit]');
        if (submitBtn) {
            submitBtn.onclick = () => submitShare(modal, trigger);
        }
    }

    async function submitShare(modal, trigger) {
        var caption   = modal.querySelector('[data-share-caption]')?.value.trim() || '';
        var shareUrl  = modal.dataset.shareUrl;
        var submitBtn = modal.querySelector('[data-share-submit]');

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sharing…';
        }

        try {
            var headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
            };
            headers[window.SideQuest.csrfHeader()] = window.SideQuest.csrfToken();

            var body = new URLSearchParams({ content: caption });
            var response = await fetch(shareUrl, { method: 'POST', headers, body, credentials: 'same-origin' });
            var data = await response.json();

            if (response.ok && data.success) {
                closeShareModal();
                // Update share count on the trigger button
                var countEl = trigger.closest('.sq-post-v2-action-item, .sq-post-v2-actions')
                    ?.querySelector('[data-shares-count-label]');
                if (countEl) {
                    countEl.textContent = data.shares_count + ' share' + (data.shares_count !== 1 ? 's' : '');
                }
                // Update action button label
                var label = trigger.querySelector('.sq-post-v2-action-label');
                if (label) {
                    label.textContent = 'Shared!';
                    setTimeout(() => { label.textContent = 'Share'; }, 2000);
                }
                // Reload feed softly
                var card = trigger.closest('.sq-post-card-v2');
                if (card) {
                    var countSpan = card.querySelector('.sq-post-v2-summary-link[data-shares-count-label]');
                    if (countSpan) {
                        countSpan.textContent = data.shares_count + ' share' + (data.shares_count !== 1 ? 's' : '');
                    }
                }
            } else {
                alert(data.error || data.message || 'Could not share. Try again.');
            }
        } catch (err) {
            console.error('Share failed:', err);
            alert('Could not share. Please try again.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg> Share now';
            }
        }
    }

    window.SideQuest.initReactionPickers = function () {
        var controlSelector = '[data-reaction-control], [data-comment-reaction-control]';
        var pickerState = new WeakMap();

        var stateFor = (control) => {
            if (!pickerState.has(control)) {
                pickerState.set(control, { showTimeout: null, hideTimeout: null });
            }

            return pickerState.get(control);
        };

        var showPicker = (control, immediate = false) => {
            var picker = control?.querySelector('[data-reaction-picker]');
            if (!(picker instanceof HTMLElement)) {
                return;
            }

            var state = stateFor(control);
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

        var hidePicker = (control) => {
            var picker = control?.querySelector('[data-reaction-picker]');
            if (!(picker instanceof HTMLElement)) {
                return;
            }

            var state = stateFor(control);
            clearTimeout(state.showTimeout);
            clearTimeout(state.hideTimeout);
            state.hideTimeout = setTimeout(() => {
                picker.hidden = true;
            }, 180);
        };

        document.addEventListener('click', (event) => {
            var shareButton = event.target.closest('[data-post-share]');
            if (shareButton) {
                event.preventDefault();
                openShareModal(shareButton);
                return;
            }

            var commentsToggle = event.target.closest('[data-comments-toggle]');
            if (commentsToggle) {
                var target = document.getElementById(commentsToggle.dataset.target || '');
                target?.classList.toggle('is-hidden');
            }
        });

        document.addEventListener('mouseover', (event) => {
            var control = event.target.closest(controlSelector);
            if (!control || control.contains(event.relatedTarget)) {
                return;
            }

            showPicker(control, false);
        });

        document.addEventListener('mouseout', (event) => {
            var control = event.target.closest(controlSelector);
            if (!control || control.contains(event.relatedTarget)) {
                return;
            }

            hidePicker(control);
        });

        document.addEventListener('focusin', (event) => {
            var control = event.target.closest(controlSelector);
            if (!control) {
                return;
            }

            showPicker(control, true);
        });

        document.addEventListener('focusout', (event) => {
            var control = event.target.closest(controlSelector);
            if (!control || control.contains(event.relatedTarget)) {
                return;
            }

            hidePicker(control);
        });
    };

    window.SideQuest.initReactorsDialog = function () {
        var dialog = document.getElementById('reactors-dialog');
        if (!(dialog instanceof HTMLDialogElement)) {
            return;
        }

        var filters = dialog.querySelector('[data-reactors-filters]');
        var body = dialog.querySelector('[data-reactors-body]');

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
            var trigger = event.target.closest('[data-reactors-open]');
            if (!trigger || !filters || !body) {
                return;
            }

            filters.innerHTML = '';
            body.innerHTML = '<p class="sq-muted">Loading reactions…</p>';
            dialog.showModal();
            document.body.style.overflow = 'hidden';

            try {
                var response = await fetch(trigger.dataset.url, { credentials: 'same-origin' });
                var data = await response.json();
                var reactions = data.reactions || [];
                var grouped = reactions.reduce((acc, reaction) => {
                    acc[reaction.type] = acc[reaction.type] || 0;
                    acc[reaction.type] += 1;
                    return acc;
                }, {});

                var render = (type = 'all') => {
                    var filtered = type === 'all' ? reactions : reactions.filter((reaction) => reaction.type === type);
                    body.innerHTML = filtered.length
                        ? filtered.map((reaction) => `
                            <a class="sq-dialog-reactor" href="${window.SideQuest.appUrl(`u/${encodeURIComponent(reaction.user.username)}`)}">

                                ${reaction.user.profile_picture_url
                                    ? `<img src="${reaction.user.profile_picture_url}" alt="${reaction.user.full_name}" class="sq-avatar">`
                                    : `<span class="sq-avatar sq-avatar-fallback">${reaction.user.full_name.split(' ').map((part) => part[0] || '').join('').slice(0, 2).toUpperCase()}</span>`}
                                <div class="sq-user-meta">
                                    <div class="sq-user-name">${reaction.user.full_name}</div>
                                    <div class="sq-user-handle">@${reaction.user.username}</div>
                                </div>
                                <img src="${window.SideQuest.appUrl(`reactions/${reaction.type}.svg`)}" alt="${reaction.type}" class="sq-reaction-badge">

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
})();
