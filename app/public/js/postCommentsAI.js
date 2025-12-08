document.querySelectorAll(".tab-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    // header styles
    document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");

    const targetId = btn.dataset.target;

    document.querySelectorAll(".swap-panel").forEach(panel => {
      if (panel.id === targetId) {
        panel.classList.remove("d-none");
        // force reflow so animation restarts
        void panel.offsetWidth;
        panel.classList.add("active");
      } else {
        panel.classList.remove("active");
        panel.classList.add("d-none");
      }
    });
  });
});

// AI call, same as before
const askBtn = document.getElementById("askAiBtn");
if (askBtn) {
  askBtn.addEventListener("click", async function () {
    const qEl = document.getElementById("aiQuestion");
    if (!qEl) return;
    const q = qEl.value.trim();
    if (!q) return;

    const btn = this;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Thinking...`;

    const box = document.getElementById("aiResponseBox");
    const out  = document.getElementById("aiResponse");

    try {
      const res = await fetch("/ai.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          event: "userChat",
          question: q,
          personality: "maid"
        })
      });

      const data = await res.json();
      let text = data?.choices?.[0]?.message?.content 
               ?? data.error 
               ?? "No reply.";
      out.textContent = text;
      box.classList.remove("d-none");
    } catch (err) {
      out.textContent = "Network error.";
      box.classList.remove("d-none");
    }

    btn.disabled = false;
    btn.innerHTML = `<i class="bi bi-robot"></i> Ask AI`;
  });
}