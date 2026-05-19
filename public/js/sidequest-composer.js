(function () {
    'use strict';

    window.SideQuest.initComposerMediaPicker = function () {

        document.querySelectorAll('[data-composer-dialog-open]').forEach((button) => {
            button.addEventListener('click', () => {
                var dialog = document.getElementById('composer-dialog');
                var mediaType = button.dataset.mediaType;

                if (dialog instanceof HTMLDialogElement) {
                    if (mediaType) {
                        var mediaInput = dialog.querySelector('[data-composer-media]');
                        if (mediaInput instanceof HTMLInputElement) {
                            if (mediaType === 'image') {
                                mediaInput.accept = 'image/jpeg,image/png,image/gif,image/webp';
                            } else if (mediaType === 'video') {
                                mediaInput.accept = 'video/mp4,video/webm,video/quicktime';
                            }

                            mediaInput.dataset.openDialogAfterSelect = 'true';
                            mediaInput.click();
                            return;
                        }
                    }

                    dialog.showModal();
                    document.body.style.overflow = 'hidden';
                    var textarea = dialog.querySelector('[data-composer-input]');
                    if (textarea instanceof HTMLTextAreaElement) {
                        requestAnimationFrame(() => textarea.focus());
                    }
                }
            });
        });

        document.querySelectorAll('[data-composer-dialog-close]').forEach((button) => {
            button.addEventListener('click', () => {
                var dialog = document.getElementById('composer-dialog');
                if (dialog instanceof HTMLDialogElement) {
                    dialog.close();
                    document.body.style.overflow = '';
                }
            });
        });

        var composerDialog = document.getElementById('composer-dialog');
        if (composerDialog instanceof HTMLDialogElement) {
            composerDialog.addEventListener('click', (event) => {
                if (event.target === composerDialog) {
                    composerDialog.close();
                    document.body.style.overflow = '';
                }
            });

            composerDialog.addEventListener('close', () => {
                document.body.style.overflow = '';
            });
        }

        document.querySelectorAll('[data-post-composer]').forEach((form) => {
            if (form.dataset.composerBound === 'true') {
                return;
            }

            var mediaInput = form.querySelector('[data-composer-media]');
            var textarea = form.querySelector('[data-composer-input]');
            var meta = form.querySelector('[data-composer-file-meta]');
            var preview = form.querySelector('[data-composer-preview]');
            var previewMedia = form.querySelector('[data-composer-preview-media]');
            var removeMedia = form.querySelector('[data-composer-remove-media]');

            if (!(mediaInput instanceof HTMLInputElement) || !(textarea instanceof HTMLTextAreaElement)) {
                return;
            }

            form.dataset.composerBound = 'true';
            var previewUrls = [];

            var autoSize = () => {
                textarea.style.height = 'auto';
                textarea.style.height = `${Math.max(textarea.scrollHeight, 120)}px`;
            };

            var clearPreview = ({ clearInput = false } = {}) => {
                previewUrls.forEach((url) => URL.revokeObjectURL(url));
                previewUrls = [];

                if (previewMedia instanceof HTMLElement) {
                    previewMedia.innerHTML = '';
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

            var renderPreview = () => {
                var files = Array.from(mediaInput.files || []);
                if (files.length === 0) {
                    clearPreview();
                    return;
                }

                previewUrls.forEach((url) => URL.revokeObjectURL(url));
                previewUrls = [];

                if (!(previewMedia instanceof HTMLElement)) {
                    return;
                }

                previewMedia.innerHTML = '';
                var isFirst = true;

                files.forEach((file, i) => {
                    var url = URL.createObjectURL(file);
                    previewUrls.push(url);

                    if (file.type.startsWith('video/')) {
                        var videoWrapper = document.createElement('div');
                        videoWrapper.className = 'sq-composer-preview-thumb';
                        var video = document.createElement('video');
                        video.className = 'sq-composer-preview-media-el';
                        video.src = url;
                        video.controls = true;
                        video.preload = 'metadata';
                        videoWrapper.appendChild(video);
                        previewMedia.appendChild(videoWrapper);
                    } else {
                        var imgWrapper = document.createElement('div');
                        imgWrapper.className = 'sq-composer-preview-thumb';
                        var img = document.createElement('img');
                        img.className = 'sq-composer-preview-media-el';
                        img.src = url;
                        img.alt = 'Preview ' + (i + 1);
                        img.loading = 'lazy';
                        imgWrapper.appendChild(img);
                        previewMedia.appendChild(imgWrapper);
                    }

                    isFirst = false;
                });

                if (meta instanceof HTMLElement) {
                    meta.textContent = files.length === 1
                        ? 'Attached: ' + files[0].name
                        : 'Attached: ' + files.length + ' files';
                }

                if (preview instanceof HTMLElement) {
                    preview.hidden = false;
                }

                if (mediaInput.dataset.openDialogAfterSelect === 'true') {
                    delete mediaInput.dataset.openDialogAfterSelect;
                    var dialog = document.getElementById('composer-dialog');
                    if (dialog instanceof HTMLDialogElement && !dialog.open) {
                        dialog.showModal();
                        document.body.style.overflow = 'hidden';
                        var ta = dialog.querySelector('[data-composer-input]');
                        if (ta instanceof HTMLTextAreaElement) {
                            requestAnimationFrame(() => ta.focus());
                        }
                    }
                }
            };

            textarea.addEventListener('input', autoSize);

            form.querySelectorAll('[data-media-trigger]').forEach((button) => {
                button.addEventListener('click', () => {
                    var kind = button.getAttribute('data-media-trigger') || 'all';
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

        var composerForm = document.getElementById('composer-form');
        if (composerForm instanceof HTMLFormElement) {
            composerForm.addEventListener('submit', () => {
            });
        }
    };
})();
