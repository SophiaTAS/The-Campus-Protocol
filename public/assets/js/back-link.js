const initBackLink = () => {
  const button = document.getElementById('back-link');
  if (!button) {
    return;
  }

  button.addEventListener('click', () => {
    if (window.history.length > 1) {
      window.history.back();
      return;
    }
    window.location.href = '/';
  });
};

document.addEventListener('DOMContentLoaded', initBackLink);
