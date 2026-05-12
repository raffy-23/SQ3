<?php if (empty($posts)): ?>
    <div class="rounded-xl border bg-card text-card-foreground py-12 text-center" data-feed-empty-state="true">

        <div class="p-6">
            <p class="text-muted-foreground">Your feed is empty. Follow people to see their posts!</p>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <?= view('partials/post_card', ['post' => $post, 'authUser' => $authUser ?? null]) ?>
    <?php endforeach; ?>
<?php endif; ?>
