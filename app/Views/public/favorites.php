<?php use App\Core\View; ?>
<section class="page-hero" style="--c: var(--rosa); padding-bottom: 90px">
  <div class="container">
    <span class="eyebrow"><?= icon('heart') ?> Tus favoritos</span>
    <h1>Lugares que <em>no quieres olvidar</em></h1>
    <p class="lead">Se guardan en este navegador, sin necesidad de crear una cuenta. Toca el corazón en cualquier destino para agregarlo.</p>
  </div>
  <?= View::partial('wave') ?>
</section>
<section class="section" style="padding-top: 20px">
  <div class="container">
    <div class="grid grid-3" data-favorites-list></div>
    <div data-favorites-empty hidden><?= View::partial('empty', ['icon' => 'heart', 'title' => 'Aún no tienes favoritos', 'text' => 'Explora el mapa y guarda los lugares que te inspiren.']) ?>
      <p class="flex center mt-4"><a class="btn btn-primary" href="<?= e(url('/turismo')) ?>"><?= icon('compass') ?> Explorar destinos</a></p></div>
  </div>
</section>
