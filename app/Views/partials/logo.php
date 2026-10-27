<?php
/** Isotipo: flor de ocho pétalos con los colores de los paisajes de Colombia. */
$petals = ['#2bb673', '#12b5b0', '#1d6fe0', '#7b3fe4', '#ff3f8e', '#ff4b3e', '#ff7a1a', '#ffc531'];
?>
<svg class="brand-mark" viewBox="-50 -50 100 100" aria-hidden="true">
  <?php foreach ($petals as $i => $c): ?>
    <path d="M0 -6 C 9 -16, 11 -34, 0 -46 C -11 -34, -9 -16, 0 -6 Z" fill="<?= $c ?>" transform="rotate(<?= $i * 45 - 20 ?>)"/>
  <?php endforeach; ?>
  <circle r="6" fill="#fff"/>
</svg>
