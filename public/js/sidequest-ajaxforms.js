(function () {
    'use strict';

    window.SideQuest.initAjaxForms = function () {
        var reactionIconUrls = {
            like: window.SideQuest.appUrl('reactions/like.svg'),
            love: window.SideQuest.appUrl('reactions/love.svg'),
            haha: window.SideQuest.appUrl('reactions/haha.svg'),
            wow: window.SideQuest.appUrl('reactions/wow.svg'),
            sad: window.SideQuest.appUrl('reactions/sad.svg'),
            angry: window.SideQuest.appUrl('reactions/angry.svg'),
        };

        var reactionLabels = {
            like: 'Like', love: 'Love', haha: 'Haha',
            wow: 'Wow', sad: 'Sad', angry: 'Angry',
        };

        var summaryIcon = (type) =>
            `<img src="${reactionIconUrls[type] || ''}" alt="" class="sq-reaction-badge">`;

        var updateReactionUI = (card, data) => {
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
                newSummary.innerHTML = `<div class="sq-post-v2-summary-left"><button type="button" class="sq-post-v2-summary-reactions" data-reactors-open data-url="${window.SideQuest.appUrl(`api/posts/${card.dataset.postId}/reactions`)}" title="View reactions" aria-label="View reactions"><span class="sq-post-v2-summary-icons">${iconsHtml}</span><span class="sq-post-v2-summary-count">${count}</span></button></div><div class="sq-post-v2-summary-right"></div>`;
                if (divider) {
                    divider.parentNode.insertBefore(newSummary, divider);
                } else {
                    card.appendChild(newSummary);
                }
            }
        };

        var updateCommentsCount = (card, count, panel) => {
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

        var pushCommentToast = (message) => {
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

        var styleCommentMenuContents = (panel) => {
            if (!(panel instanceof HTMLElement)) {
                return;
            }

            const groups = panel.querySelectorAll('.sq-post-v2-menu-group');
            const forms = panel.querySelectorAll('.sq-post-v2-menu-form');
            const items = panel.querySelectorAll('.sq-post-v2-menu-item');

            groups.forEach((group) => {
                if (group instanceof HTMLElement) {
                    group.style.display = 'grid';
                    group.style.padding = '6px';
                    group.style.gap = '2px';
                }
            });

            forms.forEach((form) => {
                if (form instanceof HTMLElement) {
                    form.style.display = 'block';
                    form.style.margin = '0';
                    form.style.width = '100%';
                }
            });

            items.forEach((item) => {
                if (!(item instanceof HTMLElement)) {
                    return;
                }

                item.style.width = '100%';
                item.style.display = 'flex';
                item.style.alignItems = 'center';
                item.style.gap = '12px';
                item.style.padding = '12px 14px';
                item.style.border = '0';
                item.style.borderRadius = '12px';
                item.style.background = 'transparent';
                item.style.color = item.classList.contains('is-destructive') ? '#f87171' : 'inherit';
                item.style.fontSize = '15px';
                item.style.fontWeight = '500';
                item.style.lineHeight = '1.2';
                item.style.textAlign = 'left';
                item.style.textDecoration = 'none';
                item.style.cursor = 'pointer';
                item.style.boxSizing = 'border-box';
            });

            panel.querySelectorAll('.sq-post-v2-menu-item svg').forEach((icon) => {
                if (icon instanceof SVGElement) {
                    icon.style.display = 'block';
                    icon.style.flexShrink = '0';
                    icon.style.width = '16px';
                    icon.style.height = '16px';
                    icon.style.minWidth = '16px';
                    icon.style.minHeight = '16px';
                    icon.style.color = icon.closest('.is-destructive') ? '#f87171' : 'rgba(148, 163, 184, 0.95)';
                }
            });

            panel.querySelectorAll('.sq-post-v2-menu-item span').forEach((label) => {
                if (label instanceof HTMLElement) {
                    label.style.display = 'block';
                    label.style.flex = '1';
                    label.style.minWidth = '0';
                    label.style.whiteSpace = 'normal';
                }
            });
        };

        var closeAllCommentMenus = () => {
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
                    panel.style.display = '';
                    panel.style.position = '';
                    panel.style.top = '';
                    panel.style.right = '';
                    panel.style.bottom = '';
                    panel.style.left = '';
                    panel.style.width = '';
                    panel.style.visibility = '';
                    panel.style.maxWidth = '';
                    panel.style.padding = '';
                    panel.style.background = '';
                    panel.style.border = '';
                    panel.style.borderRadius = '';
                    panel.style.boxShadow = '';
                    panel.style.overflow = '';
                    panel.style.isolation = '';
                    panel.style.zIndex = '';
                    panel.style.transform = '';
                }
            });
        };

        var closeAllEditAndReplyShells = () => {
            document.querySelectorAll('[data-comment-edit-shell], [data-comment-reply-shell]').forEach((shell) => {
                if (shell instanceof HTMLElement) {
                    shell.hidden = true;
                }
            });
        };

        var positionCommentMenu = (root, panel) => {
            if (!(root instanceof HTMLElement) || !(panel instanceof HTMLElement)) {
                return;
            }

            const toggle = root.querySelector('[data-comment-menu-toggle]');
            if (!(toggle instanceof HTMLElement)) {
                return;
            }

            const inset = 16;
            const gap = 8;
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const maxWidth = Math.max(220, Math.min(280, viewportWidth - (inset * 2)));
            const toggleRect = toggle.getBoundingClientRect();
            const rootStyles = getComputedStyle(document.documentElement);
            const panelBackground = (rootStyles.getPropertyValue('--sq-card-strong') || '#111114').trim() || '#111114';
            const panelBorder = (rootStyles.getPropertyValue('--sq-post-v2-border') || 'rgba(255, 255, 255, 0.12)').trim() || 'rgba(255, 255, 255, 0.12)';

            panel.hidden = false;
            panel.style.display = 'block';
            panel.style.position = 'fixed';
            panel.style.top = '0';
            panel.style.right = 'auto';
            panel.style.bottom = 'auto';
            panel.style.left = '0';
            panel.style.width = `${maxWidth}px`;
            panel.style.maxWidth = `${maxWidth}px`;
            panel.style.padding = '0';
            panel.style.background = panelBackground;
            panel.style.border = `1px solid ${panelBorder}`;
            panel.style.borderRadius = '16px';
            panel.style.boxShadow = '0 20px 48px rgba(15, 23, 42, 0.28)';
            panel.style.overflow = 'hidden';
            panel.style.isolation = 'isolate';
            panel.style.zIndex = '1000';
            panel.style.transform = 'none';
            panel.style.visibility = 'hidden';
            styleCommentMenuContents(panel);

            const panelRect = panel.getBoundingClientRect();
            const panelWidth = Math.ceil(panelRect.width || panel.scrollWidth || maxWidth);
            const panelHeight = Math.ceil(panelRect.height || panel.scrollHeight || 0);
            let left = Math.round(toggleRect.right - panelWidth);
            let top = Math.round(toggleRect.bottom + gap);

            if (left < inset) {
                left = inset;
            }

            if ((left + panelWidth) > (viewportWidth - inset)) {
                left = Math.max(inset, viewportWidth - panelWidth - inset);
            }

            if ((top + panelHeight) > (viewportHeight - inset)) {
                const aboveTop = Math.round(toggleRect.top - panelHeight - gap);
                if (aboveTop >= inset) {
                    top = aboveTop;
                } else {
                    top = Math.max(inset, viewportHeight - panelHeight - inset);
                }
            }

            panel.style.left = `${left}px`;
            panel.style.top = `${top}px`;
            panel.style.visibility = '';
        };

        var updateCommentReactionUI = (row, data) => {
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


        var setCommentContent = (row, content) => {
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

        var removeCommentRow = (row, count, card) => {
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

            const reactionControl = form.closest('[data-reaction-control]');
            if (reactionControl) {
                event.preventDefault();
                const btn = form.querySelector('button');
                if (btn) btn.disabled = true;

                try {
                    const response = await window.SideQuest.postWithCsrf(form.action, {
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

            if (form.matches('[data-comment-reaction-form]')) {
                event.preventDefault();
                const row = form.closest('.sq-post-v2-comment-row');
                const button = form.querySelector('button[type="submit"]');
                const type = form.querySelector('input[name="type"]')?.value || 'like';
                if (button) button.disabled = true;

                try {
                    const response = await window.SideQuest.postWithCsrf(form.action, { type });
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

                    const response = await window.SideQuest.postWithCsrf(form.action, payload);
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
                    const response = await window.SideQuest.postWithCsrf(form.action, {
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

            if (form.matches('[data-comment-hide-form]')) {
                event.preventDefault();
                const row = form.closest('.sq-post-v2-comment-row');
                const hideBtn = form.querySelector('button[type="submit"]');
                hideBtn?.setAttribute('disabled', 'true');

                try {
                    const response = await window.SideQuest.postWithCsrf(form.action);
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

            if (form.matches('[data-comment-delete-form]')) {
                const deleteBtn = form.querySelector('button[type="submit"]');
                if (!deleteBtn) return;

                event.preventDefault();
                const confirmed = await window.SideQuest.sqConfirm({
                    title: 'Delete comment?',
                    body: 'This will permanently remove the comment.',
                    ok: 'Delete comment',
                });
                if (!confirmed) {
                    return;
                }

                deleteBtn.disabled = true;

                try {
                    const response = await window.SideQuest.postWithCsrf(form.action, { _method: 'DELETE' });
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
        window.addEventListener('scroll', closeAllCommentMenus, true);
    };
})();
