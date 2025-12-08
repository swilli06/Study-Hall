(function () {
  const messagesDiv = document.getElementById('messages');
  const form = document.getElementById('chat-form');
  const input = document.getElementById('chat-input');
  const renderedIds = new Set();

  const initialRendered = messagesDiv?.dataset.renderedIds;
  if (initialRendered) {
    try {
      JSON.parse(initialRendered).forEach(id => renderedIds.add(parseInt(id, 10)));
    } catch (_) {
      /* ignore parse errors */
    }
  }

  let lastId = parseInt(messagesDiv?.dataset.lastId || '0', 10);
  let conversationId = parseInt(messagesDiv?.dataset.conversationId || '0', 10);
  const loggedInUserId = parseInt(messagesDiv?.dataset.loggedInUser || '0', 10);
  const partnerName = messagesDiv?.dataset.partnerName || '';

  function formatServerTime(ts) {
    if (!ts) return '';
    let iso = ts;
    if (/^\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}$/.test(ts)) {
      iso = ts.replace(' ', 'T') + 'Z';
    }
    try {
      const d = new Date(iso);
      return d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
    } catch (_) {
      return ts;
    }
  }

  function appendMessage(msg) {
    if (!messagesDiv) return;
    const numericId = msg.id ? parseInt(msg.id, 10) : null;
    if (numericId && renderedIds.has(numericId)) return;

    const isSent = msg.sender_id == loggedInUserId;
    const div = document.createElement('div');
    div.className = 'message ' + (isSent ? 'sent' : 'received');
    if (numericId) {
      div.dataset.id = numericId;
      renderedIds.add(numericId);
      lastId = Math.max(lastId, numericId);
    }

    const meta = document.createElement('div');
    meta.className = 'meta';
    const name = document.createElement('strong');
    name.textContent = isSent ? 'You' : (msg.sender_name || partnerName || '');
    const time = document.createElement('small');
    time.className = 'message-time';
    if (msg.created_at) {
      time.dataset.createdAt = msg.created_at;
      time.textContent = formatServerTime(msg.created_at);
    } else {
      time.textContent = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
    }
    meta.appendChild(name);
    meta.appendChild(time);

    const body = document.createElement('div');
    body.className = 'body';
    body.textContent = msg.body || '';

    div.appendChild(meta);
    div.appendChild(body);
    messagesDiv.appendChild(div);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
  }

  function updateMessageTimesOnLoad() {
    document.querySelectorAll('.message-time[data-created-at]').forEach(el => {
      const ts = el.dataset.createdAt;
      if (ts) el.textContent = formatServerTime(ts);
    });
  }

  let isPolling = false;
  let pollTimeout = null;

  async function poll() {
    if (!conversationId || !isPolling) return;
    try {
      const res = await fetch(`/messages/poll?conversation_id=${conversationId}&last_id=${lastId}`);
      const data = await res.json();
      if (Array.isArray(data)) {
        data.forEach(msg => {
          if (msg.id > lastId) lastId = msg.id;
          const formattedMsg = {
            ...msg,
            sender_name: msg.sender_id == loggedInUserId ? 'You' : (msg.sender_name || 'User')
          };
          appendMessage(formattedMsg);
        });
        if (data.some(msg => msg.sender_id !== loggedInUserId) && typeof checkUnreadMessages === 'function') {
          checkUnreadMessages();
        }
      }
    } catch (err) {
      console.error('Poll error', err);
    }

    if (isPolling) {
      pollTimeout = setTimeout(poll, 3000);
    }
  }

  function startPolling() {
    if (!conversationId) return;
    isPolling = true;
    poll();
  }

  async function handleSend(e) {
    e.preventDefault();
    if (!input) return;
    const body = input.value.trim();
    if (!body) return;

    const submitButton = form.querySelector('button[type=\"submit\"]');
    const formData = new FormData(form);

    input.disabled = true;
    if (submitButton) submitButton.disabled = true;

    try {
      const res = await fetch('/messages/send', { method: 'POST', body: formData });
      if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
      const data = await res.json();
      if (!data || !data.success) {
        console.error('Send failed', data);
        alert(data && data.error ? data.error : 'Failed to send message');
      } else {
        appendMessage({
          sender_id: loggedInUserId,
          sender_name: 'You',
          body,
          id: data.id,
          created_at: data.created_at || new Date().toISOString()
        });
        if (data.conversation_id && !conversationId) {
          conversationId = data.conversation_id;
          startPolling();
        }
        if (typeof checkUnreadMessages === 'function') checkUnreadMessages();
      }
    } catch (err) {
      console.error('Network error sending message:', err);
      alert('Unable to send message. Please check your connection and try again.');
    } finally {
      input.disabled = false;
      if (submitButton) submitButton.disabled = false;
      input.value = '';
      input.focus();
    }
  }

  function initForm() {
    if (!form || !input) return;
    form.addEventListener('submit', handleSend);
  }

  function initSearchFilter() {
    const searchInput = document.getElementById('conversation-search');
    if (!searchInput) return;
    const sidebarContent = document.querySelector('.sidebar .sidebar-content');
    if (!sidebarContent) return;

    const searchWrapper = sidebarContent.querySelector('div.p-2');
    const divider = sidebarContent.querySelector('.sidebar-divider');
    const originalOrder = Array.from(sidebarContent.querySelectorAll('.conversation')).map(el => ({
      userId: el.dataset.userId || '',
      convId: el.dataset.conversationId || ''
    }));
    const originalDividerIndex = divider ? Array.from(sidebarContent.children).indexOf(divider) : -1;

    const debounce = (fn, wait = 200) => {
      let t = null;
      return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), wait);
      };
    };

    const filterConversations = () => {
      const q = searchInput.value.trim().toLowerCase();
      const items = Array.from(sidebarContent.querySelectorAll('.conversation'));

      if (!q) {
        originalOrder.forEach(o => {
          const el = sidebarContent.querySelector(`.conversation[data-user-id=\"${o.userId}\"][data-conversation-id=\"${o.convId}\"]`);
          if (el) sidebarContent.appendChild(el);
        });
        if (divider) {
          if (originalDividerIndex >= 0 && originalDividerIndex < sidebarContent.children.length) {
            sidebarContent.insertBefore(divider, sidebarContent.children[originalDividerIndex]);
          } else {
            sidebarContent.appendChild(divider);
          }
        }
        items.forEach(item => (item.style.display = ''));
        return;
      }

      items.forEach(item => {
        const name = (item.querySelector('.conversation-info strong')?.textContent || '').toLowerCase();
        const uid = (item.dataset.userId || '').toString();
        const match = name.includes(q) || uid.includes(q);
        item.style.display = match ? '' : 'none';
      });

      const firstVisible = sidebarContent.querySelector('.conversation:not([style*=\"display: none\"])');
      if (firstVisible && searchWrapper) {
        sidebarContent.insertBefore(firstVisible, searchWrapper.nextSibling);
      }

      if (divider) {
        const anyPotentialVisible = Array.from(sidebarContent.querySelectorAll('.conversation')).some(
          i => i.style.display !== 'none' && (!i.dataset.conversationId || i.dataset.conversationId === '0')
        );
        divider.style.display = anyPotentialVisible ? '' : 'none';
      }
    };

    searchInput.addEventListener('input', debounce(filterConversations, 200));
    searchInput.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        e.preventDefault();
        filterConversations();
        const first = sidebarContent.querySelector('.conversation:not([style*=\"display: none\"])');
        if (first) {
          try {
            first.focus();
          } catch (_) {}
          first.scrollIntoView({ behavior: 'smooth', block: 'center' });
          first.classList.add('search-highlight');
          setTimeout(() => first.classList.remove('search-highlight'), 900);
        }
      }
    });
  }

  async function startConversation(el) {
    const userId = el.getAttribute('data-user-id');
    try {
      el.querySelector('.unread-dot')?.remove();
    } catch (_) {
      /* ignore */
    }
    if (typeof checkUnreadMessages === 'function') checkUnreadMessages();

    try {
      const res = await fetch(`/messages/getOrCreate?partner_id=${userId}`);
      if (!res.ok) throw new Error('Network response was not ok');
      const data = await res.json();
      if (data && data.conversation_id) {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('conversation', data.conversation_id);
        window.location.href = currentUrl.toString();
      } else {
        console.error('Invalid response from server', data);
        alert('Could not start conversation');
      }
    } catch (err) {
      console.error('Error starting conversation', err);
      alert('Error starting conversation');
    }
  }

  window.startConversation = startConversation;

  document.addEventListener('DOMContentLoaded', () => {
    updateMessageTimesOnLoad();
    initForm();
    if (typeof checkUnreadMessages === 'function') checkUnreadMessages();
    startPolling();
    initSearchFilter();
  });

  window.addEventListener('beforeunload', () => {
    isPolling = false;
    if (pollTimeout) clearTimeout(pollTimeout);
  });
})();
