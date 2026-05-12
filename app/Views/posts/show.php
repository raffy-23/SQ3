<div class="sq-single-column">
    <a href="javascript:history.back()" class="sq-inline-button sq-back-link">← Back</a>
    <?= view('partials/post_card', ['post' => $post, 'authUser' => $authUser ?? null, 'standalone' => true, 'commentsOpen' => true]) ?>
</div>
