<section class="error-page">
  <div>
    <div class="big text-gradient"><?= (int) $status ?></div>
    <h1 class="title-lg"><?= e($title) ?></h1>
    <p class="lead" style="margin-inline:auto"><?= e($message) ?></p>
    <div class="flex gap-2 center wrap mt-4">
      <a class="btn btn-primary" href="<?= e(url('/')) ?>"><?= icon('home') ?> Volver al inicio</a>
      <a class="btn btn-outline" href="<?= e(url('/turismo')) ?>"><?= icon('map') ?> Abrir el mapa</a>
    </div>
    <?php if (!empty($debug)): ?><pre><?= e($debug) ?></pre><?php endif; ?>
  </div>
</section>
