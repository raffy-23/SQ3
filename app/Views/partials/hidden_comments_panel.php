<?php
$comments = is_array($comments ?? null) ? $comments : [];
$authUser = $authUser ?? null;
$count = count($comments);
?>
<div class="sq-post-v2-hidden-comments-section" data-hidden-comments-section>
    <div class="sq-post-v2-hidden-comments-header">
        <span class="sq-post-v2-hidden-comments-title">Hidden comments</span>
        <span class="sq-post-v2-hidden-comments-count"><?= esc((string) $count) ?></span>
    </div>

    <?php if ($comments === []): ?>
        <p class="sq-post-v2-hidden-comments-empty">No hidden comments for this post.</p>
    <?php else: ?>
        <div class="sq-post-v2-comment-list sq-post-v2-hidden-comment-list">
            <?php foreach ($comments as $comment): ?>
                <?= view('partials/comment_row', [
                    'comment'  => $comment,
                    'authUser' => $authUser,
                    'depth'    => 0,
                ]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
