const initBestiaireTabs = () => {
  const buttons = document.querySelectorAll('.tab-button');
  const tabs = document.querySelectorAll('.tab-content');

  if (!buttons.length || !tabs.length) {
    return;
  }

  buttons.forEach((button) => {
    button.addEventListener('click', () => {
      buttons.forEach((btn) => btn.classList.remove('active'));
      tabs.forEach((tab) => tab.classList.remove('active'));

      button.classList.add('active');
      const target = document.getElementById(button.dataset.tab);
      if (target) {
        target.classList.add('active');
      }
    });
  });
};

document.addEventListener('DOMContentLoaded', initBestiaireTabs);
