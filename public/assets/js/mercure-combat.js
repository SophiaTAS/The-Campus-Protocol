const initMercureCombat = () => {
  const container = document.getElementById('combat-live');
  if (!container) {
    return;
  }

  const url = container.dataset.mercureUrl;

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

  const applyHtml = (html) => {
    if (html) {
      container.innerHTML = html;
      focusLatestLog();
      maybeRedirectToResult();
      if (window.syncCombatButtons) {
        window.syncCombatButtons();
      }
    }
  };

  if (!url) {
    return;
  }

  const source = new EventSource(url);

  source.onmessage = (event) => {
    applyHtml(event?.data);
  };

  source.onerror = () => {
    source.close();
  };

  window.addEventListener('beforeunload', () => {
    source.close();
  });
};

document.addEventListener('DOMContentLoaded', () => {
  initMercureCombat();
  const container = document.getElementById('combat-live');
  if (container) {
    const log = container.querySelector('.combat-log-body');
    if (log) {
      const last = log.lastElementChild;
      if (last) {
        last.scrollIntoView({ block: 'end' });
      } else {
        log.scrollTop = log.scrollHeight;
      }
    }
  }
});
