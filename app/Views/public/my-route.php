<?php use App\Core\View; ?>
<section class="page-hero" style="--c: var(--turquesa); padding-bottom: 90px">
  <div class="container">
    <span class="eyebrow"><?= icon('route') ?> Planificador</span>
    <h1>Arma <em>tu ruta</em></h1>
    <p class="lead">Agrega destinos desde el mapa o desde cada ficha, ordénalos a tu gusto y comparte el recorrido con un enlace.</p>
  </div>
  <?= View::partial('wave') ?>
</section>
<section class="section" style="padding-top: 20px">
  <div class="container" data-my-route>
    <div class="my-route">
      <div>
        <div class="route-summary" data-route-summary></div>
        <div class="stop-list" data-stop-list></div>
        <div data-route-empty hidden>
          <?= View::partial('empty', ['icon' => 'route', 'title' => 'Tu ruta está vacía', 'text' => 'Abre el mapa y toca «+» en los destinos que quieras visitar.']) ?>
        </div>
        <div class="flex gap-2 wrap mt-4" data-route-actions>
          <a class="btn btn-primary" href="<?= e(url('/turismo')) ?>"><?= icon('plus') ?> Agregar destinos</a>
          <button class="btn btn-outline" type="button" data-route-share><?= icon('share') ?> Compartir ruta</button>
          <button class="btn btn-outline" type="button" data-route-clear><?= icon('trash') ?> Vaciar</button>
        </div>
        <p class="muted mt-2" style="font-size:.85rem">Las distancias son en línea recta y solo orientativas. Consulta el estado de las vías antes de viajar.</p>
      </div>
      <div class="route-map" data-route-map data-stops="[]"></div>
    </div>
  </div>
</section>
