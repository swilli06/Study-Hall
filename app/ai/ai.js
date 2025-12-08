document.addEventListener('DOMContentLoaded', () => {
  const askBtn       = document.getElementById('askAiBtn');
  const questionEl   = document.getElementById('aiQuestion');
  const responseBox  = document.getElementById('aiResponseBox');
  const responseEl   = document.getElementById('aiResponse');

  if (!askBtn || !questionEl || !responseEl) return;

  const postText = window.postBodyForAI || '';

  askBtn.addEventListener('click', async () => {
    const q = questionEl.value.trim();
    if (!q) return;

    const originalHtml = askBtn.innerHTML;
    askBtn.disabled = true;
    askBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Thinking...`;

    try {
      const res = await fetch('/ai/aiCommentResponse.php', {
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
        (data && data.choices && data.choices[0]?.message?.content) ||
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
