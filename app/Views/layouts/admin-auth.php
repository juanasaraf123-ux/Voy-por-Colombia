<?php $messages = App\Core\Session::takeMessages(); ?>
<!doctype html>
<html lang="es-CO">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e(($pageTitle ?? 'Ingresar') . ' · ' . setting('site_name')) ?></title>
  <link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
  <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="auth">
  <main class="auth-card">
    <a class="brand" href="<?= e(url('/')) ?>"><?= App\Core\View::partial('logo') ?><span><strong><?= e(setting('site_name')) ?></strong><small>Panel administrativo</small></span></a>
    <?php foreach ($messages as $m): ?>
      <div class="alert alert-<?= e($m['type']) ?>" role="alert"><?= icon($m['type'] === 'error' ? 'alert' : 'check') ?><span><?= e($m['text']) ?></span></div>
    <?php endforeach; ?>
    <?= $content ?>
  </main>
</body>
</html>
