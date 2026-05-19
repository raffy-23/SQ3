<div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
    <div class="w-full max-w-sm">
        <div class="flex flex-col gap-8">
            <div class="flex flex-col items-center gap-4">
                <a href="<?= esc(site_url('/')) ?>" class="flex flex-col items-center gap-2 font-medium">
                    <div class="mb-1 flex items-center justify-center">
                        <?= view('partials/logo_mark', ['class' => 'h-10 w-auto text-black dark:text-white sm:h-12']) ?>
                    </div>
                    <span class="sr-only">Two-factor challenge</span>
                </a>

                <div class="space-y-2 text-center">
                    <h1 class="text-xl font-medium">Two-factor challenge</h1>
                    <p class="text-center text-sm text-muted-foreground">
                        Enter either an authenticator code or one of your recovery codes.
                    </p>
                </div>
            </div>

            <?php if (session('error')): ?>
                <div class="text-center text-sm font-medium text-red-600 dark:text-red-400">
                    <?= esc(session('error')) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= esc(site_url('two-factor-challenge')) ?>" class="flex flex-col gap-6">
                <?= csrf_field() ?>

                <div class="sq-toggle-row" data-two-factor-tabs>
                    <button type="button" class="sq-chip is-active" data-two-factor-trigger="code">Authentication code</button>
                    <button type="button" class="sq-chip" data-two-factor-trigger="recovery">Recovery code</button>
                </div>

                <div class="grid gap-6">
                    <div class="grid gap-2" data-two-factor-panel="code">
                        <label for="code" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">6-digit code</label>
                        <input
                            id="code"
                            type="text"
                            name="code"
                            inputmode="numeric"
                            maxlength="6"
                            autofocus
                            autocomplete="one-time-code"
                            placeholder="123456"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>

                    <div class="grid gap-2" data-two-factor-panel="recovery" hidden>
                        <label for="recovery_code" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Recovery code</label>
                        <input
                            id="recovery_code"
                            type="text"
                            name="recovery_code"
                            autocomplete="off"
                            placeholder="Paste a recovery code"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2"
                    >
                        Continue
                    </button>
                </div>

                <div class="space-x-1 text-center text-sm text-muted-foreground">
                    <span>Or, return to</span>
                    <a href="<?= esc(site_url('login')) ?>" class="underline-offset-4 hover:underline text-foreground">
                        log in
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
