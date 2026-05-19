<div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
    <div class="w-full max-w-sm">
        <div class="flex flex-col gap-8">
            <div class="flex flex-col items-center gap-4">
                <a href="<?= esc(site_url('/')) ?>" class="flex flex-col items-center gap-2 font-medium">
                    <div class="mb-1 flex items-center justify-center">
                        <?= view('partials/logo_mark', ['class' => 'h-10 w-auto text-black dark:text-white sm:h-12']) ?>
                    </div>
                    <span class="sr-only">Confirm your password</span>
                </a>

                <div class="space-y-2 text-center">
                    <h1 class="text-xl font-medium">Confirm your password</h1>
                    <p class="text-center text-sm text-muted-foreground">
                        This area is protected. Enter your password to continue.
                    </p>
                </div>
            </div>

            <?php if (session('error')): ?>
                <div class="text-center text-sm font-medium text-red-600 dark:text-red-400">
                    <?= esc(session('error')) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= esc(site_url('user/confirm-password')) ?>" class="flex flex-col gap-6">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect" value="<?= esc($redirectTo ?? site_url('feed')) ?>">

                <div class="grid gap-6">
                    <div class="grid gap-2">
                        <label for="confirm-password" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Password</label>
                        <input
                            id="confirm-password"
                            type="password"
                            name="password"
                            required
                            autofocus
                            autocomplete="current-password"
                            placeholder="Password"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2"
                    >
                        Confirm password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
