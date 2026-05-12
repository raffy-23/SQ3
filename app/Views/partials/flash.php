<?php
$errors = session('errors');
if (is_array($errors)) {
    $errors = array_filter($errors, static fn ($value): bool => is_string($value) && $value !== '');
}

// Do not show global status or errors on pages that use the AuthSimpleLayout (since they handle it inline).
// We check if we are in an auth route based on the URI.
$uri = uri_string();
$isAuthRoute = in_array($uri, ['login', 'register', 'forgot-password', 'reset-password'], true) || str_starts_with($uri, 'reset-password/');
?>
<div id="sq-toast-stack" class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2 w-full max-w-[420px] p-4 sm:p-0 pointer-events-none transition-opacity duration-300">
    <?php if (session('success')): ?>
        <div class="pointer-events-auto flex w-full flex-col gap-1 rounded-lg border bg-background p-4 shadow-lg overflow-hidden text-sm">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-green-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                <div class="font-medium">Success</div>
            </div>
            <div class="text-muted-foreground ml-6"><?= esc((string) session('success')) ?></div>
        </div>
    <?php endif; ?>

    <?php if (! $isAuthRoute && session('status') && session('status') !== 'verification-link-sent'): ?>
        <div class="pointer-events-auto flex w-full flex-col gap-1 rounded-lg border bg-background p-4 shadow-lg overflow-hidden text-sm">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-blue-600"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <div class="font-medium">Information</div>
            </div>
            <div class="text-muted-foreground ml-6"><?= esc((string) session('status')) ?></div>
        </div>
    <?php endif; ?>

    <?php if (session('error')): ?>
        <div class="pointer-events-auto flex w-full flex-col gap-1 rounded-lg border bg-destructive p-4 shadow-lg text-destructive-foreground overflow-hidden text-sm">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <div class="font-medium">Error</div>
            </div>
            <div class="opacity-90 ml-6"><?= esc((string) session('error')) ?></div>
        </div>
    <?php endif; ?>

    <?php if (! $isAuthRoute && ! empty($errors)): ?>
        <div class="pointer-events-auto flex w-full flex-col gap-1 rounded-lg border bg-destructive p-4 shadow-lg text-destructive-foreground overflow-hidden text-sm">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <div class="font-medium">Please review the following:</div>
            </div>
            <ul class="list-disc list-inside opacity-90 ml-6 mt-1">
                <?php foreach ($errors as $message): ?>
                    <li><?= esc($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<script>
    setTimeout(() => {
        const flashStack = document.getElementById('sq-toast-stack');
        if (flashStack && flashStack.children.length > 0) {
            flashStack.style.opacity = '0';
            setTimeout(() => flashStack.remove(), 300);
        }
    }, 4000);
</script>
