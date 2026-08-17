(function () {
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('#site-navigation');
  if (!toggle || !nav) return;
  toggle.addEventListener('click', function () {
    const open = toggle.getAttribute('aria-expanded') === 'true';
    toggle.setAttribute('aria-expanded', String(!open));
    nav.classList.toggle('is-open', !open);
  });
}());
