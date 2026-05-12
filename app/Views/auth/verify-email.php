<section class="sq-auth-layout sq-auth-layout-single">
    <div class="sq-auth-panel sq-card sq-auth-card">
        <h1>Verify your email</h1>
        <p class="sq-auth-lead">
            Please verify your email address before opening the full social experience.
        </p>

        <?php if (($status ?? null) === 'verification-link-sent'): ?>
            <div class="sq-helper-card">
                <strong>A fresh verification link is ready.</strong>
                <p class="sq-muted">Use the link below in your local environment.</p>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= esc(site_url('email/verification-notification')) ?>" class="sq-form-stack">
            <?= csrf_field() ?>
            <button type="submit" class="sq-btn sq-btn-primary sq-btn-block">Generate verification link</button>
        </form>

        <?php if (! empty($verificationLink)): ?>
            <div class="sq-helper-card">
                <strong>Verification link preview</strong>
                <a href="<?= esc($verificationLink) ?>" class="sq-inline-button"><?= esc($verificationLink) ?></a>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= esc(site_url('logout')) ?>" class="sq-form-stack sq-form-stack-compact">
            <?= csrf_field() ?>
            <button type="submit" class="sq-btn sq-btn-secondary sq-btn-block">Log out</button>
        </form>
    </div>
</section>
