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
    initNotificationPolling();
});

// Expose for other pages (e.g., messages) that need to refresh the unread badge.
window.checkUnreadMessages = checkUnreadMessages;
