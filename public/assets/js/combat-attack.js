const initCombatAttack = () => {
  const container = document.getElementById('combat-live');
  if (!container) {
    return;
  }
  const stateUrl = container.dataset.stateUrl;
  const aiUrl = container.dataset.aiUrl;

  const setButtonsWaiting = (waiting, message = 'En attente...') => {
    const buttons = container.querySelectorAll('button.btn-attaque');
    buttons.forEach((btn) => {
      if (!btn.dataset.label) {
        btn.dataset.label = btn.textContent;
      }
      if (waiting) {
        btn.disabled = true;
        btn.classList.add('is-waiting');
        btn.innerHTML = `<span class="btn-spinner" aria-hidden="true"></span><span>${message}</span>`;
      } else {
        btn.disabled = false;
        btn.classList.remove('is-waiting');
        btn.textContent = btn.dataset.label;
      }
    });
  };

  const syncButtonsToTurn = () => {
    const finished = container.querySelector('.combat-finished');
    if (finished) {
      setButtonsWaiting(true, 'Combat terminé');
      return;
    }
    const zone = container.querySelector('.combat-zone');
    const turn = zone ? zone.dataset.turn : null;
    if (turn && turn !== 'joueur') {
      setButtonsWaiting(true, 'En attente...');
      return;
    }
    setButtonsWaiting(false);
  };

  window.syncCombatButtons = syncButtonsToTurn;

  const handleAttackClick = (button) => {
    const form = button.form;
    if (!form || !form.classList.contains('attaque-form')) {
      return;
    }

    const formData = new FormData(form);
    if (button.name) {
      formData.set(button.name, button.value);
    }
    const originalLabel = button.textContent;
    const url = form.getAttribute('action');

    setButtonsWaiting(true, 'En attente...');

    const focusLatestLog = () => {
      const log = container.querySelector('.combat-log-body');
      if (!log) {
        return;
      }
      const last = log.lastElementChild;
      if (last) {
        last.scrollIntoView({ block: 'end' });
      } else {
        log.scrollTop = log.scrollHeight;
      }
    };

    const maybeRedirectToResult = () => {
      const marker = container.querySelector('.combat-finished');
      if (marker && marker.dataset.resultUrl) {
        window.location.href = marker.dataset.resultUrl;
      }
    };

    const clearWaitingState = () => {
      syncButtonsToTurn();
    };

    const scheduleAiTurn = () => {
      if (!aiUrl) {
        clearWaitingState();
        return;
      }
      const finished = container.querySelector('.combat-finished');
      if (finished) {
        clearWaitingState();
        return;
      }
      window.setTimeout(() => {
        fetch(aiUrl, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
          .then((response) => (response.ok ? response.text() : null))
          .then((html) => {
            if (html) {
              container.innerHTML = html;
              focusLatestLog();
              maybeRedirectToResult();
              syncButtonsToTurn();
            }
          })
          .catch(() => {
            clearWaitingState();
          });
      }, 700);
    };

    fetch(url, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new URLSearchParams(formData),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error('Attack failed');
        }
        return response.text();
      })
      .then((html) => {
        if (html) {
          container.innerHTML = html;
          focusLatestLog();
          maybeRedirectToResult();
          syncButtonsToTurn();
          scheduleAiTurn();
          return;
        }
        if (!stateUrl) {
          return;
        }
        return fetch(stateUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then((stateResponse) => (stateResponse.ok ? stateResponse.text() : null))
          .then((stateHtml) => {
            if (stateHtml) {
              container.innerHTML = stateHtml;
              focusLatestLog();
              maybeRedirectToResult();
              syncButtonsToTurn();
              scheduleAiTurn();
            }
          });
      })
      .catch(() => {
        clearWaitingState();
      });
  };

  container.addEventListener('click', (event) => {
    const button = event.target.closest('button.btn-attaque');
    if (!button) {
      return;
    }
    event.preventDefault();
    handleAttackClick(button);
  });

  container.addEventListener('submit', (event) => {
    const form = event.target;
    if (form.classList.contains('attaque-form')) {
      event.preventDefault();
    }
  });

  syncButtonsToTurn();
};

document.addEventListener('DOMContentLoaded', initCombatAttack);
