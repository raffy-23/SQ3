(function () {
    'use strict';

    window.SideQuest.initPostMenus = function () {
        if (document.body?.dataset.sideQuestPostUiBound === 'true') {
            return;
        }

        if (document.body) {
            document.body.dataset.sideQuestPostUiBound = 'true';
        }

        var galleryViewer = null;
        var galleryState = {
            items: [],
            index: 0,
            lastFocused: null,
            previousBodyOverflow: '',
            scale: 1,
            panX: 0,
            panY: 0,
            touchMode: '',
            touchStartX: 0,
            touchStartY: 0,
            touchLastX: 0,
            touchLastY: 0,
            pinchStartDistance: 0,
            pinchStartScale: 1,
        };

        var clamp = (value, min, max) => {
            return Math.min(max, Math.max(min, value));
        };

        var getTouchDistance = (touches) => {
            if (!touches || touches.length < 2) {
                return 0;
            }

            var deltaX = touches[0].clientX - touches[1].clientX;
            var deltaY = touches[0].clientY - touches[1].clientY;
            return Math.hypot(deltaX, deltaY);
        };

        var setPreviewItemReady = (item, ready) => {
            if (!(item instanceof HTMLElement)) {
                return;
            }

            item.classList.toggle('is-ready', ready);
        };

        var bindPreviewImageState = (image) => {
            if (!(image instanceof HTMLImageElement)) {
                return;
            }

            var item = image.closest('.sq-post-v2-media-item');
            if (!(item instanceof HTMLElement)) {
                return;
            }

            if (image.dataset.previewStateBound === 'true') {
                if (image.complete && image.naturalWidth > 0) {
                    setPreviewItemReady(item, true);
                }
                return;
            }

            image.dataset.previewStateBound = 'true';
            setPreviewItemReady(item, image.complete && image.naturalWidth > 0);

            var markReady = () => setPreviewItemReady(item, true);
            image.addEventListener('load', markReady, { once: true });
            image.addEventListener('error', markReady, { once: true });
        };

        var bindPreviewImages = (root) => {
            if (!(root instanceof Element || root instanceof Document)) {
                return;
            }

            root.querySelectorAll('.sq-post-v2-media-item > img').forEach((image) => {
                bindPreviewImageState(image);
            });
        };

        var syncGalleryImageTransform = () => {
            var viewer = ensureGalleryViewer();
            var media = viewer.querySelector('[data-gallery-media]');
            var image = viewer.querySelector('[data-gallery-image]');
            if (!(media instanceof HTMLElement) || !(image instanceof HTMLImageElement)) {
                return;
            }

            if (galleryState.scale <= 1) {
                galleryState.scale = 1;
                galleryState.panX = 0;
                galleryState.panY = 0;
            }

            var maxPanX = Math.max(0, (media.clientWidth * (galleryState.scale - 1)) / 2);
            var maxPanY = Math.max(0, (media.clientHeight * (galleryState.scale - 1)) / 2);
            galleryState.panX = clamp(galleryState.panX, -maxPanX, maxPanX);
            galleryState.panY = clamp(galleryState.panY, -maxPanY, maxPanY);

            image.style.transform = 'translate3d(' + galleryState.panX + 'px, ' + galleryState.panY + 'px, 0) scale(' + galleryState.scale + ')';
            media.classList.toggle('is-zoomed', galleryState.scale > 1.01);
        };

        var resetGalleryImageTransform = () => {
            galleryState.scale = 1;
            galleryState.panX = 0;
            galleryState.panY = 0;
            galleryState.touchMode = '';
            syncGalleryImageTransform();
        };

        var setGalleryMediaLoading = (loading) => {
            var viewer = ensureGalleryViewer();
            var media = viewer.querySelector('[data-gallery-media]');
            if (!(media instanceof HTMLElement)) {
                return;
            }

            media.classList.toggle('is-loading', loading);
        };

        var handleGalleryTouchStart = (event) => {
            if (!(galleryViewer instanceof HTMLElement) || galleryViewer.hidden) {
                return;
            }

            if (event.touches.length >= 2) {
                galleryState.touchMode = 'pinch';
                galleryState.pinchStartDistance = getTouchDistance(event.touches);
                galleryState.pinchStartScale = galleryState.scale;
                return;
            }

            if (event.touches.length === 1) {
                var touch = event.touches[0];
                galleryState.touchStartX = touch.clientX;
                galleryState.touchStartY = touch.clientY;
                galleryState.touchLastX = touch.clientX;
                galleryState.touchLastY = touch.clientY;
                galleryState.touchMode = galleryState.scale > 1 ? 'pan' : 'swipe';
            }
        };

        var handleGalleryTouchMove = (event) => {
            if (!(galleryViewer instanceof HTMLElement) || galleryViewer.hidden) {
                return;
            }

            if (galleryState.touchMode === 'pinch' && event.touches.length >= 2) {
                var nextDistance = getTouchDistance(event.touches);
                if (galleryState.pinchStartDistance > 0) {
                    event.preventDefault();
                    galleryState.scale = clamp((nextDistance / galleryState.pinchStartDistance) * galleryState.pinchStartScale, 1, 4);
                    syncGalleryImageTransform();
                }
                return;
            }

            if (galleryState.touchMode === 'pan' && event.touches.length === 1) {
                var touch = event.touches[0];
                event.preventDefault();
                galleryState.panX += touch.clientX - galleryState.touchLastX;
                galleryState.panY += touch.clientY - galleryState.touchLastY;
                galleryState.touchLastX = touch.clientX;
                galleryState.touchLastY = touch.clientY;
                syncGalleryImageTransform();
                return;
            }

            if (galleryState.touchMode === 'swipe' && event.touches.length === 1) {
                var swipeTouch = event.touches[0];
                galleryState.touchLastX = swipeTouch.clientX;
                galleryState.touchLastY = swipeTouch.clientY;
            }
        };

        var handleGalleryTouchEnd = (event) => {
            if (!(galleryViewer instanceof HTMLElement) || galleryViewer.hidden) {
                return;
            }

            if (galleryState.touchMode === 'pinch' && event.touches.length === 1) {
                galleryState.touchMode = galleryState.scale > 1 ? 'pan' : 'swipe';
                galleryState.touchLastX = event.touches[0].clientX;
                galleryState.touchLastY = event.touches[0].clientY;
                return;
            }

            if (galleryState.touchMode === 'swipe' && event.touches.length === 0) {
                var deltaX = galleryState.touchLastX - galleryState.touchStartX;
                var deltaY = galleryState.touchLastY - galleryState.touchStartY;
                if (Math.abs(deltaX) > 48 && Math.abs(deltaX) > Math.abs(deltaY) * 1.15) {
                    moveGallery(deltaX < 0 ? 1 : -1);
                }
            }

            galleryState.touchMode = '';
            if (galleryState.scale <= 1.01) {
                resetGalleryImageTransform();
            }
        };

        var ensureGalleryViewer = () => {
            if (galleryViewer instanceof HTMLElement) {
                return galleryViewer;
            }

            galleryViewer = document.createElement('div');
            galleryViewer.className = 'sq-post-gallery-viewer';
            galleryViewer.hidden = true;
            galleryViewer.innerHTML = [
                '<div class="sq-post-gallery-viewer-backdrop" data-gallery-close></div>',
                '<div class="sq-post-gallery-viewer-shell" role="dialog" aria-modal="true" aria-label="Post media viewer">',
                '    <div class="sq-post-gallery-viewer-topbar">',
                '        <div class="sq-post-gallery-viewer-count" data-gallery-count></div>',
                '        <button type="button" class="sq-post-gallery-viewer-close" data-gallery-close aria-label="Close viewer">',
                '            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
                '        </button>',
                '    </div>',
                '    <div class="sq-post-gallery-viewer-stage">',
                '        <button type="button" class="sq-post-gallery-viewer-nav" data-gallery-nav="prev" aria-label="Previous">',
                '            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>',
                '        </button>',
                '        <div class="sq-post-gallery-viewer-media is-loading" data-gallery-media>',
                '            <img class="sq-post-gallery-viewer-image" data-gallery-image alt="">',
                '            <video class="sq-post-gallery-viewer-video" data-gallery-video controls preload="metadata" hidden></video>',
                '        </div>',
                '        <button type="button" class="sq-post-gallery-viewer-nav" data-gallery-nav="next" aria-label="Next">',
                '            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>',
                '        </button>',
                '    </div>',
                '    <div class="sq-post-gallery-viewer-filmstrip" data-gallery-filmstrip></div>',
                '</div>',
            ].join('');

            document.body.appendChild(galleryViewer);
            var stageMedia = galleryViewer.querySelector('[data-gallery-media]');
            var stageImage = galleryViewer.querySelector('[data-gallery-image]');
            if (stageMedia instanceof HTMLElement) {
                stageMedia.addEventListener('touchstart', handleGalleryTouchStart, { passive: false });
                stageMedia.addEventListener('touchmove', handleGalleryTouchMove, { passive: false });
                stageMedia.addEventListener('touchend', handleGalleryTouchEnd, { passive: false });
                stageMedia.addEventListener('touchcancel', handleGalleryTouchEnd, { passive: false });
            }
            if (stageImage instanceof HTMLImageElement) {
                stageImage.draggable = false;
                stageImage.decoding = 'async';
            }
            var stageVideo = galleryViewer.querySelector('[data-gallery-video]');
            if (stageVideo instanceof HTMLVideoElement) {
                stageVideo.addEventListener('click', (e) => e.stopPropagation());
            }
            return galleryViewer;
        };

        var closeGallery = () => {
            var viewer = ensureGalleryViewer();
            if (viewer.hidden) {
                return;
            }

            viewer.hidden = true;
            document.body.style.overflow = galleryState.previousBodyOverflow;

            if (galleryState.lastFocused instanceof HTMLElement) {
                galleryState.lastFocused.focus();
            }
        };

        var renderGallery = () => {
            var viewer = ensureGalleryViewer();
            var image = viewer.querySelector('[data-gallery-image]');
            var videoEl = viewer.querySelector('[data-gallery-video]');
            var count = viewer.querySelector('[data-gallery-count]');
            var filmstrip = viewer.querySelector('[data-gallery-filmstrip]');
            var media = viewer.querySelector('[data-gallery-media]');
            var prev = viewer.querySelector('[data-gallery-nav="prev"]');
            var next = viewer.querySelector('[data-gallery-nav="next"]');

            if (!(count instanceof HTMLElement) || !(filmstrip instanceof HTMLElement) || !(media instanceof HTMLElement)) {
                return;
            }

            var total = galleryState.items.length;
            var safeIndex = Math.max(0, Math.min(galleryState.index, Math.max(0, total - 1)));
            galleryState.index = safeIndex;

            var currentItem = galleryState.items[safeIndex] || { url: '', type: 'image' };
            var isVideo = currentItem.type === 'video';

            resetGalleryImageTransform();
            setGalleryMediaLoading(true);
            count.textContent = (isVideo ? 'Video ' : 'Image ') + (safeIndex + 1) + ' of ' + total;

            var markViewerReady = () => {
                setGalleryMediaLoading(false);
                syncGalleryImageTransform();
            };

            if (isVideo && videoEl instanceof HTMLVideoElement) {
                if (image instanceof HTMLImageElement) { image.hidden = true; image.src = ''; }
                videoEl.hidden = false;
                videoEl.src = currentItem.url;
                videoEl.load();
                videoEl.oncanplay = markViewerReady;
                videoEl.onerror = markViewerReady;
            } else if (image instanceof HTMLImageElement) {
                if (videoEl instanceof HTMLVideoElement) { videoEl.hidden = true; videoEl.pause(); videoEl.src = ''; }
                image.hidden = false;
                image.src = currentItem.url;
                image.alt = 'Post image ' + (safeIndex + 1) + ' of ' + total;
                image.loading = 'eager';
                image.onload = markViewerReady;
                image.onerror = markViewerReady;
                if (image.complete && image.naturalWidth > 0) {
                    requestAnimationFrame(markViewerReady);
                }
            }

            filmstrip.innerHTML = '';
            galleryState.items.forEach((item, index) => {
                var thumb = document.createElement('button');
                thumb.type = 'button';
                thumb.className = 'sq-post-gallery-viewer-thumb is-loading' + (index === safeIndex ? ' is-active' : '') + (item.type === 'video' ? ' is-video' : '');
                thumb.dataset.galleryThumb = String(index);
                thumb.setAttribute('aria-label', (item.type === 'video' ? 'View video ' : 'View image ') + (index + 1));

                if (item.type === 'video') {
                    var thumbVideo = document.createElement('video');
                    thumbVideo.src = item.url;
                    thumbVideo.muted = true;
                    thumbVideo.preload = 'metadata';
                    thumbVideo.addEventListener('loadeddata', () => thumb.classList.remove('is-loading'), { once: true });
                    thumbVideo.addEventListener('error', () => thumb.classList.remove('is-loading'), { once: true });
                    var playIcon = document.createElement('span');
                    playIcon.className = 'sq-post-gallery-viewer-thumb-video-icon';
                    playIcon.innerHTML = '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>';
                    thumb.appendChild(thumbVideo);
                    thumb.appendChild(playIcon);
                } else {
                    var thumbImage = document.createElement('img');
                    thumbImage.src = item.url;
                    thumbImage.alt = '';
                    thumbImage.loading = 'lazy';
                    thumbImage.decoding = 'async';
                    thumbImage.addEventListener('load', () => thumb.classList.remove('is-loading'), { once: true });
                    thumbImage.addEventListener('error', () => thumb.classList.remove('is-loading'), { once: true });
                    if (thumbImage.complete && thumbImage.naturalWidth > 0) {
                        thumb.classList.remove('is-loading');
                    }
                    thumb.appendChild(thumbImage);
                }

                filmstrip.appendChild(thumb);
            });

            requestAnimationFrame(() => {
                filmstrip.querySelector('.sq-post-gallery-viewer-thumb.is-active')?.scrollIntoView({
                    behavior: 'smooth', block: 'nearest', inline: 'center',
                });
            });

            if (prev instanceof HTMLButtonElement) { prev.disabled = safeIndex === 0; }
            if (next instanceof HTMLButtonElement) { next.disabled = safeIndex >= total - 1; }
        };

        var openGallery = (items, startIndex, trigger) => {
            if (!Array.isArray(items) || items.length === 0) {
                return;
            }

            ensureGalleryViewer();
            galleryState.items = items.filter((item) => item && typeof item.url === 'string' && item.url !== '');
            galleryState.index = Math.max(0, Math.min(startIndex, galleryState.items.length - 1));
            galleryState.lastFocused = trigger instanceof HTMLElement ? trigger : null;
            galleryState.previousBodyOverflow = document.body.style.overflow;
            resetGalleryImageTransform();
            renderGallery();
            galleryViewer.hidden = false;
            document.body.style.overflow = 'hidden';
        };

        bindPreviewImages(document);
        if (document.body?.dataset.sideQuestPreviewObserverBound !== 'true') {
            document.body.dataset.sideQuestPreviewObserverBound = 'true';
            var previewObserver = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (!(node instanceof HTMLElement)) {
                            return;
                        }

                        if (node.matches('.sq-post-v2-media-item > img')) {
                            bindPreviewImageState(node);
                            return;
                        }

                        bindPreviewImages(node);
                    });
                });
            });

            previewObserver.observe(document.body, {
                childList: true,
                subtree: true,
            });
        }

        var moveGallery = (direction) => {
            if (!(galleryViewer instanceof HTMLElement) || galleryViewer.hidden) {
                return;
            }

            var nextIndex = galleryState.index + direction;
            if (nextIndex < 0 || nextIndex >= galleryState.items.length) {
                return;
            }

            galleryState.index = nextIndex;
            renderGallery();
        };

        var postMultipartWithCsrf = async function (url, formData) {
            var headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            };
            headers[window.SideQuest.csrfHeader()] = window.SideQuest.csrfToken();
            return fetch(url, {
                method: 'POST',
                headers: headers,
                body: formData,
                credentials: 'same-origin',
            });
        };

        var renderNewFilePreviews = (fileInput) => {
            var form = fileInput.closest('[data-post-edit-form]');
            var manager = form?.querySelector('[data-post-edit-media-manager]');
            var previewContainer = manager?.querySelector('[data-post-edit-media-preview]');
            if (!previewContainer) return;
            if (manager) { manager.hidden = false; }
            
            previewContainer.querySelectorAll('.sq-post-v2-edit-media-item:not([data-existing-media])').forEach(function (item) {
                item.remove();
            });
            
            var dt = fileInput._dataTransfer || new DataTransfer();
            for (var i = 0; i < dt.files.length; i++) {
                var file = dt.files[i];
                var url = URL.createObjectURL(file);
                var isImg = file.type.startsWith('image/');
                
                var item = document.createElement('div');
                item.className = 'sq-post-v2-edit-media-item';
                item.dataset.newFileIndex = String(i);
                
                if (isImg) {
                    var img = document.createElement('img');
                    img.src = url;
                    img.alt = '';
                    item.appendChild(img);
                } else {
                    var video = document.createElement('video');
                    video.src = url;
                    video.muted = true;
                    item.appendChild(video);
                }
                
                var removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'sq-post-v2-edit-media-remove';
                removeBtn.setAttribute('aria-label', 'Remove media');
                removeBtn.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>';
                
                item.appendChild(removeBtn);
                previewContainer.appendChild(item);
            }
        };

        var stylePostMenuContents = (panel) => {
            if (!(panel instanceof HTMLElement)) {
                return;
            }

            var header = panel.querySelector('.sq-post-v2-menu-header');
            var groups = panel.querySelectorAll('.sq-post-v2-menu-group');
            var separator = panel.querySelector('.sq-post-v2-menu-separator');
            var forms = panel.querySelectorAll('.sq-post-v2-menu-form');
            var items = panel.querySelectorAll('.sq-post-v2-menu-item');

            if (header instanceof HTMLElement) {
                header.style.display = 'grid';
                header.style.gap = '2px';
                header.style.padding = '14px 16px 12px';
                header.style.borderBottom = '1px solid rgba(148, 163, 184, 0.16)';
            }

            groups.forEach((group) => {
                if (group instanceof HTMLElement) {
                    group.style.display = 'grid';
                    group.style.padding = '6px';
                    group.style.gap = '2px';
                }
            });

            if (separator instanceof HTMLElement) {
                separator.style.height = '1px';
                separator.style.margin = '2px 0';
                separator.style.background = 'rgba(148, 163, 184, 0.16)';
            }

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

            panel.querySelectorAll('.sq-post-v2-menu-header strong').forEach((title) => {
                if (title instanceof HTMLElement) {
                    title.style.display = 'block';
                    title.style.fontSize = '15px';
                    title.style.fontWeight = '700';
                    title.style.lineHeight = '1.2';
                }
            });

            panel.querySelectorAll('.sq-post-v2-menu-header span').forEach((subtitle) => {
                if (subtitle instanceof HTMLElement) {
                    subtitle.style.display = 'block';
                    subtitle.style.fontSize = '13px';
                    subtitle.style.lineHeight = '1.3';
                    subtitle.style.color = 'rgba(148, 163, 184, 0.9)';
                }
            });
        };

        var closeAllMenus = () => {
            document.querySelectorAll('[data-post-menu-root]').forEach((root) => {
                var toggle = root.querySelector('[data-post-menu-toggle]');
                var panel = root.querySelector('[data-post-menu-panel]');
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
                    panel.style.maxWidth = '';
                    panel.style.padding = '';
                    panel.style.background = '';
                    panel.style.border = '';
                    panel.style.borderRadius = '';
                    panel.style.boxShadow = '';
                    panel.style.overflow = '';
                    panel.style.isolation = '';
                    panel.style.zIndex = '';
                    panel.style.visibility = '';
                }
            });
        };

        var positionPostMenu = (toggle, panel) => {
            if (!(toggle instanceof HTMLElement) || !(panel instanceof HTMLElement)) {
                return;
            }

            var inset = 16;
            var gap = 8;
            var viewportWidth = window.innerWidth;
            var viewportHeight = window.innerHeight;
            var maxWidth = Math.max(220, Math.min(280, viewportWidth - (inset * 2)));
            var toggleRect = toggle.getBoundingClientRect();
            var rootStyles = getComputedStyle(document.documentElement);
            var panelBackground = (rootStyles.getPropertyValue('--sq-card-strong') || '#111114').trim() || '#111114';
            var panelBorder = (rootStyles.getPropertyValue('--sq-post-v2-border') || 'rgba(255, 255, 255, 0.12)').trim() || 'rgba(255, 255, 255, 0.12)';

            panel.hidden = false;
            panel.style.display = 'block';
            panel.style.position = 'fixed';
            panel.style.top = '0';
            panel.style.right = 'auto';
            panel.style.bottom = 'auto';
            panel.style.left = '0';
            panel.style.width = maxWidth + 'px';
            panel.style.maxWidth = maxWidth + 'px';
            panel.style.padding = '0';
            panel.style.background = panelBackground;
            panel.style.border = '1px solid ' + panelBorder;
            panel.style.borderRadius = '16px';
            panel.style.boxShadow = '0 20px 48px rgba(15, 23, 42, 0.28)';
            panel.style.overflow = 'hidden';
            panel.style.isolation = 'isolate';
            panel.style.zIndex = '1000';
            panel.style.visibility = 'hidden';
            stylePostMenuContents(panel);

            var panelRect = panel.getBoundingClientRect();
            var panelWidth = Math.ceil(panelRect.width || panel.scrollWidth || maxWidth);
            var panelHeight = Math.ceil(panelRect.height || panel.scrollHeight || 0);
            var left = Math.round(toggleRect.right - panelWidth);
            var top = Math.round(toggleRect.bottom + gap);

            if (left < inset) {
                left = inset;
            }

            if ((left + panelWidth) > (viewportWidth - inset)) {
                left = Math.max(inset, viewportWidth - panelWidth - inset);
            }

            if ((top + panelHeight) > (viewportHeight - inset)) {
                var aboveTop = Math.round(toggleRect.top - panelHeight - gap);
                if (aboveTop >= inset) {
                    top = aboveTop;
                } else {
                    top = Math.max(inset, viewportHeight - panelHeight - inset);
                }
            }

            panel.style.left = left + 'px';
            panel.style.top = top + 'px';
            panel.style.visibility = '';
        };

        var setPostContent = (card, content) => {
            var contentNode = card.querySelector('[data-post-content]');
            if (!contentNode && content !== '') {
                contentNode = document.createElement('div');
                contentNode.className = 'sq-post-v2-content';
                contentNode.dataset.postContent = 'true';
                var media = card.querySelector('.sq-post-v2-media');
                var editShell = card.querySelector('[data-post-edit-shell]');
                var insertBefore = media || editShell?.nextElementSibling || card.querySelector('.sq-post-v2-summary') || card.querySelector('.sq-post-v2-divider');
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

        var showHiddenComments = async (card, url) => {
            if (!(card instanceof HTMLElement) || !url) {
                return;
            }

            var panel = card.querySelector('.sq-post-v2-comments');
            var root = panel?.querySelector('[data-hidden-comments-root]');
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
                var response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                var data = await response.json();

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
            var galleryTrigger = event.target.closest('[data-post-gallery-open]');
            if (galleryTrigger) {
                var galleryRoot = galleryTrigger.closest('[data-post-gallery]');
                var items = [];

                if (galleryRoot instanceof HTMLElement) {
                    try {
                        var parsed = JSON.parse(galleryRoot.dataset.postGallery || '[]');
                        // Support both [{url,type}] objects and legacy ["url"] strings
                        items = parsed.map((entry) => {
                            if (typeof entry === 'string') return { url: entry, type: 'image' };
                            return { url: String(entry.url || ''), type: String(entry.type || 'image') };
                        });
                    } catch (error) {
                        console.error('Parse post gallery failed:', error);
                    }
                }

                event.preventDefault();
                closeAllMenus();
                openGallery(items, Number(galleryTrigger.dataset.galleryIndex || 0), galleryTrigger);
                return;
            }

            var toggle = event.target.closest('[data-post-menu-toggle]');
            if (toggle) {
                event.preventDefault();
                event.stopPropagation();
                var root = toggle.closest('[data-post-menu-root]');
                var panel = root?.querySelector('[data-post-menu-panel]');
                var willOpen = panel?.hidden ?? false;
                closeAllMenus();
                if (root && panel instanceof HTMLElement && willOpen) {
                    positionPostMenu(toggle, panel);
                    toggle.setAttribute('aria-expanded', 'true');
                }
                return;
            }

            var mediaRemove = event.target.closest('.sq-post-v2-edit-media-remove');
            if (mediaRemove) {
                event.preventDefault();
                var mediaItem = mediaRemove.closest('.sq-post-v2-edit-media-item');
                if (mediaItem) {
                    if (mediaItem.dataset.existingMedia) {
                        mediaItem.remove();
                    } else {
                        var fileInput = mediaItem.closest('[data-post-edit-media-manager]')?.querySelector('[data-post-edit-file-input]');
                        var fileIndex = Number(mediaItem.dataset.newFileIndex);
                        if (fileInput && fileInput._dataTransfer) {
                            var dt = fileInput._dataTransfer;
                            var newDt = new DataTransfer();
                            for (var i = 0; i < dt.files.length; i++) {
                                if (i !== fileIndex) {
                                    newDt.items.add(dt.files[i]);
                                }
                            }
                            fileInput._dataTransfer = newDt;
                            fileInput.files = newDt.files;
                            renderNewFilePreviews(fileInput);
                        }
                    }
                }
                return;
            }

            var mediaAdd = event.target.closest('[data-post-edit-media-add-btn]');
            if (mediaAdd) {
                event.preventDefault();
                var editForm = mediaAdd.closest('[data-post-edit-form]');
                var fileInput = editForm?.querySelector('[data-post-edit-file-input]');
                // Reveal the media manager container (hidden for text-only posts)
                var manager = editForm?.querySelector('[data-post-edit-media-manager]');
                if (manager) { manager.hidden = false; }
                if (fileInput instanceof HTMLInputElement) {
                    fileInput.click();
                }
                return;
            }

            var editStart = event.target.closest('[data-post-edit-start]');
            if (editStart) {
                var card = editStart.closest('.sq-post-card-v2');
                var shell = card?.querySelector('[data-post-edit-shell]');
                var field = shell?.querySelector('[data-post-edit-field]');
                closeAllMenus();
                if (shell instanceof HTMLElement) {
                    shell.hidden = false;
                    var fileInput = shell.querySelector('[data-post-edit-file-input]');
                    if (fileInput instanceof HTMLInputElement) {
                        fileInput.value = '';
                        fileInput._dataTransfer = new DataTransfer();
                    }
                }
                if (field instanceof HTMLTextAreaElement) {
                    requestAnimationFrame(() => {
                        field.focus();
                        field.setSelectionRange(field.value.length, field.value.length);
                    });
                }
                return;
            }

            var hiddenCommentsView = event.target.closest('[data-post-hidden-comments-view]');
            if (hiddenCommentsView) {
                var card = hiddenCommentsView.closest('.sq-post-card-v2');
                closeAllMenus();
                showHiddenComments(card, hiddenCommentsView.dataset.url || '');
                return;
            }

            var editCancel = event.target.closest('[data-post-edit-cancel]');
            if (editCancel) {
                var shell = editCancel.closest('[data-post-edit-shell]');
                if (shell instanceof HTMLElement) {
                    shell.hidden = true;
                }
                return;
            }

            var galleryClose = event.target.closest('[data-gallery-close]');
            if (galleryClose) {
                event.preventDefault();
                closeGallery();
                return;
            }

            var galleryNav = event.target.closest('[data-gallery-nav]');
            if (galleryNav) {
                event.preventDefault();
                moveGallery(galleryNav.dataset.galleryNav === 'next' ? 1 : -1);
                return;
            }

            var galleryThumb = event.target.closest('[data-gallery-thumb]');
            if (galleryThumb) {
                event.preventDefault();
                galleryState.index = Number(galleryThumb.dataset.galleryThumb || 0);
                renderGallery();
                return;
            }

            if (!event.target.closest('[data-post-menu-root]')) {
                closeAllMenus();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (galleryViewer instanceof HTMLElement && !galleryViewer.hidden) {
                if (event.key === 'Escape') {
                    closeGallery();
                    return;
                }

                if (event.key === 'ArrowLeft') {
                    event.preventDefault();
                    moveGallery(-1);
                    return;
                }

                if (event.key === 'ArrowRight') {
                    event.preventDefault();
                    moveGallery(1);
                    return;
                }
            }

            if (event.key === 'Escape') {
                closeAllMenus();
                document.querySelectorAll('[data-post-edit-shell]').forEach((shell) => {
                    shell.hidden = true;
                });
            }
        });

        window.addEventListener('resize', closeAllMenus);
        window.addEventListener('scroll', closeAllMenus, true);

        document.addEventListener('change', (event) => {
            var fileInput = event.target.closest('[data-post-edit-file-input]');
            if (fileInput instanceof HTMLInputElement && fileInput.files) {
                var dt = fileInput._dataTransfer || new DataTransfer();
                for (var i = 0; i < fileInput.files.length; i++) {
                    dt.items.add(fileInput.files[i]);
                }
                fileInput._dataTransfer = dt;
                fileInput.files = dt.files;
                renderNewFilePreviews(fileInput);
            }
        });

        document.addEventListener('submit', async (event) => {
            var form = event.target.closest('form');
            if (!form) {
                return;
            }

            var card = form.closest('.sq-post-card-v2');
            if (!card) {
                return;
            }

            if (form.matches('[data-post-edit-form]')) {
                event.preventDefault();
                var field = form.querySelector('[data-post-edit-field]');
                var submitBtn = form.querySelector('button[type="submit"]');
                if (!(field instanceof HTMLTextAreaElement)) {
                    return;
                }

                submitBtn?.setAttribute('disabled', 'true');
                try {
                    var formData = new FormData(form);
                    var response = await postMultipartWithCsrf(form.action, formData);
                    var data = await response.json();
                    if (response.ok && data.success) {
                        card.outerHTML = data.html;
                    } else if (data.errors || data.error) {
                        alert(data.error || Object.values(data.errors).join('\n'));
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
                var methodInput = form.querySelector('[data-post-save-method]');
                var label = form.querySelector('[data-post-save-label]');
                var button = form.querySelector('[data-post-save-button]');
                var isSaved = card.dataset.postSaved === 'true';
                try {
                    var response = await window.SideQuest.postWithCsrf(form.action, isSaved ? { _method: 'DELETE' } : {});
                    var data = await response.json();
                    if (response.ok && data.success) {
                        var nextSaved = !!data.is_saved;
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
                    var response = await window.SideQuest.postWithCsrf(form.action);
                    var data = await response.json();
                    if (response.ok && data.success) {
                        var list = card.parentElement;
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
                var confirmed = await window.SideQuest.sqConfirm({
                    title: 'Delete post?',
                    body: 'This will permanently remove the post and all its comments.',
                    ok: 'Delete post',
                });
                if (!confirmed) {
                    return;
                }

                try {
                    var response = await window.SideQuest.postWithCsrf(form.action, { _method: 'DELETE' });
                    var data = await response.json();
                    if (response.ok && data.success) {
                        var list = card.parentElement;
                        card.remove();
                        syncFeedEmptyState(list);
                    }
                } catch (error) {
                    console.error('Delete post failed:', error);
                }
            }
        });
    };
})();
