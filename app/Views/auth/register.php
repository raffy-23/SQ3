<div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
    <div class="w-full max-w-sm">
        <div class="flex flex-col gap-8">
            <div class="flex flex-col items-center gap-4">
                <a href="<?= esc(site_url('/')) ?>" class="flex flex-col items-center gap-2 font-medium">
                    <div class="mb-1 flex items-center justify-center">
                        <?= view('partials/logo_mark', ['class' => 'h-10 w-auto text-black dark:text-white sm:h-12']) ?>
                    </div>
                    <span class="sr-only">Create an account</span>
                </a>

                <div class="space-y-2 text-center">
                    <h1 class="text-xl font-medium">Create an account</h1>
                    <p class="text-center text-sm text-muted-foreground">
                        Enter your details below to create your account
                    </p>
                </div>
            </div>

            <form method="post" action="<?= esc(site_url('register')) ?>" class="flex flex-col gap-6">
                <?= csrf_field() ?>

                <div class="grid gap-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <label for="first_name" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">First name</label>
                            <input
                                id="first_name"
                                type="text"
                                name="first_name"
                                value="<?= esc(old('first_name')) ?>"
                                required
                                autofocus
                                tabindex="1"
                                autocomplete="given-name"
                                placeholder="First name"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            />
                        </div>

                        <div class="grid gap-2">
                            <label for="last_name" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Last name</label>
                            <input
                                id="last_name"
                                type="text"
                                name="last_name"
                                value="<?= esc(old('last_name')) ?>"
                                required
                                tabindex="2"
                                autocomplete="family-name"
                                placeholder="Last name"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <label for="username" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Username</label>
                        <input
                            id="username"
                            type="text"
                            name="username"
                            value="<?= esc(old('username')) ?>"
                            required
                            tabindex="3"
                            autocomplete="username"
                            placeholder="your_username"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>

                    <div class="grid gap-2">
                        <label for="email" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Email address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="<?= esc(old('email')) ?>"
                            required
                            tabindex="4"
                            autocomplete="email"
                            placeholder="email@example.com"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <label for="date_of_birth" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Date of birth</label>
                            <input
                                id="date_of_birth"
                                type="date"
                                name="date_of_birth"
                                value="<?= esc(old('date_of_birth')) ?>"
                                required
                                tabindex="5"
                                data-calendar-size="small"
                                class="custom-date flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            />
                        </div>

                        <div class="grid gap-2">
                            <label for="sex" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Sex</label>
                            <select
                                id="sex"
                                name="sex"
                                tabindex="6"
                                required
                                data-dropdown-size="small"
                                class="custom-select flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="" disabled hidden <?= empty(old('sex')) ? 'selected' : '' ?>></option>
                                <?php foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_say' => 'Prefer not to say'] as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" <?= old('sex') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <label for="password" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            tabindex="7"
                            autocomplete="new-password"
                            placeholder="Password"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>

                    <div class="grid gap-2">
                        <label for="password_confirmation" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Confirm password</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            tabindex="8"
                            autocomplete="new-password"
                            placeholder="Confirm password"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2 mt-2 w-full"
                        tabindex="9"
                        data-test="register-user-button"
                    >
                        Create account
                    </button>
                </div>

                <div class="text-center text-sm text-muted-foreground">
                    Already have an account? 
                    <a href="<?= esc(site_url('login')) ?>" tabindex="10" class="underline-offset-4 hover:underline text-foreground">
                        Log in
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
