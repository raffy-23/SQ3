<div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
    <div class="w-full max-w-sm">
        <div class="flex flex-col gap-8">
            <div class="flex flex-col items-center gap-4">
                <a href="<?= esc(site_url('/')) ?>" class="flex flex-col items-center gap-2 font-medium">
                    <div class="mb-1 flex items-center justify-center">
                        <?= view('partials/logo_mark', ['class' => 'h-10 w-auto text-black dark:text-white sm:h-12']) ?>
                    </div>
                    <span class="sr-only">Verify your email</span>
                </a>

                <div class="space-y-2 text-center">
                    <h1 class="text-xl font-medium">Verify your email</h1>
                    <p class="text-center text-sm text-muted-foreground">
                        Please verify your email address before opening the full social experience.
                    </p>
                </div>
            </div>

            <?php if (session('error')): ?>
                <div class="text-center text-sm font-medium text-red-600 dark:text-red-400">
                    <?= esc(session('error')) ?>
                </div>
            <?php endif; ?>

            <?php if (($status ?? null) === 'verification-link-sent'): ?>
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-4">
                    <strong class="block mb-2 text-sm font-medium">A fresh verification link is ready.</strong>
                    <p class="text-xs text-muted-foreground">Use the link below in your local environment.</p>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= esc(site_url('email/verification-notification')) ?>" class="flex flex-col gap-6">
                <?= csrf_field() ?>

                <div class="grid gap-6">
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2"
                    >
                        Generate verification link
                    </button>
                </div>

                <?php if (! empty($verificationLink)): ?>
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-4 overflow-hidden">
                        <strong class="block mb-2 text-sm font-medium">Verification link preview</strong>
                        <a href="<?= esc($verificationLink) ?>" class="sq-reset-link text-xs text-primary underline"><?= esc($verificationLink) ?></a>
                    </div>
                <?php endif; ?>
            </form>

            <form method="post" action="<?= esc(site_url('logout')) ?>">
                <?= csrf_field() ?>

                <div class="space-x-1 text-center text-sm text-muted-foreground">
                    <span>Or,</span>
                    <button type="submit" class="underline-offset-4 hover:underline text-foreground bg-transparent border-none cursor-pointer p-0 text-sm">
                        log out
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
