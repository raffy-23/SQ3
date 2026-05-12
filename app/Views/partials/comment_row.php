<?php
$comment = $comment ?? [];
$depth = (int) ($depth ?? 0);
$isReply = $depth > 0;
$commentId = (int) ($comment['id'] ?? 0);
$postId = (int) ($comment['post_id'] ?? 0);
$parentId = isset($comment['parent_id']) ? (int) $comment['parent_id'] : 0;
$commentAuthor = $comment['author'] ?? null;
$commentAuthorUrl = $commentAuthor ? site_url('u/' . rawurlencode((string) $commentAuthor['username'])) : '#';
$commentAuthorHandle = '@' . ($commentAuthor['username'] ?? 'user');
$content = (string) ($comment['content'] ?? '');
$replies = is_array($comment['replies'] ?? null) ? $comment['replies'] : [];
$currentReaction = (string) ($comment['current_user_reaction'] ?? '');
$reactionDisplayType = (string) ($comment['reaction_display_type'] ?? $currentReaction);
$reactionBreakdown = is_array($comment['reactions_breakdown'] ?? null) ? $comment['reactions_breakdown'] : [];
$isLiked = $currentReaction !== '';

$reactionsCount = (int) ($comment['reactions_count'] ?? 0);
$reactionIcons = reaction_icons();

$reactionTypes = \App\Models\ReactionModel::TYPES;
$reactionLabel = ucfirst($currentReaction !== '' ? $currentReaction : 'Like');
$canReply = ! empty($comment['can_reply']);
$canEdit = ! empty($comment['can_edit']);
$canDelete = ! empty($comment['can_delete']);
$canHide = ! empty($comment['can_hide']);
$hasMenu = $canEdit || $canDelete || $canHide;
$replyPlaceholder = 'Reply to ' . ($commentAuthor['full_name'] ?? $commentAuthor['username'] ?? 'this comment') . '...';
?>
<div class="sq-post-v2-comment-row<?= $isReply ? ' is-reply' : '' ?>" data-comment-id="<?= esc((string) $commentId) ?>" data-parent-comment-id="<?= esc((string) $parentId) ?>" data-has-replies="<?= $replies !== [] ? 'true' : 'false' ?>">
    <a href="<?= esc($commentAuthorUrl) ?>" class="sq-post-v2-comment-avatar-link">
        <?php if (! empty($commentAuthor['profile_picture_url'])): ?>
            <img
                src="<?= esc($commentAuthor['profile_picture_url']) ?>"
                alt="<?= esc($commentAuthor['full_name'] ?? $commentAuthor['username'] ?? 'User') ?>"
                class="sq-post-v2-comment-avatar"
            >
        <?php else: ?>
            <span class="sq-post-v2-comment-avatar sq-post-v2-comment-avatar-fb">
                <?= esc(user_initials($commentAuthor)) ?>
            </span>
        <?php endif; ?>
    </a>

    <div class="sq-post-v2-comment-body">
        <div class="sq-post-v2-comment-stack">
            <div class="sq-post-v2-comment-topline">
                <div class="sq-post-v2-comment-bubble" data-comment-bubble>
                    <a href="<?= esc($commentAuthorUrl) ?>" class="sq-post-v2-comment-author">
                        <?= esc($commentAuthor['full_name'] ?? $commentAuthor['username'] ?? 'Unknown user') ?>
                    </a>
                    <p data-comment-content><?= esc($content) ?></p>
                </div>

                <?php if ($hasMenu): ?>
                    <div class="sq-post-v2-comment-menu-root" data-comment-menu-root>
                        <button type="button" class="sq-post-v2-comment-more" aria-label="Comment actions" aria-haspopup="menu" aria-expanded="false" data-comment-menu-toggle>
                            <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18" aria-hidden="true">
                                <circle cx="5" cy="12" r="2"/>
                                <circle cx="12" cy="12" r="2"/>
                                <circle cx="19" cy="12" r="2"/>
                            </svg>
                        </button>

                        <div class="sq-post-v2-comment-menu-panel" data-comment-menu-panel hidden>
                            <div class="sq-post-v2-menu-group">
                                <?php if ($canEdit): ?>
                                    <button type="button" class="sq-post-v2-menu-item" data-comment-edit-start>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        <span>Edit comment</span>
                                    </button>
                                <?php endif; ?>

                                <?php if ($canHide): ?>
                                    <form method="post" action="<?= esc(site_url('comments/' . $commentId . '/hide')) ?>" class="sq-post-v2-menu-form" data-comment-hide-form>
                                        <?= csrf_field() ?>
                                        <button type="submit" class="sq-post-v2-menu-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 3 18 18"/><path d="M10.58 10.58a2 2 0 0 0 2.83 2.83"/><path d="M9.88 5.09A10.94 10.94 0 0 1 12 5c5 0 9.27 3.11 11 7-1.01 2.27-2.77 4.2-5 5.32"/><path d="M6.61 6.61C4.62 7.95 3.16 9.82 2 12c.69 1.55 1.72 2.96 3.02 4.11"/></svg>
                                            <span>Hide comment</span>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($canDelete): ?>
                                    <form method="post" action="<?= esc(site_url('comments/' . $commentId)) ?>" class="sq-post-v2-menu-form" data-comment-delete-form>
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="sq-post-v2-menu-item is-destructive">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                            <span>Delete comment</span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="sq-post-v2-comment-meta">
                <div class="sq-post-v2-comment-meta-left">
                    <?php if (! empty($comment['created_at_human'])): ?>
                        <span class="sq-post-v2-comment-time"><?= esc($comment['created_at_human']) ?></span>
                    <?php endif; ?>

                    <div class="sq-post-v2-comment-reaction-control sq-reaction-control" data-comment-reaction-control>
                        <form method="post" action="<?= esc(site_url('comments/' . $commentId . '/reactions')) ?>" class="sq-post-v2-comment-reaction-form" data-comment-reaction-form>
                            <?= csrf_field() ?>
                            <input type="hidden" name="type" value="<?= esc($currentReaction !== '' ? $currentReaction : 'like') ?>" data-comment-reaction-type>
                            <button type="submit" class="sq-post-v2-comment-action<?= $isLiked ? ' is-active' : '' ?>" data-comment-reaction-button title="<?= esc($reactionLabel) ?>" aria-label="<?= esc($reactionLabel) ?>">
                                <span class="sq-post-v2-action-label" data-comment-reaction-label<?= $currentReaction !== '' ? ' data-reaction="' . esc($currentReaction) . '"' : '' ?>><?= esc($reactionLabel) ?></span>
                            </button>
                        </form>

                        <div class="sq-reaction-picker sq-post-v2-comment-picker" data-reaction-picker hidden>
                            <?php foreach ($reactionTypes as $type): ?>
                                <form method="post" action="<?= esc(site_url('comments/' . $commentId . '/reactions')) ?>" data-comment-reaction-form>
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="type" value="<?= esc($type) ?>">
                                    <button type="submit" class="sq-reaction-option" title="<?= esc(ucfirst($type)) ?>" aria-label="<?= esc(ucfirst($type)) ?>">
                                        <img src="<?= esc($reactionIcons[$type] ?? '') ?>" alt="" class="sq-reaction-option-icon">
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if ($canReply): ?>
                        <button type="button" class="sq-post-v2-comment-action" data-comment-reply-toggle>Reply</button>
                    <?php endif; ?>
                </div>

                <?php if ($reactionsCount > 0): ?>
                    <span class="sq-post-v2-comment-reaction-count" data-comment-reaction-count>
                        <span data-comment-reaction-count-value><?= esc((string) $reactionsCount) ?></span>
                        <span class="sq-post-v2-comment-reaction-icons" data-comment-reaction-count-icons>
                            <?php foreach ($reactionTypes as $type): ?>
                                <?php if (! empty($reactionBreakdown[$type])): ?>
                                    <img src="<?= esc($reactionIcons[$type] ?? '') ?>" alt="" class="sq-reaction-badge sq-post-v2-comment-reaction-count-icon" data-comment-reaction-count-icon>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if ($reactionBreakdown === [] && $reactionDisplayType !== '' && ! empty($reactionIcons[$reactionDisplayType])): ?>
                                <img src="<?= esc($reactionIcons[$reactionDisplayType]) ?>" alt="" class="sq-reaction-badge sq-post-v2-comment-reaction-count-icon" data-comment-reaction-count-icon>
                            <?php endif; ?>
                        </span>
                    </span>

                <?php endif; ?>

            </div>
        </div>

        <?php if ($canEdit): ?>
            <div class="sq-post-v2-comment-edit-shell" data-comment-edit-shell hidden>
                <form method="post" action="<?= esc(site_url('comments/' . $commentId)) ?>" class="sq-post-v2-comment-edit-form" data-comment-edit-form>
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="PATCH">
                    <div class="sq-post-v2-comment-pill is-edit">
                        <input
                            type="text"
                            name="content"
                            value="<?= esc($content) ?>"
                            maxlength="1000"
                            class="sq-post-v2-comment-field"
                            data-comment-edit-field
                        >
                        <button type="submit" class="sq-post-v2-comment-submit">Save</button>
                        <button type="button" class="sq-post-v2-comment-cancel-icon" aria-label="Cancel edit" title="Cancel edit" data-comment-edit-cancel>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M18 6 6 18"/>
                                <path d="m6 6 12 12"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($canReply): ?>
            <div class="sq-post-v2-reply-shell" data-comment-reply-shell hidden>
                <div class="sq-post-v2-reply-context">Replying to <strong><?= esc($commentAuthorHandle) ?></strong></div>
                <form method="post" action="<?= esc(site_url('posts/' . $postId . '/comments')) ?>" class="sq-post-v2-comment-form sq-post-v2-reply-form" data-comment-form>
                    <?= csrf_field() ?>
                    <input type="hidden" name="parent_id" value="<?= esc((string) $commentId) ?>">
                    <div class="sq-post-v2-comment-pill is-reply">
                        <input
                            type="text"
                            name="content"
                            placeholder="<?= esc($replyPlaceholder) ?>"
                            maxlength="1000"
                            class="sq-post-v2-comment-field"
                        >
                        <button type="submit" class="sq-post-v2-comment-submit">Reply</button>
                        <button type="button" class="sq-post-v2-comment-cancel-icon" aria-label="Cancel reply" title="Cancel reply" data-comment-reply-cancel>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M18 6 6 18"/>
                                <path d="m6 6 12 12"/>
                            </svg>
                        </button>
                    </div>

                </form>
            </div>
        <?php endif; ?>

        <div class="sq-post-v2-comment-replies" data-comment-replies<?= $replies === [] ? ' hidden' : '' ?>>
            <?php foreach ($replies as $reply): ?>
                <?= view('partials/comment_row', [
                    'comment'  => $reply,
                    'authUser' => $authUser,
                    'depth'    => $depth + 1,
                ]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
