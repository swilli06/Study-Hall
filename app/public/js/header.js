function updateIcon(theme, icon) {
    if (!icon) return;
    icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
}

function setTheme(theme, icon) {
    const root = document.documentElement;
    root.setAttribute('data-bs-theme', theme);
    localStorage.setItem('theme', theme);
    document.cookie = 'theme=' + theme + '; path=/; max-age=31536000';
    updateIcon(theme, icon);
}

function initThemeToggle() {
    const btn = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');
    const cookieTheme = document.cookie.match(/(?:^|;\s*)theme=(light|dark)/)?.[1];
    const storedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initialTheme = cookieTheme || storedTheme || (prefersDark ? 'dark' : 'light');

    setTheme(initialTheme, icon);

    if (btn) {
        btn.addEventListener('click', () => {
            const nextTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            setTheme(nextTheme, icon);
        });
    }

    try {
        if (!storedTheme && window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                setTheme(e.matches ? 'dark' : 'light', icon);
            });
        }
    } catch (_) {
        /* no-op */
    }
}

async function checkUnreadMessages() {
    try {
        const response = await fetch('/messages/unread-count');
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();
        const indicator = document.querySelector('.notification-bell .notification-indicator');
        if (!indicator) return data?.unreadCount ?? 0;
        indicator.style.display = data.unreadCount > 0 ? 'block' : 'none';
        return data.unreadCount ?? 0;
    } catch (error) {
        console.error('Error checking unread messages:', error);
        return 0;
    }
}

function initNotificationPolling() {
    const indicator = document.querySelector('.notification-bell .notification-indicator');
    if (!indicator) return;
    checkUnreadMessages();
    setInterval(checkUnreadMessages, 10000);
}

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initNotificationPolling();
});

// Expose for other pages (e.g., messages) that need to refresh the unread badge.
window.checkUnreadMessages = checkUnreadMessages;
