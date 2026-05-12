<div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
    <div class="w-full max-w-sm">
        <div class="flex flex-col gap-8">
            <div class="flex flex-col items-center gap-4">
                <a href="<?= esc(site_url('/')) ?>" class="flex flex-col items-center gap-2 font-medium">
                    <div class="mb-1 flex items-center justify-center">
                        <?= view('partials/logo_mark', ['class' => 'h-10 w-auto text-black dark:text-white sm:h-12']) ?>
                    </div>
                    <span class="sr-only">Log in to your account</span>
                </a>

                <div class="space-y-2 text-center">
                    <h1 class="text-xl font-medium">Log in to your account</h1>
                    <p class="text-center text-sm text-muted-foreground">
                        Enter your email and password below to log in
                    </p>
                </div>
            </div>

            <form method="post" action="<?= esc(site_url('login')) ?>" class="flex flex-col gap-6">
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
                            tabindex="1"
                            autocomplete="email"
                            placeholder="email@example.com"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center">
                            <label for="password" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Password</label>
                            <?php if (! empty($canResetPassword)): ?>
                                <a
                                    href="<?= esc(site_url('forgot-password')) ?>"
                                    class="ml-auto text-sm underline-offset-4 hover:underline"
                                    tabindex="5"
                                >
                                    Forgot password?
                                </a>
                            <?php endif; ?>
                        </div>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            tabindex="2"
                            autocomplete="current-password"
                            placeholder="Password"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>

                    <div class="flex items-center space-x-3">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            tabindex="3"
                            value="1"
                            <?= old('remember') ? 'checked' : '' ?>
                            class="h-4 w-4 rounded-sm border border-primary text-primary shadow focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring accent-primary"
                        />
                        <label for="remember" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Remember me</label>
                    </div>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2 mt-4 w-full"
                        tabindex="4"
                        data-test="login-button"
                    >
                        Log in
                    </button>
                </div>

                <?php if (! empty($canRegister)): ?>
                    <div class="text-center text-sm text-muted-foreground">
                        Don't have an account? 
                        <a href="<?= esc(site_url('register')) ?>" tabindex="5" class="underline-offset-4 hover:underline text-foreground">
                            Sign up
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
