const initCombatIntro = () => {
  const modal = document.getElementById('modal-intro-combat');
  const startButton = document.getElementById('btn-start-combat');

  if (!modal || !startButton) {
    return;
  }

  startButton.addEventListener('click', () => {
    modal.style.display = 'none';
  });
};

document.addEventListener('DOMContentLoaded', initCombatIntro);
