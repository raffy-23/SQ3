<div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
    <div class="w-full max-w-sm">
        <div class="flex flex-col gap-8">
            <div class="flex flex-col items-center gap-4">
                <a href="<?= esc(site_url('/')) ?>" class="flex flex-col items-center gap-2 font-medium">
                    <div class="mb-1 flex items-center justify-center">
                        <?= view('partials/logo_mark', ['class' => 'h-10 w-auto text-black dark:text-white sm:h-12']) ?>
                    </div>
                    <span class="sr-only">Reset password</span>
                </a>

                <div class="space-y-2 text-center">
                    <h1 class="text-xl font-medium">Reset password</h1>
                    <p class="text-center text-sm text-muted-foreground">
                        Choose a new password for <strong><?= esc($email ?? '') ?></strong>.
                    </p>
                </div>
            </div>

            <form method="post" action="<?= esc(site_url('reset-password')) ?>" class="flex flex-col gap-6">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= esc($token ?? '') ?>">
                <input type="hidden" name="email" value="<?= esc($email ?? '') ?>">

                <div class="grid gap-6">
                    <div class="grid gap-2">
                        <label for="reset-password" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">New password</label>
                        <input
                            id="reset-password"
                            type="password"
                            name="password"
                            required
                            autofocus
                            autocomplete="new-password"
                            placeholder="New password"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>

                    <div class="grid gap-2">
                        <label for="reset-password-confirmation" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Confirm password</label>
                        <input
                            id="reset-password-confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Confirm password"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2"
                    >
                        Reset password
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
