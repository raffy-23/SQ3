    <section class="sq-post-card-v2 sq-profile-media-card">
        <div style="padding: 1.25rem; font-size: .875rem;">
            <div class="sq-card-title" style="font-size: .875rem;">Profile media</div>
            <p class="text-sm text-muted-foreground">Manage the avatar and cover photo people see first on your profile.</p>

            <div class="sq-profile-media-preview">
                <div class="sq-profile-media-cover<?= empty($authUser['cover_photo_url']) ? ' is-fallback' : '' ?>">
                    <?php if (! empty($authUser['cover_photo_url'])): ?>
                        <img
                            src="<?= esc($authUser['cover_photo_url']) ?>"
                            alt="<?= esc($authUser['full_name']) ?> cover photo"
                            class="sq-profile-media-cover-image"
                        >
                    <?php else: ?>
                        <div class="sq-profile-media-cover-fallback" aria-hidden="true"></div>
                    <?php endif; ?>
                </div>

                <div class="sq-profile-media-avatar">
                    <?php if (! empty($authUser['profile_picture_url'])): ?>
                        <img src="<?= esc($authUser['profile_picture_url']) ?>" alt="<?= esc($authUser['full_name']) ?>" class="sq-profile-media-avatar-image">
                    <?php else: ?>
                        <span class="sq-profile-media-avatar-fallback"><?= esc(user_initials($authUser)) ?></span>
                    <?php endif; ?>
                </div>

                <div class="sq-profile-media-preview-copy">
                    <div class="sq-profile-media-name"><?= esc($authUser['full_name']) ?></div>
                    <div class="sq-profile-media-handle">@<?= esc($authUser['username']) ?></div>
                </div>
            </div>

        <div class="sq-profile-media-grid">
            <div class="sq-profile-media-panel">
                <div>
                    <label for="profile_picture" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Profile picture</label>
                    <input id="profile_picture" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                    <p class="text-sm text-muted-foreground sq-profile-media-hint">Select an image to crop and upload.</p>
                </div>
                <?php if (! empty($authUser['profile_picture_url'])): ?>
                    <form method="post" action="<?= esc(site_url('profile-picture')) ?>" class="sq-profile-inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="sq-inline-button">Remove current photo</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="sq-profile-media-panel">
                <form method="post" action="<?= esc(site_url('cover-photo')) ?>" enctype="multipart/form-data" class="sq-profile-upload-form">
                    <?= csrf_field() ?>
                    <div>
                        <label for="cover_photo" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Cover photo</label>
                        <input id="cover_photo" type="file" name="cover_photo" accept="image/jpeg,image/png,image/gif,image/webp" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" required>
                        <p class="text-sm text-muted-foreground sq-profile-media-hint">Use a wide image for the best fit on your public header.</p>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2">Upload cover photo</button>
                </form>
                <?php if (! empty($authUser['cover_photo_url'])): ?>
                    <form method="post" action="<?= esc(site_url('cover-photo')) ?>" class="sq-profile-inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="sq-inline-button">Remove cover photo</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </section>

    <section class="sq-post-card-v2">
        <div style="padding: 1.25rem; font-size: .875rem;">
            <div class="sq-card-title" style="font-size: .875rem;">Profile information</div>
            <p class="sq-muted">Update your public profile and contact details.</p>

            <form method="post" action="<?= esc(site_url('settings/profile')) ?>" class="flex flex-col gap-6" style="margin-top: 1rem;">
            <?= csrf_field() ?>
            <div class="grid gap-2">
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Username</label>
                <input type="text" value="<?= esc($authUser['username']) ?>" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" disabled>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <label for="first_name" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">First name</label>
                    <input id="first_name" type="text" name="first_name" value="<?= esc(old('first_name', $authUser['first_name'])) ?>" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" required>
                </div>
                <div class="grid gap-2">
                    <label for="last_name" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Last name</label>
                    <input id="last_name" type="text" name="last_name" value="<?= esc(old('last_name', $authUser['last_name'])) ?>" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" required>
                </div>
            </div>
            <div class="grid gap-2">
                <label for="profile-email" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Email address</label>
                <input id="profile-email" type="email" name="email" value="<?= esc(old('email', $authUser['email'])) ?>" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <label for="date_of_birth" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Date of birth</label>
                    <input id="date_of_birth" type="date" name="date_of_birth" value="<?= esc(old('date_of_birth', substr((string) ($authUser['date_of_birth'] ?? ''), 0, 10))) ?>" data-calendar-size="small" class="custom-date flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" required>
                </div>
                <div class="grid gap-2">
                    <label for="sex" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Sex</label>
                    <select id="sex" name="sex" data-dropdown-size="small" class="custom-select flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" required>
                        <?php foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_say' => 'Prefer not to say'] as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= old('sex', $authUser['sex']) === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="grid gap-2">
                <label for="bio" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Bio</label>
                <textarea id="bio" name="bio" rows="4" maxlength="500" class="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" placeholder="Tell people a little about yourself..."><?= esc(old('bio', $authUser['bio'] ?? '')) ?></textarea>
            </div>
            <?php if (! empty($mustVerifyEmail) && empty($authUser['email_verified_at'])): ?>
                <div class="sq-helper-card">
                    <strong>Your email address is not verified.</strong>
                    <p class="sq-muted">Generate a fresh verification link if you changed your address or never confirmed it.</p>
                    <form method="post" action="<?= esc(site_url('email/verification-notification')) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="sq-inline-button">Generate verification link</button>
                    </form>
                </div>
            <?php endif; ?>
            <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2">Save profile</button>
        </form>
        </div>
    </section>

    <section class="sq-post-card-v2">
        <div style="padding: 1.25rem; font-size: .875rem;">
            <div class="sq-card-title" style="font-size: .875rem;">Delete account</div>
            <p class="sq-muted">This permanently removes your account and associated social graph data.</p>
            <form method="post" action="<?= esc(site_url('settings/profile')) ?>" class="flex flex-col gap-6" style="margin-top: 1rem;">
            <?= csrf_field() ?>
            <input type="hidden" name="_method" value="DELETE">
            <div class="grid gap-2">
                <label for="delete-password" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Current password</label>
                <input id="delete-password" type="password" name="password" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" required>
            </div>
            <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2">Delete my account</button>
        </form>
        </div>
    </section>

<?= view('components/profile_crop_modal') ?>
