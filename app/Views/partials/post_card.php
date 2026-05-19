<?php
$postId            = (int) ($post['id'] ?? 0);
$author            = $post['author'] ?? null;
$comments          = is_array($post['comments'] ?? null) ? $post['comments'] : [];
$commentsOpen      = ! empty($commentsOpen);
$standalone        = ! empty($standalone);
$commentsTargetId  = 'comments-panel-post-' . $postId;
$currentReaction   = $post['current_user_reaction'] ?? null;
$reactionIcons     = reaction_icons();
$reactionBreakdown = is_array($post['reactions_breakdown'] ?? null) ? $post['reactions_breakdown'] : [];
$reactionTypes     = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];
$viewPostUrl       = site_url('posts/' . $postId);
$authorUrl         = $author ? site_url('u/' . rawurlencode((string) $author['username'])) : '#';
$reactionLabel     = ucfirst((string) ($currentReaction ?: 'Like'));
$reactionsCount    = (int) ($post['reactions_count'] ?? 0);
$commentsCount     = (int) ($post['comments_count'] ?? 0);
$sharesCount       = (int) ($post['shares_count'] ?? 0);
$timestamp         = $post['created_at_human'] ?? '';
$content           = trim((string) ($post['content'] ?? ''));
$mediaUrl          = $post['media_url'] ?? ($post['photo_url'] ?? null);
$mediaUrls         = array_values(array_filter(
    is_array($post['media_urls'] ?? null) ? $post['media_urls'] : ($post['photo_urls'] ?? []),
    static fn ($url): bool => is_string($url) && trim($url) !== ''
));
$mediaType         = $post['media_type'] ?? ($mediaUrl ? 'image' : null);
$mediaTypes        = is_array($post['media_types'] ?? null) ? $post['media_types'] : [];
$galleryItemsRaw   = is_array($post['gallery_items'] ?? null) ? $post['gallery_items'] : [];
$mediaCount        = count($mediaUrls);
$visibleMediaUrls  = $mediaCount === 3 ? array_slice($mediaUrls, 0, 2) : ($mediaCount > 4 ? array_slice($mediaUrls, 0, 4) : $mediaUrls);
$hiddenMediaCount  = max(0, $mediaCount - count($visibleMediaUrls));
$moreOverlayIndex  = $mediaCount === 3 ? 1 : 3; // which index gets the +N badge
// Build gallery items [{url, type}] – fall back to deriving from mediaTypes if not pre-built
if ($galleryItemsRaw === [] && $mediaUrls !== []) {
    foreach ($mediaUrls as $i => $u) {
        $galleryItemsRaw[] = ['url' => $u, 'type' => $mediaTypes[$i] ?? $mediaType ?? 'image'];
    }
}
$galleryJson       = json_encode($galleryItemsRaw, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$isMediaGrid       = $mediaCount > 1;
$isOwner           = (bool) ($post['is_owner'] ?? (($authUser['id'] ?? null) === ($author['id'] ?? null)));
$isSaved           = ! empty($post['is_saved']);
$canEdit           = ! empty($post['can_edit']);
$canDelete         = ! empty($post['can_delete']);
$canSave           = ! empty($post['can_save']);
$canHide           = ! empty($post['can_hide']);
$menuTitle         = $isOwner ? 'Manage post' : 'Customize your feed';
$menuSubtitle      = $isOwner ? 'Your post' : ('@' . ($author['username'] ?? 'user'));
$sharesCount       = (int) ($post['shares_count'] ?? 0);
$sharedPost        = $post['shared_post'] ?? null;
$isShare           = $sharedPost !== null;
$shareActionUrl    = site_url('posts/' . $postId . '/share');
$shareAuthorName   = esc($author['full_name'] ?? $author['username'] ?? 'Unknown');
$sharePreviewText  = mb_strimwidth(trim((string) ($post['content'] ?? '')), 0, 120, '…');
$shareAuthorAvatar = $author['profile_picture_url'] ?? null;
$shareFirstMedia   = $mediaUrls[0] ?? null;
?>

<article class="sq-post-card-v2" data-post-id="<?= esc((string) $postId) ?>" data-post-saved="<?= $isSaved ? 'true' : 'false' ?>" data-has-media="<?= $mediaUrl ? 'true' : 'false' ?>">
    <div class="sq-post-v2-header">
        <div class="sq-post-v2-header-main">
            <a href="<?= esc($authorUrl) ?>" class="sq-post-v2-avatar-link">
                <?php if (! empty($author['profile_picture_url'])): ?>
                    <img
                        src="<?= esc($author['profile_picture_url']) ?>"
                        alt="<?= esc($author['full_name'] ?? $author['username'] ?? 'User') ?>"
                        class="sq-post-v2-avatar"
                    >
                <?php else: ?>
                    <span class="sq-post-v2-avatar sq-post-v2-avatar-fb">
                        <?= esc(user_initials($author)) ?>
                    </span>
                <?php endif; ?>
            </a>

            <div class="sq-post-v2-headline">
                <a href="<?= esc($authorUrl) ?>" class="sq-post-v2-author">
                    <?= esc($author['full_name'] ?? $author['username'] ?? 'Unknown user') ?>
                </a>

                <div class="sq-post-v2-subline">
                    <?php if ($timestamp !== ''): ?>
                        <span><?= esc($timestamp) ?></span>
                        <span class="sq-post-v2-dot">·</span>
                    <?php endif; ?>
                    <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14" class="sq-post-v2-audience">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM11 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v-1.07zM17.9 17.39c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="sq-post-v2-menu-root" data-post-menu-root>
            <button type="button" class="sq-post-v2-more" aria-label="More options" aria-haspopup="menu" aria-expanded="false" data-post-menu-toggle>
                <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                    <circle cx="12" cy="5" r="2"/>
                    <circle cx="12" cy="12" r="2"/>
                    <circle cx="12" cy="19" r="2"/>
                </svg>
            </button>

            <div class="sq-post-v2-menu-panel" data-post-menu-panel hidden>
                <div class="sq-post-v2-menu-header">
                    <strong><?= esc($menuTitle) ?></strong>
                    <span><?= esc($menuSubtitle) ?></span>
                </div>

                <div class="sq-post-v2-menu-group">
                    <?php if ($canEdit): ?>
                        <button type="button" class="sq-post-v2-menu-item" data-post-edit-start>
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            <span>Edit post</span>
                        </button>
                    <?php endif; ?>

                    <?php if ($canSave): ?>
                        <form method="post" action="<?= esc(site_url('posts/' . $postId . '/save')) ?>" class="sq-post-v2-menu-form" data-post-save-form>
                            <?= csrf_field() ?>
                            <input type="hidden" name="_method" value="<?= $isSaved ? 'DELETE' : '' ?>" data-post-save-method>
                            <button type="submit" class="sq-post-v2-menu-item<?= $isSaved ? ' is-active' : '' ?>" data-post-save-button>
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h12a1 1 0 0 1 1 1v17l-7-4-7 4V4a1 1 0 0 1 1-1Z"/></svg>
                                <span data-post-save-label><?= esc($isSaved ? 'Unsave post' : 'Save post') ?></span>
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($canHide): ?>
                        <form method="post" action="<?= esc(site_url('posts/' . $postId . '/hide')) ?>" class="sq-post-v2-menu-form" data-post-hide-form>
                            <?= csrf_field() ?>
                            <button type="submit" class="sq-post-v2-menu-item">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 3 18 18"/><path d="M10.58 10.58a2 2 0 0 0 2.83 2.83"/><path d="M9.88 5.09A10.94 10.94 0 0 1 12 5c5 0 9.27 3.11 11 7-1.01 2.27-2.77 4.2-5 5.32"/><path d="M6.61 6.61C4.62 7.95 3.16 9.82 2 12c.69 1.55 1.72 2.96 3.02 4.11"/></svg>
                                <span>Hide post</span>
                            </button>
                        </form>
                    <?php endif; ?>

                    <button type="button" class="sq-post-v2-menu-item" data-post-hidden-comments-view data-url="<?= esc(site_url('posts/' . $postId . '/hidden-comments')) ?>" data-target="<?= esc($commentsTargetId) ?>">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span>View hidden comments</span>
                    </button>

                    <a href="<?= esc($viewPostUrl) ?>" class="sq-post-v2-menu-item">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M21 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/></svg>
                        <span>View post</span>
                    </a>
                </div>

                <?php if ($canDelete): ?>
                    <div class="sq-post-v2-menu-separator" aria-hidden="true"></div>
                    <div class="sq-post-v2-menu-group">
                        <form method="post" action="<?= esc(site_url('posts/' . $postId)) ?>" class="sq-post-v2-menu-form" data-post-delete-form>
                            <?= csrf_field() ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="sq-post-v2-menu-item is-destructive">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                <span>Delete post</span>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($canEdit): ?>
        <div class="sq-post-v2-edit-shell" data-post-edit-shell hidden>
            <form method="post" action="<?= esc(site_url('posts/' . $postId)) ?>" class="sq-post-v2-edit-form" enctype="multipart/form-data" data-post-edit-form>
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PATCH">
                <label class="sq-post-v2-edit-label" for="post-edit-<?= esc((string) $postId) ?>">Edit post</label>
                <textarea id="post-edit-<?= esc((string) $postId) ?>" name="content" class="sq-post-v2-edit-field" maxlength="1000" data-post-edit-field><?= esc($content) ?></textarea>
                
                <div class="sq-post-v2-edit-media-manager" data-post-edit-media-manager<?= $mediaUrls === [] ? ' hidden' : '' ?>>
                    <div class="sq-post-v2-edit-media-preview" data-post-edit-media-preview>
                        <?php foreach ($mediaUrls as $index => $url): ?>
                            <?php 
                            $rawPath = is_array($post['photo_paths']) ? ($post['photo_paths'][$index] ?? '') : '';
                            $isImg   = ($mediaTypes[$index] ?? $mediaType ?? 'image') === 'image';
                            ?>
                            <div class="sq-post-v2-edit-media-item" data-existing-media="<?= esc($rawPath) ?>">
                                <?php if ($isImg): ?>
                                    <img src="<?= esc($url) ?>" alt="">
                                <?php else: ?>
                                    <video src="<?= esc($url) ?>" muted></video>
                                <?php endif; ?>
                                <input type="hidden" name="keep_media[]" value="<?= esc($rawPath) ?>">
                                <button type="button" class="sq-post-v2-edit-media-remove" aria-label="Remove media">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="sq-post-v2-edit-actions">
                    <!-- Add Photo/Video — left side -->
                    <button type="button" class="sq-post-v2-edit-media-add" data-post-edit-media-add-btn>
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        <span>Add Photo/Video</span>
                    </button>
                    <input type="file" name="media[]" multiple accept="image/*,video/*" data-post-edit-file-input style="display:none;">

                    <!-- Spacer -->
                    <div style="flex:1;"></div>

                    <!-- Cancel + Save — right side -->
                    <button type="button" class="sq-post-v2-edit-btn is-secondary" data-post-edit-cancel>Cancel</button>
                    <button type="submit" class="sq-post-v2-edit-btn is-primary">Save</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($content !== ''): ?>
        <div class="sq-post-v2-content" data-post-content><?= esc($content) ?></div>
    <?php endif; ?>

    <?php if ($isShare && $sharedPost): ?>
        <?php
            $origAuthor     = $sharedPost['author'] ?? null;
            $origContent    = trim((string) ($sharedPost['content'] ?? ''));
            $origMediaUrls  = $sharedPost['media_urls'] ?? [];
            $origMediaTypes = is_array($sharedPost['media_types'] ?? null) ? $sharedPost['media_types'] : [];
            $origMediaType  = $sharedPost['media_type'] ?? null;
            $origAuthorUrl  = $origAuthor ? site_url('u/' . rawurlencode((string) $origAuthor['username'])) : '#';
            $hasOrigMedia   = $origMediaUrls !== [];

            // Build gallery items for original post media
            $origGalleryItems = [];
            foreach ($origMediaUrls as $i => $origUrl) {
                $origGalleryItems[] = [
                    'url'  => $origUrl,
                    'type' => $origMediaTypes[$i] ?? $origMediaType ?? 'image',
                ];
            }
            $origGalleryJson = esc(json_encode($origGalleryItems), 'attr');

            $firstOrigUrl  = $origMediaUrls[0] ?? null;
            $firstOrigType = $origMediaTypes[0] ?? $origMediaType ?? 'image';
            $origMoreCount = max(0, count($origMediaUrls) - 1);
        ?>
        <div
            class="sq-post-v2-shared-embed<?= $hasOrigMedia ? ' has-media' : '' ?>"
            <?php if ($hasOrigMedia): ?>
                data-post-gallery="<?= $origGalleryJson ?>"
            <?php endif; ?>
        >
            <?php if ($hasOrigMedia): ?>
                <?php
                    $origMediaCount   = count($origMediaUrls);
                    $origIsGrid       = $origMediaCount > 1;
                    $origVisible      = array_slice($origMediaUrls, 0, 4, true);
                    $origHiddenCount  = max(0, $origMediaCount - 4);
                    $origSingleVideo  = $origMediaCount === 1 && $firstOrigType === 'video';
                ?>
                <!-- Shared embed media: grid if multiple, natural if single -->
                <div
                    class="sq-post-v2-media sq-post-v2-shared-media<?= $origIsGrid ? ' sq-post-v2-media-grid' : '' ?>"
                    data-post-gallery="<?= $origGalleryJson ?>"
                    data-post-gallery-count="<?= esc((string) $origMediaCount) ?>"
                >
                    <?php if ($origSingleVideo): ?>
                        <video class="sq-post-v2-video" controls preload="metadata">
                            <source src="<?= esc($firstOrigUrl) ?>">
                        </video>
                    <?php else: ?>
                        <?php foreach ($origVisible as $oi => $oUrl): ?>
                            <?php $oType = $origMediaTypes[$oi] ?? $origMediaType ?? 'image'; ?>
                            <button
                                type="button"
                                class="sq-post-v2-media-item<?= $origIsGrid ? ' is-grid' : '' ?><?= $oType === 'video' ? ' is-video' : '' ?>"
                                data-post-gallery-open
                                data-gallery-index="<?= esc((string) $oi) ?>"
                                aria-label="Open <?= esc($oType) ?> <?= esc((string) ($oi + 1)) ?> of <?= esc((string) $origMediaCount) ?>"
                            >
                                <?php if ($oType === 'video'): ?>
                                    <video src="<?= esc($oUrl) ?>" class="sq-post-v2-photo<?= $origIsGrid ? ' is-grid' : '' ?>" muted preload="metadata"></video>
                                    <span class="sq-post-v2-video-play-overlay" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                    </span>
                                <?php else: ?>
                                    <img
                                        src="<?= esc($oUrl) ?>"
                                        alt="<?= esc($origContent !== '' ? mb_strimwidth($origContent, 0, 120, '…') : 'Shared image ' . ($oi + 1)) ?>"
                                        class="sq-post-v2-photo<?= $origIsGrid ? ' is-grid' : '' ?>"
                                        loading="<?= $oi === 0 ? 'eager' : 'lazy' ?>"
                                    >
                                <?php endif; ?>
                                <?php if ($origHiddenCount > 0 && $oi === 3): ?>
                                    <span class="sq-post-v2-media-more" aria-hidden="true">+<?= esc((string) $origHiddenCount) ?> more</span>
                                <?php endif; ?>
                            </button>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Author row below media -->
            <div class="sq-post-v2-shared-body">
                <div class="sq-post-v2-shared-header">
                    <?php if (! empty($origAuthor['profile_picture_url'])): ?>
                        <img src="<?= esc($origAuthor['profile_picture_url']) ?>" alt="" class="sq-post-v2-shared-avatar">
                    <?php else: ?>
                        <span class="sq-post-v2-shared-avatar sq-post-v2-avatar-fb"><?= esc(user_initials($origAuthor)) ?></span>
                    <?php endif; ?>
                    <div class="sq-post-v2-shared-author-col">
                        <a href="<?= esc($origAuthorUrl) ?>" class="sq-post-v2-shared-author"><?= esc($origAuthor['full_name'] ?? $origAuthor['username'] ?? 'Unknown') ?></a>
                        <?php if (! empty($sharedPost['created_at_human'])): ?>
                            <span class="sq-post-v2-shared-time"><?= esc($sharedPost['created_at_human']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($origContent !== ''): ?>
                    <p class="sq-post-v2-shared-content"><?= esc($origContent) ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($mediaUrls !== []): ?>
        <?php
            // Single video with no other files: show inline player
            $singleVideo = $mediaCount === 1 && ($mediaTypes[0] ?? $mediaType) === 'video';
        ?>
        <div
            class="sq-post-v2-media<?= $isMediaGrid ? ' sq-post-v2-media-grid' : '' ?>"
            data-post-gallery="<?= esc($galleryJson, 'attr') ?>"
            data-post-gallery-count="<?= esc((string) $mediaCount) ?>"
        >
            <?php if ($singleVideo): ?>
                <video class="sq-post-v2-video" controls preload="metadata">
                    <source src="<?= esc($mediaUrls[0]) ?>">
                    Your browser does not support the video tag.
                </video>
            <?php else: ?>
                <?php foreach ($visibleMediaUrls as $index => $url): ?>
                    <?php $itemType = $mediaTypes[$index] ?? $mediaType ?? 'image'; ?>
                    <button
                        type="button"
                        class="sq-post-v2-media-item<?= $isMediaGrid ? ' is-grid' : '' ?><?= $itemType === 'video' ? ' is-video' : '' ?>"
                        data-post-gallery-open
                        data-gallery-index="<?= esc((string) $index) ?>"
                        aria-label="Open <?= esc($itemType) ?> <?= esc((string) ($index + 1)) ?> of <?= esc((string) $mediaCount) ?>"
                    >
                        <?php if ($itemType === 'video'): ?>
                            <video
                                src="<?= esc($url) ?>"
                                class="sq-post-v2-photo<?= $isMediaGrid ? ' is-grid' : '' ?>"
                                muted
                                preload="metadata"
                            ></video>
                            <span class="sq-post-v2-video-play-overlay" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                            </span>
                        <?php else: ?>
                            <img
                                src="<?= esc($url) ?>"
                                alt="<?= esc($content !== '' ? mb_strimwidth($content, 0, 120, '…') : 'Post image ' . ($index + 1)) ?>"
                                class="sq-post-v2-photo<?= $isMediaGrid ? ' is-grid' : '' ?>"
                                loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"
                            >
                        <?php endif; ?>
                        <?php if ($hiddenMediaCount > 0 && $index === $moreOverlayIndex): ?>
                            <span class="sq-post-v2-media-more" aria-hidden="true">+<?= esc((string) $hiddenMediaCount) ?> more</span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($reactionsCount > 0 || $commentsCount > 0 || $sharesCount > 0): ?>
    <div class="sq-post-v2-summary">
        <div class="sq-post-v2-summary-left">
            <?php if ($reactionsCount > 0): ?>
                <button
                    type="button"
                    class="sq-post-v2-summary-reactions"
                    data-reactors-open
                    data-url="<?= esc(site_url('api/posts/' . $postId . '/reactions')) ?>"
                    title="View reactions"
                    aria-label="View reactions"
                >
                    <span class="sq-post-v2-summary-icons">
                        <?php foreach ($reactionTypes as $type): ?>
                            <?php if (! empty($reactionBreakdown[$type])): ?>
                                <img src="<?= esc($reactionIcons[$type] ?? '') ?>" alt="" class="sq-reaction-badge">
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </span>
                    <span class="sq-post-v2-summary-count"><?= esc((string) $reactionsCount) ?></span>
                </button>
            <?php endif; ?>
        </div>
        <div class="sq-post-v2-summary-right">
            <?php if ($commentsCount > 0): ?>
                <button type="button" class="sq-post-v2-summary-link"
                    data-comments-toggle
                    data-target="<?= esc($commentsTargetId) ?>"><?= esc((string) $commentsCount) ?> comment<?= $commentsCount !== 1 ? 's' : '' ?></button>
            <?php endif; ?>
            <?php if ($commentsCount > 0 && $sharesCount > 0): ?>
                <span class="sq-post-v2-dot">·</span>
            <?php endif; ?>
    <?php if ($sharesCount > 0): ?>
            <span class="sq-post-v2-summary-link"><?= esc((string) $sharesCount) ?> share<?= $sharesCount !== 1 ? 's' : '' ?></span>
        <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="sq-post-v2-divider"></div>

    <div class="sq-post-v2-actions">
        <div class="sq-post-v2-action-item sq-reaction-control" data-reaction-control>
            <form method="post" action="<?= esc(site_url('posts/' . $postId . '/reactions')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="<?= esc((string) ($currentReaction ?: 'like')) ?>">
                <button type="submit" class="sq-post-v2-action-btn"
                    title="<?= esc($reactionLabel) ?>"
                    aria-label="<?= esc($reactionLabel) ?>"
                >
                    <?php if ($currentReaction && ! empty($reactionIcons[$currentReaction])): ?>
                        <img src="<?= esc($reactionIcons[$currentReaction]) ?>" alt="" class="sq-post-v2-action-icon-reaction">
                        <span class="sq-post-v2-action-label" data-reaction="<?= esc($currentReaction) ?>"><?= esc($reactionLabel) ?></span>
                    <?php else: ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="sq-post-v2-action-icon">
                            <path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3m7-2V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14Z"/>
                        </svg>
                        <span class="sq-post-v2-action-label">Like</span>
                    <?php endif; ?>
                </button>
            </form>

            <div class="sq-reaction-picker" data-reaction-picker hidden>
                <?php foreach ($reactionTypes as $type): ?>
                    <form method="post" action="<?= esc(site_url('posts/' . $postId . '/reactions')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="type" value="<?= esc($type) ?>">
                        <button
                            type="submit"
                            class="sq-reaction-option"
                            title="<?= esc(ucfirst($type)) ?>"
                            aria-label="<?= esc(ucfirst($type)) ?>"
                        >
                            <img src="<?= esc($reactionIcons[$type] ?? '') ?>" alt="" class="sq-reaction-option-icon">
                        </button>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>

        <button
            type="button"
            class="sq-post-v2-action-btn"
            data-comments-toggle
            data-target="<?= esc($commentsTargetId) ?>"
            title="Comment"
            aria-label="Comment"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="sq-post-v2-action-icon">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <span class="sq-post-v2-action-label">Comment</span>
        </button>

        <button
            type="button"
            class="sq-post-v2-action-btn"
            data-post-share
            data-post-id="<?= esc((string) $postId) ?>"
            data-share-url="<?= esc($shareActionUrl) ?>"
            data-share-author="<?= $shareAuthorName ?>"
            data-share-preview="<?= esc($sharePreviewText) ?>"
            data-share-avatar="<?= esc($shareAuthorAvatar ?? '') ?>"
            data-share-media="<?= esc($shareFirstMedia ?? '') ?>"
            data-shares-count="<?= esc((string) $sharesCount) ?>"
            title="Share"
            aria-label="Share"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="sq-post-v2-action-icon sq-post-v2-share-icon">
                <polyline points="17 1 21 5 17 9"/>
                <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                <polyline points="7 23 3 19 7 15"/>
                <path d="M21 13v2a4 4 0 0 1-4 4H3"/>
            </svg>
            <span class="sq-post-v2-action-label">Share</span>
        </button>
    </div>

    <div id="<?= esc($commentsTargetId) ?>" class="sq-post-v2-comments<?= $commentsOpen ? '' : ' is-hidden' ?>">
        <?php if ($comments === []): ?>
            <p class="sq-post-v2-comments-empty" data-comments-empty-state>No comments yet. Start the conversation.</p>
        <?php else: ?>
            <div class="sq-post-v2-comment-list" data-comment-list>
                <?php foreach ($comments as $comment): ?>
                    <?= view('partials/comment_row', [
                        'comment'  => $comment,
                        'authUser' => $authUser,
                        'depth'    => 0,
                    ]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="sq-post-v2-hidden-comments" data-hidden-comments-root hidden></div>

        <div class="sq-post-v2-comment-input">
            <?php if (! empty($authUser['profile_picture_url'])): ?>
                <img
                    src="<?= esc($authUser['profile_picture_url']) ?>"
                    alt="<?= esc($authUser['full_name'] ?? $authUser['username'] ?? 'You') ?>"
                    class="sq-post-v2-comment-input-avatar"
                >
            <?php else: ?>
                <span class="sq-post-v2-comment-input-avatar sq-post-v2-comment-input-avatar-fb">
                    <?= esc(user_initials($authUser ?? null)) ?>
                </span>
            <?php endif; ?>

            <form method="post" action="<?= esc(site_url('posts/' . $postId . '/comments')) ?>" class="sq-post-v2-comment-form" data-comment-form>
                <?= csrf_field() ?>
                <div class="sq-post-v2-comment-pill">
                    <input
                        type="text"
                        name="content"
                        placeholder="Write a comment..."
                        maxlength="1000"
                        class="sq-post-v2-comment-field"
                    >
                    <button type="submit" class="sq-post-v2-comment-submit">Post</button>
                </div>
            </form>
        </div>
    </div>
</article>
