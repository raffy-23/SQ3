<section class="sq-auth-layout sq-auth-layout-single">
    <div class="sq-auth-panel sq-card sq-auth-card">
        <h1>Two-factor challenge</h1>
        <p class="sq-auth-lead">Enter either an authenticator code or one of your recovery codes.</p>

        <div class="sq-toggle-row" data-two-factor-tabs>
            <button type="button" class="sq-chip is-active" data-two-factor-trigger="code">Authentication code</button>
            <button type="button" class="sq-chip" data-two-factor-trigger="recovery">Recovery code</button>
        </div>

        <form method="post" action="<?= esc(site_url('two-factor-challenge')) ?>" class="sq-form-stack">
            <?= csrf_field() ?>
            <div data-two-factor-panel="code">
                <label for="code" class="sq-label">6-digit code</label>
                <input id="code" type="text" name="code" inputmode="numeric" maxlength="6" class="sq-input" placeholder="123456">
            </div>
            <div data-two-factor-panel="recovery" hidden>
                <label for="recovery_code" class="sq-label">Recovery code</label>
                <input id="recovery_code" type="text" name="recovery_code" class="sq-input" placeholder="Paste a recovery code">
            </div>
            <button type="submit" class="sq-btn sq-btn-primary sq-btn-block">Continue</button>
        </form>
    </div>
</section>
