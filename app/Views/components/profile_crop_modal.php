<?php
/**
 * Profile Crop Modal Component
 *
 * Analogous to profile-crop-modal.tsx in the Laravel/React version.
 * Include this view wherever a profile-picture upload with cropping is needed.
 *
 * Requirements:
 *  - A <meta name="csrf-token"> and <meta name="csrf-token-name"> must exist in <head>.
 *  - The file input that triggers the modal must have id="profile_picture".
 *  - The upload endpoint is resolved via site_url('profile-picture').
 */
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<style>
/* ── Round crop shape (mirrors cropShape="round" in react-easy-crop) ── */
#profile-crop-dialog .cropper-view-box,
#profile-crop-dialog .cropper-face {
    border-radius: 50%;
}
#profile-crop-dialog .cropper-view-box {
    box-shadow: 0 0 0 1.5px var(--sq-primary);
    outline: 0;
}
/* Hide resize handles — fixed circle, not resizable */
#profile-crop-dialog .cropper-dashed,
#profile-crop-dialog .cropper-point,
#profile-crop-dialog .cropper-line {
    display: none;
}
/* ── Replace checkerboard with solid theme background ── */
#profile-crop-dialog .cropper-bg {
    background-image: none !important;
}
#profile-crop-dialog .cropper-canvas,
#profile-crop-dialog .cropper-drag-box,
#profile-crop-dialog .cropper-wrap-box {
    background: var(--sq-card-strong);
}
/* Zoom slider */
#profile-crop-zoom {
    width: 8rem;
    accent-color: var(--sq-primary);
    cursor: pointer;
}
.sq-cropper-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 0.75rem 1rem;
    border-top: 1px solid var(--sq-border);
    background: var(--sq-card-strong);
}
.sq-cropper-zoom-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.sq-icon-btn {
    background: transparent;
    border: 1px solid var(--sq-border);
    border-radius: 0.375rem;
    padding: 0.35rem;
    cursor: pointer;
    color: var(--sq-muted-text, #6b7280);
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
.sq-icon-btn:hover {
    background: var(--sq-hover, rgba(0,0,0,0.06));
}
#profile-crop-dialog::backdrop {
    background: rgba(0, 0, 0, 0.72);
    backdrop-filter: blur(4px);
}
</style>

<dialog id="profile-crop-dialog" style="padding: 0; width: min(360px, calc(100vw - 2rem)); border-radius: 0.75rem; border: 1px solid var(--sq-border); background: var(--sq-card-strong); box-shadow: 0 20px 60px rgba(0,0,0,0.45); position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); margin: 0; color: var(--sq-text);">

    <!-- Header -->
    <div style="padding: 0.6rem 1rem; border-bottom: 1px solid var(--sq-border); background: var(--sq-card-strong);">
        <h2 style="font-size: 0.85rem; font-weight: 600; margin: 0;">Crop profile picture</h2>
    </div>

    <!-- Crop area — dark background, fixed height (mirrors the h-80 div in React) -->
    <div style="position: relative; height: 220px; width: 100%; background: rgba(0,0,0,0.9); overflow: hidden;">
        <img id="profile-crop-image" style="display: block; max-width: 100%;" src="" alt="">
    </div>

    <!-- Controls: zoom slider + rotate button -->
    <div class="sq-cropper-controls">
        <div class="sq-cropper-zoom-group">
            <!-- ZoomOut icon -->
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--sq-muted-text,#6b7280)">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="M8 11h6"/>
            </svg>
            <input id="profile-crop-zoom" type="range" min="1" max="3" step="0.05" value="1">
            <!-- ZoomIn icon -->
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--sq-muted-text,#6b7280)">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="M11 8v6"/><path d="M8 11h6"/>
            </svg>
        </div>
        <!-- RotateCw icon (matches the React version's RotateCw button) -->
        <button type="button" class="sq-icon-btn" id="profile-crop-rotate" title="Rotate 90°">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>
            </svg>
        </button>
    </div>

    <!-- Footer -->
    <div style="padding: 0.5rem 1rem; border-top: 1px solid var(--sq-border); display: flex; justify-content: flex-end; gap: 0.5rem; background: var(--sq-card-strong);">
        <button type="button" class="sq-btn sq-btn-secondary" id="profile-crop-cancel" style="font-size:.8rem;padding:.35rem .85rem;">Cancel</button>
        <button type="button" id="profile-crop-save" style="font-size:.8rem;padding:.35rem .85rem;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;border:1px solid var(--sq-primary);background:var(--sq-primary);color:var(--sq-card-strong);font-weight:600;cursor:pointer;transition:opacity .15s;">Save</button>
    </div>

</dialog>

<script>
(function () {
    'use strict';

    var fileInput  = document.getElementById('profile_picture');
    var dialog     = document.getElementById('profile-crop-dialog');
    var image      = document.getElementById('profile-crop-image');
    var btnCancel  = document.getElementById('profile-crop-cancel');
    var btnSave    = document.getElementById('profile-crop-save');
    var btnRotate  = document.getElementById('profile-crop-rotate');
    var zoomSlider = document.getElementById('profile-crop-zoom');

    var csrfToken  = (document.querySelector('meta[name="csrf-token"]')  || {}).getAttribute('content') || '';
    var csrfName   = (document.querySelector('meta[name="csrf-token-name"]') || {}).getAttribute('content') || 'csrf_token';

    var uploadUrl  = '<?= esc(site_url('profile-picture')) ?>';
    var cropper    = null;

    if (!fileInput || !dialog) return; // guard: component not on this page

    // ── Open modal when user picks a file ──────────────────────────────────
    fileInput.addEventListener('change', function (e) {
        var file = e.target.files && e.target.files[0];
        if (!file) return;

        var reader = new FileReader();
        reader.onload = function (ev) {
            image.src = ev.target.result;
            dialog.showModal();

            if (cropper) { cropper.destroy(); }
            zoomSlider.value = 1;

            cropper = new Cropper(image, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',        // image moves, box stays fixed
                autoCropArea: 0.85,
                restore: false,
                guides: false,
                center: false,
                highlight: false,
                cropBoxMovable: false,
                cropBoxResizable: false,
                toggleDragModeOnDblclick: false,
            });
        };
        reader.readAsDataURL(file);
    });

    // ── Zoom slider → Cropper ──────────────────────────────────────────────
    zoomSlider.addEventListener('input', function () {
        if (cropper) { cropper.zoomTo(parseFloat(this.value)); }
    });

    // ── Cropper wheel/pinch → slider feedback ─────────────────────────────
    image.addEventListener('zoom', function (e) {
        var next = parseFloat(zoomSlider.value) + (e.detail.ratio - 1);
        zoomSlider.value = Math.min(3, Math.max(1, next));
    });

    // ── Rotate 90° clockwise ──────────────────────────────────────────────
    btnRotate.addEventListener('click', function () {
        if (cropper) { cropper.rotate(90); }
    });

    // ── Close / cancel ────────────────────────────────────────────────────
    function closeModal() {
        dialog.close();
        if (cropper) { cropper.destroy(); cropper = null; }
        fileInput.value = '';
    }
    btnCancel.addEventListener('click', closeModal);

    // ── Save: crop → upload via fetch ─────────────────────────────────────
    btnSave.addEventListener('click', function () {
        if (!cropper) return;

        btnSave.disabled = true;
        btnSave.textContent = 'Saving...';

        cropper.getCroppedCanvas({
            width: 400,
            height: 400,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        }).toBlob(function (blob) {
            var formData = new FormData();
            formData.append('profile_picture', blob, 'profile.jpg');
            formData.append(csrfName, csrfToken);

            fetch(uploadUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            })
            .then(function (response) {
                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Upload failed. Please try again.');
                    btnSave.disabled = false;
                    btnSave.textContent = 'Save';
                }
            })
            .catch(function (err) {
                console.error(err);
                alert('An error occurred.');
                btnSave.disabled = false;
                btnSave.textContent = 'Save';
            });
        }, 'image/jpeg', 0.92);
    });
}());
</script>

<!-- ── Cover photo: pick → instant upload ──────────────────────────── -->
<script>
(function () {
    'use strict';
    var coverInput = document.getElementById('sq-pme-cover-input');
    var coverForm  = document.getElementById('sq-pme-cover-form');
    var coverField = document.getElementById('sq-pme-cover-file-field');

    if (!coverInput || !coverForm || !coverField) return;

    coverInput.addEventListener('change', function () {
        var file = coverInput.files && coverInput.files[0];
        if (!file) return;

        // Preview immediately
        var preview = document.getElementById('sq-pme-cover-preview');
        if (preview) {
            var url = URL.createObjectURL(file);
            if (preview.tagName === 'IMG') {
                preview.src = url;
            } else {
                // Replace div with img
                var img = document.createElement('img');
                img.src = url;
                img.alt = '';
                img.className = 'sq-pme-cover-img';
                img.id = 'sq-pme-cover-preview';
                preview.replaceWith(img);
            }
        }

        // Transfer file to hidden form field and submit
        var dt = new DataTransfer();
        dt.items.add(file);
        coverField.files = dt.files;
        coverForm.submit();
    });
}());
</script>
