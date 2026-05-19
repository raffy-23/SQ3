<?php foreach ($posts as $post): ?>
    <?= view('partials/post_card', ['post' => $post, 'authUser' => $authUser ?? null]) ?>
<?php endforeach; ?>
