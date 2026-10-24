<?php
use App\Core\View;
$imgs = array_values(array_filter(array_map(static fn ($e) => $e['cover'], $items)));
$ts = strtotime($month . '-01');
$prev = date('Y-m', strtotime('-1 month', $ts));
$next = date('Y-m', strtotime('+1 month', $ts));
$firstDow = ((int) date('N', $ts)) - 1;
$days = (int) date('t', $ts);
$byDay = [];
foreach ($monthEvents as $ev) {
    $s = max(strtotime((string) $ev['event_start']), $ts);
    $end = min(strtotime((string) ($ev['event_end'] ?: $ev['event_start'])), strtotime(date('Y-m-t 23:59:59', $ts)));
    for ($d = strtotime(date('Y-m-d', $s)); $d <= $end; $d += 86400) {
        $byDay[(int) date('j', $d)][] = $ev;
    }
}
$q = $_GET;
?>
<?= View::partial('page-hero', ['info' => $info, 'title' => 'Fiestas, música y', 'titleAccent' => 'saberes vivos', 'images' => [$info['cover'] ?? null, $imgs[2] ?? null], 'sticker' => [(string) count($upcoming), 'próximas fiestas']]) ?>

<section class="section" id="calendario" style="--c: <?= e($info['def']['color']) ?>; padding-top: 10px">
  <div class="container">
    <div class="section-head reveal"><div><span class="eyebrow">Calendario cultural</span><h2 class="title-xl">¿Qué se celebra este mes?</h2><p class="lead">Fechas de referencia: confirma siempre la programación con los organizadores.</p></div></div>
    <div class="calendar-wrap">
      <div class="calendar reveal">
        <div class="calendar-head">
          <a class="btn btn-outline btn-icon btn-sm" href="?<?= e(http_build_query(['mes' => $prev] + $q)) ?>#calendario" aria-label="Mes anterior"><?= icon('chevron-left') ?></a>
          <h3><?= e(MESES[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts)) ?></h3>
          <a class="btn btn-outline btn-icon btn-sm" href="?<?= e(http_build_query(['mes' => $next] + $q)) ?>#calendario" aria-label="Mes siguiente"><?= icon('chevron-right') ?></a>
        </div>
        <div class="cal-grid" role="grid">
          <?php foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $d): ?><div class="cal-dow"><?= $d ?></div><?php endforeach; ?>
          <?php for ($k = 0; $k < $firstDow; $k++): ?><div class="cal-day is-out"></div><?php endfor; ?>
          <?php for ($d = 1; $d <= $days; $d++): $isToday = date('Y-m-d') === sprintf('%s-%02d', $month, $d); ?>
            <div class="cal-day <?= $isToday ? 'is-today' : '' ?>"><b><?= $d ?></b>
              <?php foreach (array_slice($byDay[$d] ?? [], 0, 2) as $ev): ?><a class="cal-ev" href="<?= e($ev['url']) ?>" title="<?= e($ev['title']) ?>"><?= e($ev['title']) ?></a><?php endforeach; ?>
            </div>
          <?php endfor; ?>
        </div>
      </div>
      <div class="reveal" style="display:grid;gap:12px;align-content:start">
        <h3 class="title-md">Próximamente</h3>
        <?php foreach ($upcoming as $ev): $t = strtotime((string) $ev['event_start']); ?>
          <article class="event-card" style="--c: var(--rosa)">
            <div class="date-tile"><b><?= date('j', $t) ?></b><span><?= e(mb_substr(MESES[(int) date('n', $t) - 1], 0, 3)) ?></span></div>
            <div><h3><a href="<?= e($ev['url']) ?>"><?= e($ev['title']) ?></a></h3><small><?= e($ev['municipality_name'] ?? $ev['department_name'] ?? 'Todo el país') ?></small></div>
          </article>
        <?php endforeach; ?>
        <?php if (!$upcoming): ?><p class="muted">No hay eventos próximos publicados.</p><?php endif; ?>
      </div>
    </div>
  </div>
</section>

<section class="section bg-arena" style="--c: <?= e($info['def']['color']) ?>">
  <div class="container">
    <div class="section-head reveal"><div><span class="eyebrow">Expresiones culturales</span><h2 class="title-xl">Historias de las comunidades</h2></div></div>
    <?= View::partial('filters', compact('filters', 'categories', 'departments', 'municipalities', 'regions') + ['placeholder' => 'Busca una fiesta, ritmo, artesanía…']) ?>
    <?= View::partial('results-info', compact('total', 'filters')) ?>
    <?php if ($items): ?>
      <div class="grid grid-3"><?php foreach ($items as $i => $e): ?><?= View::partial('card', ['e' => $e, 'reveal' => true, 'i' => $i % 3]) ?><?php endforeach; ?></div>
      <?= View::partial('pagination', compact('page', 'pages')) ?>
    <?php else: ?>
      <?= View::partial('empty', ['icon' => 'mask']) ?>
    <?php endif; ?>
  </div>
</section>
