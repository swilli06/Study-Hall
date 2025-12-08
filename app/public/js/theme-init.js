document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    
    // Check 1: If the button element is missing, stop here.
    if (!themeToggle) {
        console.error("Theme Toggle Button (id='themeToggle') not found.");
        return; 
    }
    
    const themeKey = 'data-bs-theme';
    
    // Define classes for Light Mode
    const lightModeClasses = ['bg-black', 'text-white', 'border-black'];
    // Define classes for Dark Mode
    const darkModeClasses = ['bg-white', 'text-black', 'border-white'];

    function setTheme(theme) {
        document.documentElement.setAttribute(themeKey, theme);
        localStorage.setItem(themeKey, theme);

        if (theme === 'dark') {
            themeToggle.classList.remove(...lightModeClasses);
            themeToggle.classList.add(...darkModeClasses);
            
            // CRITICAL CHECK: Only update the icon if the element exists
            if (themeIcon) {
                themeIcon.classList.remove('bi-moon-stars'); 
                themeIcon.classList.add('bi-brightness-high'); 
            }
        } else {
            themeToggle.classList.remove(...darkModeClasses);
            themeToggle.classList.add(...lightModeClasses);
            
            // CRITICAL CHECK: Only update the icon if the element exists
            if (themeIcon) {
                themeIcon.classList.remove('bi-brightness-high');
                themeIcon.classList.add('bi-moon-stars'); 
            }
        }
    }

    // Initialize button appearance based on the theme
    const initialTheme = document.documentElement.getAttribute(themeKey);
    setTheme(initialTheme);
    
    // Event Listener for Toggle Button
    themeToggle.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute(themeKey);
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);
    });
});