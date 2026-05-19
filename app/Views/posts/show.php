<div class="sq-single-column">

    <!-- ── Back button ─────────────────────────────────────────────── -->
    <button
        type="button"
        class="sq-back-btn"
        onclick="if (document.referrer && document.referrer !== window.location.href) { history.back(); } else { window.location.href = '<?= esc(site_url('feed')) ?>'; }"
        aria-label="Go back"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.2"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M19 12H5"/>
            <path d="M12 19l-7-7 7-7"/>
        </svg>
        <span>Back</span>
    </button>

    <?= view('partials/post_card', ['post' => $post, 'authUser' => $authUser ?? null, 'standalone' => true, 'commentsOpen' => true]) ?>
</div>
