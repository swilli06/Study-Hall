document.addEventListener('DOMContentLoaded', () => {
  // --- Tab toggle logic ---
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      // header styles
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const targetId = btn.dataset.target;

      document.querySelectorAll('.swap-panel').forEach(panel => {
        if (panel.id === targetId) {
          panel.classList.remove('d-none');
          // force reflow so animation restarts
          void panel.offsetWidth;
          panel.classList.add('active');
        } else {
          panel.classList.remove('active');
          panel.classList.add('d-none');
        }
      });
    });
  });

  // --- AI call wiring ---
  const askBtn      = document.getElementById('askAiBtn');
  const questionEl  = document.getElementById('aiQuestion');
  const responseBox = document.getElementById('aiResponseBox');
  const responseEl  = document.getElementById('aiResponse');

  // If any of the key elements are missing, bail quietly
  if (!askBtn || !questionEl || !responseBox || !responseEl) {
    return;
  }

  const postText = window.postBodyForAI || '';

  askBtn.addEventListener('click', async () => {
    const q = questionEl.value.trim();
    if (!q) return;

    const originalHtml = askBtn.innerHTML;
    askBtn.disabled = true;
    askBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Thinking...`;

    try {
      const res = await fetch('/ai/comment-response', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          event: 'userChat',
          question: q,
          post: postText
        })
      });

      const data = await res.json();

      const text =
        data?.choices?.[0]?.message?.content ||
        data.reply ||
        data.error ||
        'No reply.';

      responseEl.textContent = text;
      responseBox.classList.remove('d-none');
    } catch (err) {
      console.error('AI error:', err);
      responseEl.textContent = 'Error contacting AI.';
      responseBox.classList.remove('d-none');
    } finally {
      askBtn.disabled = false;
      askBtn.innerHTML = originalHtml;
    }
  });
});
