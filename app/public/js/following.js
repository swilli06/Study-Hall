function filterFollowing() {
  const input = document.getElementById('searchInput');
  if (!input) return;
  const filter = input.value.toLowerCase();
  const cards = document.querySelectorAll('.follower-card');

  cards.forEach(card => {
    const username = card.querySelector('.follower-username')?.textContent.toLowerCase() || '';
    const match = username.includes(filter);
    card.parentElement.style.display = match ? '' : 'none';
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('searchInput');
  if (!input) return;
  input.addEventListener('input', filterFollowing);
  filterFollowing();
});
