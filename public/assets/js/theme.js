/* Aplica el tema guardado antes de pintar la página (evita el destello de color). */
(function () {
  try {
    var t = JSON.parse(localStorage.getItem('vpc:tema') || 'null');
    if (t === 'dark' || t === 'light') document.documentElement.setAttribute('data-theme', t);
  } catch (e) { /* sin almacenamiento */ }
})();
