<section class="sq-auth-layout sq-auth-layout-single">
    <div class="sq-auth-panel sq-card sq-auth-card">
        <h1>Confirm your password</h1>
        <p class="sq-auth-lead">This area is protected. Enter your password to continue.</p>

        <form method="post" action="<?= esc(site_url('user/confirm-password')) ?>" class="sq-form-stack">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect" value="<?= esc($redirectTo ?? site_url('dashboard')) ?>">
            <div>
                <label for="confirm-password" class="sq-label">Password</label>
                <input id="confirm-password" type="password" name="password" class="sq-input" required>
            </div>
            <button type="submit" class="sq-btn sq-btn-primary sq-btn-block">Confirm password</button>
        </form>
    </div>
</section>
