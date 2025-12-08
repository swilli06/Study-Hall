document.addEventListener('DOMContentLoaded', () => {
  const chatContainer = document.getElementById('chat-container');
  const chatForm = document.getElementById('chat-form');
  const chatInput = document.getElementById('chat-input');
  const chatWithId = parseInt(chatContainer?.dataset.chatWithId || '0', 10);

  function appendMessage(text, outgoing = true) {
    if (!chatContainer) return;

    const div = document.createElement('div');
    div.classList.add('message', outgoing ? 'outgoing' : 'incoming');
    div.textContent = text;

    const time = document.createElement('div');
    time.classList.add('message-time');
    const now = new Date();
    time.textContent = `${now.getHours()}:${String(now.getMinutes()).padStart(2, '0')}`;
    div.appendChild(time);

    const wrapper = document.createElement('div');
    wrapper.classList.add('d-flex');
    if (outgoing) wrapper.classList.add('justify-content-end');
    wrapper.appendChild(div);

    chatContainer.appendChild(wrapper);
    chatContainer.scrollTop = chatContainer.scrollHeight;
  }

  if (chatForm && chatInput && chatWithId) {
    chatForm.addEventListener('submit', async e => {
      e.preventDefault();
      const message = chatInput.value.trim();
      if (!message) return;

      appendMessage(message, true);
      chatInput.value = '';

      await fetch('/chat/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ toUserId: chatWithId, message })
      });
    });
  }
});
