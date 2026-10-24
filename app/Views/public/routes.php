<?php use App\Core\View; ?>
<section class="page-hero" style="--c: var(--turquesa); padding-bottom: 90px">
  <div class="container">
    <span class="eyebrow"><?= icon('route') ?> Rutas turísticas</span>
    <h1>Itinerarios para <em>recorrer Colombia</em></h1>
    <p class="lead">Recorridos sugeridos con varias paradas. Úsalos como inspiración o arma el tuyo.</p>
    <a class="btn btn-primary mt-4" href="<?= e(url('/mi-ruta')) ?>"><?= icon('route') ?> Arma tu propia ruta</a>
  </div>
  <?= View::partial('wave') ?>
</section>
<section class="section" style="padding-top: 20px">
  <div class="container">
    <?php if ($routes): ?>
      <div class="grid grid-3"><?php foreach ($routes as $i => $r): ?><?= View::partial('route-card', ['r' => $r, 'i' => $i % 3]) ?><?php endforeach; ?></div>
    <?php else: ?><?= View::partial('empty', ['icon' => 'route', 'title' => 'Aún no hay rutas publicadas']) ?><?php endif; ?>
  </div>
</section>
