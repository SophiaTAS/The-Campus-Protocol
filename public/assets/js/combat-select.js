const initCombatSelect = () => {
  const creatureCards = document.querySelectorAll('#combat-creatures .combat-card');
  const arenaCards = document.querySelectorAll('#combat-arenes .arena-card');
  const inputCreature = document.getElementById('input-creature-id');
  const inputArene = document.getElementById('input-arene-id');
  const submit = document.getElementById('combat-submit');

  if (!inputCreature || !inputArene || !submit) {
    return;
  }

  const updateSubmit = () => {
    submit.disabled = !(inputCreature.value && inputArene.value);
  };

  creatureCards.forEach((card) => {
    card.addEventListener('click', () => {
      creatureCards.forEach((c) => c.classList.remove('selected'));
      card.classList.add('selected');
      inputCreature.value = card.dataset.id || '';
      updateSubmit();
    });
  });

  arenaCards.forEach((card) => {
    card.addEventListener('click', () => {
      arenaCards.forEach((a) => a.classList.remove('selected'));
      card.classList.add('selected');
      inputArene.value = card.dataset.id || '';
      updateSubmit();
    });
  });

  updateSubmit();
};

document.addEventListener('DOMContentLoaded', initCombatSelect);
