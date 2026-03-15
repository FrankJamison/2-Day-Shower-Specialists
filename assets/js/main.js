// Minimal first-party JS only

(function () {
  const yearEl = document.getElementById('y');
  if (yearEl) yearEl.textContent = String(new Date().getFullYear());

  const statusEl = document.getElementById('formStatus');
  if (statusEl) {
    const params = new URLSearchParams(window.location.search);
    const submitted = params.get('submitted');
    if (submitted === '1') {
      statusEl.hidden = false;
      statusEl.textContent = "Thanks — we got your request. We'll contact you shortly to schedule your consultation.";
    } else if (submitted === '0') {
      statusEl.hidden = false;
      statusEl.textContent = "Something went wrong — please double-check the form and try again.";
    }
  }
})();
