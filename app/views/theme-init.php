<?php /* Include this BEFORE Bootstrap CSS in <head> */ ?>
<script id="theme-init">
    (function () {
        try {
            // Key used to store the theme preference
            var themeKey = 'data-bs-theme';

            // 1. Check for saved preference in localStorage
            var storedTheme = localStorage.getItem(themeKey);

            // 2. Check for system preference
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

            // 3. Determine final theme: Stored > System Default (or 'light' if neither)
            var theme = storedTheme || (prefersDark ? 'dark' : 'light');

            // CRITICAL: Set the theme on the <html> tag immediately
            document.documentElement.setAttribute(themeKey, theme);
        } catch (_) {
            // Suppress any errors during theme initialization
        }
    })();
</script>