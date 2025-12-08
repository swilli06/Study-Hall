function toggleTagField() {
  const sel = document.querySelector('select[name="type"]');
  const tag = document.getElementById('tagField');
  if (!sel || !tag) return;
  tag.disabled = sel.value !== 'posts';
}

document.addEventListener('DOMContentLoaded', toggleTagField);
