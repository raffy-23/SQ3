<div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
    <div class="w-full max-w-sm">
        <div class="flex flex-col gap-8">
            <div class="flex flex-col items-center gap-4">
                <a href="<?= esc(site_url('/')) ?>" class="flex flex-col items-center gap-2 font-medium">
                    <div class="mb-1 flex items-center justify-center">
                        <?= view('partials/logo_mark', ['class' => 'h-10 w-auto text-black dark:text-white sm:h-12']) ?>
                    </div>
                    <span class="sr-only">Forgot password</span>
                </a>

                <div class="space-y-2 text-center">
                    <h1 class="text-xl font-medium">Forgot password</h1>
                    <p class="text-center text-sm text-muted-foreground">
                        Enter your email address to receive a password reset link
                    </p>
                </div>
            </div>

            <?php if (session('status')): ?>
                <div class="mb-4 text-center text-sm font-medium text-green-600">
                    <?= esc(session('status')) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= esc(site_url('forgot-password')) ?>" class="flex flex-col gap-6">
                <?= csrf_field() ?>

                <div class="grid gap-6">
                    <div class="grid gap-2">
                        <label for="email" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Email address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="<?= esc(old('email')) ?>"
                            required
                            autofocus
                            autocomplete="off"
                            placeholder="email@example.com"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2"
                        data-test="email-password-reset-link-button"
                    >
                        Email password reset link
                    </button>
                </div>

                <?php if (session('resetLink')): ?>
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-4 mt-4">
                        <strong class="block mb-2 text-sm font-medium">Reset link preview</strong>
                        <p class="text-xs text-muted-foreground mb-2">Mail delivery is not wired yet, so use this link directly in your local environment.</p>
                        <a href="<?= esc((string) session('resetLink')) ?>" class="text-xs text-primary underline break-all"><?= esc((string) session('resetLink')) ?></a>
                    </div>
                <?php endif; ?>

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
