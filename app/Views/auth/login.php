<?php
$_loginError   = session('error');
$_loginStatus  = session('status');
$_loginSuccess = session('success');
$_loginErrors  = session('errors') ?? [];
?>

<!-- ── Login Toast Container ─────────────────────────────────────── -->
<div id="login-toast-stack" style="position:fixed;top:1.25rem;right:1.25rem;z-index:9999;display:flex;flex-direction:column;gap:.6rem;width:100%;max-width:360px;pointer-events:none;"></div>

<script>
(function () {
    var stack = document.getElementById('login-toast-stack');

    function showToast(message, type) {
        var colours = {
            error:   { bg:'#fee2e2', border:'#ef4444', text:'#991b1b', icon:'#dc2626', label:'Error' },
            success: { bg:'#d1fae5', border:'#10b981', text:'#047857', icon:'#059669', label:'Success' },
            info:    { bg:'#dbeafe', border:'#3b82f6', text:'#1e40af', icon:'#2563eb', label:'Info' },
            warning: { bg:'#fef9c3', border:'#f59e0b', text:'#92400e', icon:'#d97706', label:'Warning' },
        };
        var c = colours[type] || colours.error;
        var iconMap = {
            error:   '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>',
            success: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>',
            info:    '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>',
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

    // ── Server-side flash messages ──
    <?php if ($_loginError): ?>
    showToast(<?= json_encode((string) $_loginError) ?>, 'error');
    <?php endif; ?>

    <?php if ($_loginSuccess): ?>
    showToast(<?= json_encode((string) $_loginSuccess) ?>, 'success');
    <?php endif; ?>

    <?php if ($_loginStatus && $_loginStatus !== 'verification-link-sent'): ?>
    showToast(<?= json_encode((string) $_loginStatus) ?>, 'info');
    <?php endif; ?>

    <?php if (is_array($_loginErrors)): ?>
    <?php foreach ($_loginErrors as $_msg): ?>
    showToast(<?= json_encode((string) $_msg) ?>, 'error');
    <?php endforeach; ?>
    <?php endif; ?>
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
                        <a href="<?= esc(site_url('register')) ?>" tabindex="5" class="sq-auth-switch-link underline-offset-4 text-foreground">
                            Sign up
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
