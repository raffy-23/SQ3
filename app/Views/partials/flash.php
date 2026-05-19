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
<div id="sq-toast-stack" style="position: fixed; top: 1rem; right: 1rem; z-index: 100; display: flex; flex-direction: column; gap: 0.5rem; width: 100%; max-width: 420px; padding: 1rem; pointer-events: none; transition: opacity 0.3s;">
    <?php if (session('success')): ?>
        <div style="pointer-events: auto; display: flex; flex-direction: column; gap: 0.25rem; border-radius: 0.5rem; border: 1px solid #10b981; background: #d1fae5; padding: 1rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); overflow: hidden; font-size: 0.875rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="height: 1rem; width: 1rem; color: #059669;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                <div style="font-weight: 600; color: #065f46;">Success</div>
            </div>
            <div style="margin-left: 1.5rem; color: #047857;"><?= esc((string) session('success')) ?></div>
        </div>
    <?php endif; ?>

    <?php if (! $isAuthRoute && session('status') && session('status') !== 'verification-link-sent'): ?>
        <div style="pointer-events: auto; display: flex; flex-direction: column; gap: 0.25rem; border-radius: 0.5rem; border: 1px solid #3b82f6; background: #dbeafe; padding: 1rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); overflow: hidden; font-size: 0.875rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="height: 1rem; width: 1rem; color: #2563eb;"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <div style="font-weight: 600; color: #1e40af;">Information</div>
            </div>
            <div style="margin-left: 1.5rem; color: #1e3a8a;"><?= esc((string) session('status')) ?></div>
        </div>
    <?php endif; ?>

    <?php if (session('error')): ?>
        <div style="pointer-events: auto; display: flex; flex-direction: column; gap: 0.25rem; border-radius: 0.5rem; border: 1px solid #ef4444; background: #fee2e2; padding: 1rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); overflow: hidden; font-size: 0.875rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="height: 1rem; width: 1rem; color: #dc2626;"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <div style="font-weight: 600; color: #991b1b;">Error</div>
            </div>
            <div style="margin-left: 1.5rem; color: #7f1d1d;"><?= esc((string) session('error')) ?></div>
        </div>
    <?php endif; ?>

    <?php if (! $isAuthRoute && ! empty($errors)): ?>
        <div style="pointer-events: auto; display: flex; flex-direction: column; gap: 0.25rem; border-radius: 0.5rem; border: 1px solid #ef4444; background: #fee2e2; padding: 1rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); overflow: hidden; font-size: 0.875rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="height: 1rem; width: 1rem; color: #dc2626;"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <div style="font-weight: 600; color: #991b1b;">Please review the following:</div>
            </div>
            <ul style="list-style-type: disc; list-style-position: inside; margin-left: 1.5rem; margin-top: 0.25rem; color: #7f1d1d;">
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
