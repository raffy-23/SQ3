<?php $emptyMessage = $emptyMessage ?? 'No posts yet.'; ?>
<?php if (empty($posts)): ?>
    <div class="rounded-xl border border-border bg-card py-12 text-center text-card-foreground" data-feed-empty-state="true">
        <div class="p-6">
            <p class="text-muted-foreground"><?= esc($emptyMessage) ?></p>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <?= view('partials/post_card', ['post' => $post, 'authUser' => $authUser ?? null]) ?>
    <?php endforeach; ?>
<?php endif; ?>
