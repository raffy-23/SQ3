<?php
// Extract specific validation errors from session to show as toasts
$_regErrors    = session('errors') ?? [];
$_pwMinError   = null;
$_pwMatchError = null;
$_emailError   = null;
$_usernameError = null;
$_otherErrors  = [];
$_regSuccess   = session('success');

if (is_array($_regErrors)) {
    foreach ($_regErrors as $_field => $_msg) {
        $_msg = (string) $_msg;
        if ($_field === 'password' && stripos($_msg, 'least') !== false) {
            $_pwMinError = $_msg;
        } elseif ($_field === 'password_confirmation' || stripos($_msg, 'match') !== false) {
            $_pwMatchError = $_msg;
        } elseif ($_field === 'email') {
            $_emailError = $_msg;
        } elseif ($_field === 'username') {
            $_usernameError = $_msg;
        } else {
            $_otherErrors[] = $_msg;
        }
    }
}
?>

<!-- ── Register Toast Container ─────────────────────────────────── -->
<div id="reg-toast-stack" style="position:fixed;top:1.25rem;right:1.25rem;z-index:9999;display:flex;flex-direction:column;gap:.6rem;width:100%;max-width:360px;pointer-events:none;"></div>

<script>
(function () {
    var stack = document.getElementById('reg-toast-stack');

    function showToast(message, type) {
        var colours = {
            error:   { bg:'#fee2e2', border:'#ef4444', text:'#991b1b', icon:'#dc2626', label:'Error' },
            success: { bg:'#d1fae5', border:'#10b981', text:'#047857', icon:'#059669', label:'Success' },
            warning: { bg:'#fef9c3', border:'#f59e0b', text:'#92400e', icon:'#d97706', label:'Warning' },
        };
        var c = colours[type] || colours.error;

        var iconMap = {
            error:   '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>',
            success: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>',
            warning: '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/>',
        };

        var toast = document.createElement('div');
        toast.style.cssText = 'pointer-events:auto;display:flex;flex-direction:column;gap:.25rem;border-radius:.5rem;border:1px solid '+c.border+';background:'+c.bg+';padding:1rem;box-shadow:0 10px 15px -3px rgba(0,0,0,.12);font-size:.875rem;transition:opacity .3s,transform .3s;';
        toast.innerHTML =
            '<div style="display:flex;align-items:center;gap:.5rem;">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="'+c.icon+'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'+iconMap[type]+'</svg>' +
                '<span style="font-weight:600;color:'+c.text+';">'+c.label+'</span>' +
            '</div>' +
            '<div style="margin-left:1.5rem;color:'+c.text+';">'+message+'</div>';

        stack.appendChild(toast);

        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-8px)';
            setTimeout(function () { toast.remove(); }, 320);
        }, 4000);
    }

    // ── Show server-side errors on page load ──
    <?php if ($_pwMinError): ?>
    showToast(<?= json_encode($_pwMinError) ?>, 'error');
    <?php endif; ?>

    <?php if ($_pwMatchError): ?>
    showToast(<?= json_encode($_pwMatchError) ?>, 'error');
    <?php endif; ?>

    <?php if ($_emailError): ?>
    showToast(<?= json_encode($_emailError) ?>, 'error');
    <?php endif; ?>

    <?php if ($_usernameError): ?>
    showToast(<?= json_encode($_usernameError) ?>, 'error');
    <?php endif; ?>

    <?php foreach ($_otherErrors as $_oe): ?>
    showToast(<?= json_encode($_oe) ?>, 'error');
    <?php endforeach; ?>

    <?php if ($_regSuccess): ?>
    showToast(<?= json_encode((string) $_regSuccess) ?>, 'success');
    <?php endif; ?>

    // ── Client-side live validation ──
    document.addEventListener('DOMContentLoaded', function () {
        var pwField      = document.getElementById('password');
        var pwConfField  = document.getElementById('password_confirmation');
        var shownMinWarn = false;
        var shownMatch   = false;

        if (pwField) {
            pwField.addEventListener('blur', function () {
                if (pwField.value.length > 0 && pwField.value.length < 8) {
                    if (!shownMinWarn) {
                        shownMinWarn = true;
                        showToast('Password must be at least 8 characters.', 'warning');
                        setTimeout(function () { shownMinWarn = false; }, 4200);
                    }
                }
            });
        }

        if (pwConfField) {
            pwConfField.addEventListener('blur', function () {
                if (pwConfField.value.length > 0 && pwField && pwField.value !== pwConfField.value) {
                    if (!shownMatch) {
                        shownMatch = true;
                        showToast('Passwords do not match.', 'error');
                        setTimeout(function () { shownMatch = false; }, 4200);
                    }
                }
            });
        }

        // Success on redirect (only if page freshly loaded after register action)
        <?php if ($_regSuccess): ?>
        // Already shown above via PHP
        <?php endif; ?>
    });
})();
</script>


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
                    <a href="<?= esc(site_url('login')) ?>" tabindex="10" class="sq-auth-switch-link underline-offset-4 text-foreground">
                        Log in
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
