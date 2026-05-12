    <section class="sq-post-card-v2">
        <div style="padding: 1.25rem; font-size: .875rem;">
            <div class="sq-card-title" style="font-size: .875rem;">Update password</div>
            <p class="sq-muted">Use a strong password to secure your account.</p>
            <form method="post" action="<?= esc(site_url('settings/security')) ?>" class="flex flex-col gap-6" style="margin-top: 1rem;">
            <?= csrf_field() ?>
            <div class="grid gap-2">
                <label for="current_password" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Current password</label>
                <input id="current_password" type="password" name="current_password" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" required>
            </div>
            <div class="grid gap-2">
                <label for="new_password" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">New password</label>
                <input id="new_password" type="password" name="password" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" required>
            </div>
            <div class="grid gap-2">
                <label for="password_confirmation" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" required>
            </div>
            <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2">Save password</button>
        </form>
        </div>
    </section>

    <section class="sq-post-card-v2">
        <div style="padding: 1.25rem; font-size: .875rem;">
            <div class="sq-card-title" style="font-size: .875rem;">Two-factor authentication</div>
        <?php if (! empty($twoFactorEnabled)): ?>
            <p class="text-sm text-muted-foreground">Your account is currently protected by a TOTP-based second factor.</p>
            <?php if (! empty($twoFactorRecoveryCodes)): ?>
                <div class="sq-helper-card" style="margin-top: 1rem;">
                    <strong>Recovery codes</strong>
                    <ul class="sq-alert-list">
                        <?php foreach ($twoFactorRecoveryCodes as $recoveryCode): ?>
                            <li><code><?= esc($recoveryCode) ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post" action="<?= esc(site_url('settings/two-factor/disable')) ?>" style="margin-top: 1rem;">
                <?= csrf_field() ?>
                <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2">Disable 2FA</button>
            </form>
        <?php elseif (! empty($pendingTwoFactorSetup)): ?>
            <p class="text-sm text-muted-foreground">Scan this QR code in your authenticator app, then enter the generated 6-digit code.</p>
            <div class="sq-helper-card" style="margin-top: 1rem;">
                <strong>Authenticator QR code</strong>
                <div style="max-width: 16rem; margin-top: .75rem;"><?= $pendingTwoFactorSetup['qrSvg'] ?></div>
                <p class="text-sm text-muted-foreground" style="margin-top: .75rem;">Manual setup key: <code><?= esc($pendingTwoFactorSetup['secret']) ?></code></p>
            </div>
            <div class="sq-helper-card">
                <strong>Recovery codes</strong>
                <ul class="sq-alert-list">
                    <?php foreach ($pendingTwoFactorSetup['recovery_codes'] as $recoveryCode): ?>
                        <li><code><?= esc($recoveryCode) ?></code></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <form method="post" action="<?= esc(site_url('settings/two-factor/confirm')) ?>" class="flex flex-col gap-6">
                <?= csrf_field() ?>
                <div class="grid gap-2">
                    <label for="two_factor_code" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Authentication code</label>
                    <input id="two_factor_code" type="text" name="code" inputmode="numeric" maxlength="6" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50" placeholder="123456" required>
                </div>
                <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2">Confirm and enable 2FA</button>
            </form>
            <form method="post" action="<?= esc(site_url('settings/two-factor/cancel')) ?>" style="margin-top: 1rem;">
                <?= csrf_field() ?>
                <button type="submit" class="sq-inline-button">Cancel setup</button>
            </form>
        <?php else: ?>
            <p class="text-sm text-muted-foreground">Add an authenticator app challenge to your login flow for better account security.</p>
            <form method="post" action="<?= esc(site_url('settings/two-factor/enable')) ?>" style="margin-top: 1rem;">
                <?= csrf_field() ?>
                <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2">Enable 2FA</button>
            </form>
        <?php endif; ?>
        </div>
    </section>
