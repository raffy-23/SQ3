<!DOCTYPE html>
<html lang="en" class="<?= ($appearance ?? 'system') === 'dark' ? 'dark' : '' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="<?= esc(csrf_hash()) ?>">
    <meta name="csrf-header" content="X-CSRF-TOKEN">
    <meta name="csrf-token-name" content="<?= esc(csrf_token()) ?>">
    <meta name="app-base-url" content="<?= esc(rtrim(base_url(), '/')) ?>">

    <title><?= esc(($pageTitle ?? $appName) . ' · ' . $appName) ?></title>
    <link rel="icon" href="<?= esc(base_url('favicon-light.svg')) ?>" type="image/svg+xml" media="(prefers-color-scheme: light)">
    <link rel="icon" href="<?= esc(base_url('favicon-dark.svg')) ?>" type="image/svg+xml" media="(prefers-color-scheme: dark)">
    <script>
        (function () {
            const appearance = <?= json_encode($appearance ?? 'system', JSON_UNESCAPED_SLASHES) ?>;
            const root = document.documentElement;

            if (appearance === 'dark') {
                root.classList.add('dark');
                return;
            }
            if (appearance === 'light') {
                root.classList.remove('dark');
                return;
            }
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
            if (prefersDark.matches) {
                root.classList.add('dark');
            }
            prefersDark.addEventListener('change', function (e) {
                if (appearance !== 'system') return;
                root.classList.toggle('dark', e.matches);
            });
        })();
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="<?= esc(base_url('build/assets/app-BwJKfC5G.css')) ?>">
    <link rel="stylesheet" href="<?= esc(base_url('css/sidequest-bridge.css')) ?>">
</head>
<body class="sq-page sq-guest-page">
    <div class="sq-guest-shell">
        <?= view('partials/flash', $pageData) ?>
        <?= view($contentView, $pageData) ?>
    </div>
    <?php
        $jsTagGuest = function (string $file): string {
            $path = FCPATH . 'js/' . $file;
            $url  = base_url('js/' . $file);
            if (is_file($path)) { $url .= '?v=' . filemtime($path); }
            return '<script src="' . esc($url) . '" defer></script>';
        };
        $guestScripts = [
            'sidequest-core.js',
            'sidequest-custom-ui.js',
            'sidequest-2fa.js',
            'sidequest.js',
        ];
    ?>
    <?php foreach ($guestScripts as $gs): ?>
    <?= $jsTagGuest($gs) ?>
    <?php endforeach; ?>
</body>
</html>
