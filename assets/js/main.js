// Minimal first-party JS only

(function () {
  const yearEl = document.getElementById('y');
  if (yearEl) yearEl.textContent = String(new Date().getFullYear());
})();
