(function () {
  try {
    var match = document.cookie.match(/(?:^|;\s*)theme=(light|dark)/);
    var cookieTheme = match ? match[1] : null;
    var storedTheme = localStorage.getItem('theme');
    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    var theme = cookieTheme || storedTheme || (prefersDark ? 'dark' : 'light');
    document.documentElement.setAttribute('data-bs-theme', theme);
  } catch (_) {
    /* ignore init errors */
  }
})();
