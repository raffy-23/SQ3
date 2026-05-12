<section class="sq-auth-layout sq-auth-layout-single">
    <div class="sq-auth-panel sq-card sq-auth-card">
        <h1>Reset password</h1>
        <p class="sq-auth-lead">Choose a new password for <strong><?= esc($email ?? '') ?></strong>.</p>

        <form method="post" action="<?= esc(site_url('reset-password')) ?>" class="sq-form-stack">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= esc($token ?? '') ?>">
            <input type="hidden" name="email" value="<?= esc($email ?? '') ?>">

            <div>
                <label for="reset-password" class="sq-label">New password</label>
                <input id="reset-password" type="password" name="password" class="sq-input" required>
            </div>
            <div>
                <label for="reset-password-confirmation" class="sq-label">Confirm password</label>
                <input id="reset-password-confirmation" type="password" name="password_confirmation" class="sq-input" required>
            </div>
            <button type="submit" class="sq-btn sq-btn-primary sq-btn-block">Reset password</button>
        </form>
    </div>
</section>
