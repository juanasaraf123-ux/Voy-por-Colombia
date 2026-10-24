<?php
use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;
use App\Sections;
$path = Request::path();
$active = static fn (string $p, bool $exact = false) => $exact ? $path === $p : ($path === $p || str_starts_with($path, $p . '/'));
$u = Auth::user();
$messages = Session::takeMessages();
?>
<!doctype html>
<html lang="es-CO">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf-token" content="<?= e(App\Core\Csrf::token()) ?>">
  <title><?= e(($pageTitle ?? 'Panel') . ' · Panel · ' . setting('site_name')) ?></title>
  <link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
  <link rel="stylesheet" href="<?= e(asset('vendor/leaflet/leaflet.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
  <script type="application/json" id="admin-config"><?= json_encode(['base' => url('/'), 'icons' => url('/assets/img/icons.svg'), 'geo' => url('/data/geo')], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
</head>
<body class="admin">
  <a class="skip-link" href="#admin-main">Saltar al contenido</a>
  <aside class="sidebar" data-sidebar>
    <a class="brand" href="<?= e(url('/admin')) ?>"><?= App\Core\View::partial('logo') ?><span><strong><?= e(setting('site_name')) ?></strong><small>Panel administrativo</small></span></a>
    <nav aria-label="Menú del panel">
      <a href="<?= e(url('/admin')) ?>" <?= $active('/admin', true) ? 'aria-current="page"' : '' ?>><?= icon('home') ?> Panel</a>
      <p class="nav-label">Contenido</p>
      <?php foreach (Sections::ALL as $s): ?>
        <a href="<?= e(url('/admin/contenido/' . $s['path'])) ?>" style="--nav-c: <?= e($s['color']) ?>" <?= $active('/admin/contenido/' . $s['path']) ? 'aria-current="page"' : '' ?>><?= icon($s['icon']) ?> <?= e($s['label']) ?></a>
      <?php endforeach; ?>
      <a href="<?= e(url('/admin/rutas')) ?>" <?= $active('/admin/rutas') ? 'aria-current="page"' : '' ?>><?= icon('route') ?> Rutas</a>
      <p class="nav-label">Sitio</p>
      <a href="<?= e(url('/admin/multimedia')) ?>" <?= $active('/admin/multimedia') ? 'aria-current="page"' : '' ?>><?= icon('camera') ?> Multimedia</a>
      <a href="<?= e(url('/admin/mapa')) ?>" <?= $active('/admin/mapa') ? 'aria-current="page"' : '' ?>><?= icon('map') ?> Mapa</a>
      <a href="<?= e(url('/admin/inicio')) ?>" <?= $active('/admin/inicio') ? 'aria-current="page"' : '' ?>><?= icon('star') ?> Página de inicio</a>
      <a href="<?= e(url('/admin/categorias')) ?>" <?= $active('/admin/categorias') ? 'aria-current="page"' : '' ?>><?= icon('tag') ?> Categorías</a>
      <?php if (Auth::isAdmin()): ?>
        <p class="nav-label">Administración</p>
        <a href="<?= e(url('/admin/usuarios')) ?>" <?= $active('/admin/usuarios') ? 'aria-current="page"' : '' ?>><?= icon('users') ?> Usuarios</a>
        <a href="<?= e(url('/admin/auditoria')) ?>" <?= $active('/admin/auditoria') ? 'aria-current="page"' : '' ?>><?= icon('shield') ?> Auditoría</a>
        <a href="<?= e(url('/admin/configuracion')) ?>" <?= $active('/admin/configuracion') ? 'aria-current="page"' : '' ?>><?= icon('settings') ?> Configuración</a>
      <?php endif; ?>
    </nav>
    <div class="sidebar-foot">
      <a class="me" href="<?= e(url('/admin/perfil')) ?>"><span class="avatar"><?= e(mb_strtoupper(mb_substr($u['name'] ?? '?', 0, 1))) ?></span><span><strong><?= e($u['name'] ?? '') ?></strong><small><?= e($u['role_name'] ?? '') ?></small></span></a>
      <div class="flex">
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/')) ?>" target="_blank" rel="noopener"><?= icon('globe') ?> Ver sitio</a>
        <form method="post" action="<?= e(url('/admin/logout')) ?>"><?= csrf_field() ?><button class="btn btn-ghost btn-sm" type="submit"><?= icon('logout') ?> Salir</button></form>
      </div>
    </div>
  </aside>
  <div class="admin-wrap">
    <header class="topbar">
      <button class="icon-btn menu-btn" type="button" data-sidebar-toggle aria-label="Abrir menú"><?= icon('menu') ?></button>
      <h1><?= e($pageTitle ?? 'Panel') ?></h1>
    </header>
    <main id="admin-main" class="admin-main" tabindex="-1">
      <?php foreach ($messages as $m): ?>
        <div class="alert alert-<?= e($m['type']) ?>" role="<?= $m['type'] === 'error' ? 'alert' : 'status' ?>"><?= icon($m['type'] === 'error' ? 'alert' : 'check') ?><span><?= e($m['text']) ?></span><button type="button" class="alert-close" aria-label="Cerrar" data-dismiss><?= icon('close') ?></button></div>
      <?php endforeach; ?>
      <?= $content ?>
    </main>
  </div>
  <div class="modal" data-modal hidden></div>
  <script src="<?= e(asset('vendor/leaflet/leaflet.js')) ?>" defer></script>
  <script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</body>
</html>
