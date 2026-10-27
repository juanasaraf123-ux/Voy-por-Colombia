<?php
$siteName = setting('site_name');
$title = !empty($pageTitle) ? $pageTitle . ' · ' . $siteName : $siteName . ' · ' . setting('site_tagline');
$description = $metaDescription ?? setting('hero_subtitle');
$needsMap = $needsMap ?? true;
?>
<!doctype html>
<html lang="es-CO" class="no-js">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e(str_limit($description, 160)) ?>">
  <meta name="theme-color" content="#0f5c4a">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= e($title) ?>">
  <meta property="og:description" content="<?= e(str_limit($description, 200)) ?>">
  <?php if (!empty($ogImage)): ?><meta property="og:image" content="<?= e(base_url() . $ogImage) ?>"><?php endif; ?>
  <link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
  <link rel="preload" href="<?= e(url('/assets/fonts/fraunces-latin-full-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="<?= e(url('/assets/fonts/plus-jakarta-sans-latin-wght-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
  <?php if ($needsMap): ?>
    <link rel="stylesheet" href="<?= e(asset('vendor/leaflet/leaflet.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('vendor/markercluster/MarkerCluster.css')) ?>">
  <?php endif; ?>
  <link rel="preload" href="<?= e(url('/assets/fonts/kaushan-script-latin-400-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/design.css')) ?>">
  <script src="<?= e(asset('js/theme.js')) ?>"></script>
  <script type="application/json" id="app-config"><?= json_encode([
      'base' => url('/'),
      'api' => url('/api'),
      'geo' => url('/data/geo'),
      'icons' => url('/assets/img/icons.svg'),
      'tiles' => (string) env('MAP_TILE_URL', ''),
      'tilesAttribution' => (string) env('MAP_TILE_ATTRIBUTION', ''),
  ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
  <a class="skip-link" href="#contenido">Saltar al contenido</a>
  <?= App\Core\View::partial('header', ['transparentHeader' => $transparentHeader ?? false]) ?>
  <main id="contenido" tabindex="-1"><?= $content ?></main>
  <?= App\Core\View::partial('footer') ?>
  <div class="toasts" data-toasts aria-live="polite"></div>
  <div class="lightbox" data-lightbox aria-hidden="true" role="dialog" aria-modal="true" aria-label="Visor multimedia">
    <span class="lb-count" data-lb-count></span>
    <button class="lb-btn lb-close" type="button" data-lb-close aria-label="Cerrar visor"><?= icon('close') ?></button>
    <button class="lb-btn lb-prev" type="button" data-lb-prev aria-label="Anterior"><?= icon('chevron-left') ?></button>
    <button class="lb-btn lb-next" type="button" data-lb-next aria-label="Siguiente"><?= icon('chevron-right') ?></button>
    <div class="lightbox-stage" data-lb-stage></div>
    <div class="lightbox-caption" data-lb-caption></div>
  </div>
  <?php if ($needsMap): ?>
    <script src="<?= e(asset('vendor/leaflet/leaflet.js')) ?>" defer></script>
    <script src="<?= e(asset('vendor/markercluster/leaflet.markercluster.js')) ?>" defer></script>
    <script src="<?= e(asset('js/map.js')) ?>" defer></script>
  <?php endif; ?>
  <script src="<?= e(asset('js/app.js')) ?>" defer></script>
  <script src="<?= e(asset('js/pages.js')) ?>" defer></script>
</body>
</html>
