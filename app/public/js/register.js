document.addEventListener('DOMContentLoaded', () => {
  const bio = document.getElementById('bio');
  const counter = document.getElementById('bioCounter');
  if (!bio || !counter) return;
  const maxLength = bio.getAttribute('maxlength') || '0';

  const updateCounter = () => {
    counter.textContent = `${bio.value.length}/${maxLength}`;
  };

  bio.addEventListener('input', updateCounter);
  updateCounter();
});
