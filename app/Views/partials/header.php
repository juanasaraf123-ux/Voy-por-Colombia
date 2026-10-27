<?php
use App\Sections;
$nav = [['/', 'Inicio', 'home', '#ff5a5f']];
foreach (['tourism', 'gastronomy', 'coffee', 'entrepreneurship', 'culture'] as $k) {
    $nav[] = ['/' . Sections::ALL[$k]['path'], Sections::ALL[$k]['label'], $k === 'tourism' ? 'map' : Sections::ALL[$k]['icon'], Sections::ALL[$k]['color']];
}
$nav[] = ['/galeria', 'Galería', 'camera', Sections::GALLERY['color']];
$nav[] = ['/noticias', 'Noticias', 'news', Sections::ALL['news']['color']];
$current = App\Core\Request::path();
$isActive = static fn (string $href) => $href === '/' ? $current === '/' : ($current === $href || str_starts_with($current, $href . '/'));
?>
<header class="site-header <?= !empty($transparentHeader) ? 'is-transparent' : '' ?>" data-header>
  <div class="header-inner">
    <?= App\Core\View::partial('brand') ?>
    <nav class="main-nav" aria-label="Navegación principal">
      <ul>
        <?php foreach ($nav as [$href, $label, $ic, $color]): ?>
          <li><a href="<?= e(url($href)) ?>" style="--c: <?= e($color) ?>" <?= $isActive($href) ? 'aria-current="page"' : '' ?>><?= icon($ic) ?><span><?= e($label) ?></span></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <div class="header-actions">
      <button class="search-pill" type="button" data-search-open aria-label="Buscar (atajo: /)"><?= icon('search') ?><span>¿Qué lugar quieres descubrir?</span></button>
      <a class="icon-btn" href="<?= e(url('/favoritos')) ?>" aria-label="Mis favoritos"><?= icon('heart') ?><span class="count" data-fav-count>0</span></a>
      <a class="icon-btn" href="<?= e(url('/mi-ruta')) ?>" aria-label="Mi ruta"><?= icon('route') ?><span class="count" data-route-count>0</span></a>
      <a class="avatar-btn" href="<?= e(url('/admin')) ?>" aria-label="Acceso para administradores" title="Acceso para administradores"><?= icon('user') ?></a>
      <button class="icon-btn menu-toggle" type="button" data-menu-open aria-label="Abrir menú" aria-expanded="false" aria-controls="mobile-menu"><?= icon('menu') ?></button>
    </div>
  </div>
</header>

<div class="mobile-menu" id="mobile-menu" data-menu aria-hidden="true">
  <div class="mobile-menu-top">
    <?= App\Core\View::partial('brand') ?>
    <button class="icon-btn" type="button" data-menu-close aria-label="Cerrar menú"><?= icon('close') ?></button>
  </div>
  <nav aria-label="Navegación móvil">
    <ol>
      <?php foreach ($nav as [$href, $label, , $color]): ?>
        <li><a class="mm-link" style="--c: <?= e($color) ?>" href="<?= e(url($href)) ?>" <?= $isActive($href) ? 'aria-current="page"' : '' ?>><?= e($label) ?><span class="dot"></span></a></li>
      <?php endforeach; ?>
    </ol>
  </nav>
  <div class="mobile-menu-foot">
    <button class="btn btn-light" type="button" data-search-open><?= icon('search') ?> Buscar</button>
    <a class="btn btn-glass" href="<?= e(url('/mi-ruta')) ?>"><?= icon('route') ?> Mi ruta</a>
    <a class="btn btn-glass" href="<?= e(url('/favoritos')) ?>"><?= icon('heart') ?> Favoritos</a>
  </div>
</div>

<div class="theme-switch" role="group" aria-label="Tema de color">
  <button type="button" data-theme-set="dark" aria-label="Modo oscuro" title="Modo oscuro"><?= icon('moon') ?></button>
  <button type="button" data-theme-set="light" aria-label="Modo claro" title="Modo claro"><?= icon('sun') ?></button>
</div>

<div class="search-overlay" data-search aria-hidden="true">
  <div class="search-panel" role="dialog" aria-modal="true" aria-label="Buscar en la plataforma">
    <form class="search-field" action="<?= e(url('/buscar')) ?>" method="get" role="search">
      <?= icon('search') ?>
      <label class="sr-only" for="global-search">Buscar destinos, municipios, experiencias o noticias</label>
      <input id="global-search" name="q" type="search" autocomplete="off" placeholder="Busca un destino, municipio, plato, festival…" data-search-input>
      <button class="icon-btn" type="button" data-search-close aria-label="Cerrar buscador"><?= icon('close') ?></button>
    </form>
    <div class="search-results" data-search-results aria-live="polite">
      <div class="search-hint">
        Prueba con:
        <div class="chips">
          <?php foreach (['Caribe', 'Café', 'Cartagena', 'Carnaval', 'Ajiaco', 'Amazonas'] as $s): ?>
            <button type="button" class="chip" data-search-suggest="<?= e($s) ?>"><?= e($s) ?></button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>
