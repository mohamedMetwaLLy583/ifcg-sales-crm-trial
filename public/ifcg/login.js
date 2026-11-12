(() => {
  const password = document.getElementById('password');
  const toggle = document.getElementById('password-toggle');
  if (password && toggle) {
    toggle.hidden = false;
    toggle.addEventListener('click', () => {
      const show = password.type === 'password';
      password.type = show ? 'text' : 'password';
      toggle.textContent = show ? toggle.dataset.hide : toggle.dataset.show;
      toggle.setAttribute('aria-pressed', String(show));
    });
  }
  const language = document.getElementById('lang');
  if (language) {
    language.addEventListener('change', () => language.form.requestSubmit());
    language.form.querySelector('button').hidden = true;
  }
})();
