const initHomeIntro = () => {
  const intro = document.getElementById('intro-screen');
  const content = document.getElementById('home-content');
  const linesContainer = document.getElementById('intro-lines');
  const prompt = document.getElementById('intro-prompt');
  const yesBtn = document.getElementById('intro-yes');
  const noBtn = document.getElementById('intro-no');

  if (!intro || !content || !linesContainer || !prompt || !yesBtn || !noBtn) {
    return;
  }
  if (intro.dataset.introBound === 'true') {
    return;
  }
  intro.dataset.introBound = 'true';

  const PREF_KEY = 'campus-audio-enabled';
  const INTRO_KEY = 'campus-intro-done';
  const saved = window.localStorage.getItem(INTRO_KEY);

  const finishIntro = (enableAudio) => {
    window.localStorage.setItem(PREF_KEY, String(enableAudio));
    window.localStorage.setItem(INTRO_KEY, 'true');
    document.body.classList.remove('intro-active');
    intro.classList.remove('is-visible');
    intro.setAttribute('aria-hidden', 'true');
    content.style.opacity = '';
    content.style.pointerEvents = '';
    content.style.userSelect = '';

    const toggle = document.getElementById('audio-toggle');
    if (toggle) {
      toggle.setAttribute('aria-pressed', String(enableAudio));
      toggle.textContent = enableAudio ? 'Son: ON' : 'Son: OFF';
    }

    if (enableAudio) {
      if (window.initAudioPlayer) {
        window.initAudioPlayer();
      }
      const start = Date.now();
      const tryStart = () => {
        if (window.campusBgm) {
          window.campusBgm.play().catch(() => {});
          return;
        }
        if (Date.now() - start < 2000) {
          window.setTimeout(tryStart, 100);
        }
      };
      tryStart();
    }
  };

  if (saved === 'true') {
    return;
  }

  document.body.classList.add('intro-active');
  intro.classList.add('is-visible');
  intro.setAttribute('aria-hidden', 'false');

  const startPreload = () => {
    const urls = [
      '/bestiaire',
      '/combat',
      '/assets/css/bestiaire.css',
      '/assets/js/bestiaire-tabs.js',
      '/assets/js/bestiaire-music.js',
      '/assets/css/combat.css',
      '/assets/js/combat-select.js'
    ];

    let images = [];
    let tracks = [];
    const preloadEl = document.getElementById('preload-images');
    if (preloadEl) {
      try {
        images = JSON.parse(preloadEl.textContent || '[]');
      } catch (e) {
        images = [];
      }
    }
    const trackEl = document.getElementById('preload-tracks');
    if (trackEl) {
      try {
        tracks = JSON.parse(trackEl.textContent || '[]');
      } catch (e) {
        tracks = [];
      }
    }

    const tasks = [
      ...urls.map((url) => ({ type: 'fetch', url })),
      ...images.map((url) => ({ type: 'image', url })),
      ...tracks.map((url) => ({ type: 'fetch', url }))
    ];

    const total = tasks.length || 1;
    let done = 0;

    const markDone = () => {
      done += 1;
    };

    tasks.forEach((task) => {
      if (task.type === 'image') {
        const img = new Image();
        img.onload = markDone;
        img.onerror = markDone;
        img.src = task.url;
      } else {
        fetch(task.url, { credentials: 'same-origin' })
          .catch(() => {})
          .finally(markDone);
      }
    });

    const preloadPromise = Promise.allSettled(
      tasks.map((task) => {
        if (task.type === 'image') {
          return new Promise((resolve) => {
            const img = new Image();
            img.onload = resolve;
            img.onerror = resolve;
            img.src = task.url;
          });
        }
        return fetch(task.url, { credentials: 'same-origin' }).catch(() => {});
      })
    );

    if ('caches' in window) {
      caches.open('campus-preload-v1').then((cache) => {
        cache.addAll([...urls, ...images, ...tracks]).catch(() => {});
      }).catch(() => {});
    }

    return {
      total,
      getDone: () => done,
      promise: preloadPromise,
    };
  };

  const preload = startPreload();

  const lines = [
    '[BOOT] Sync: alignement du CRT 60Hz...',
    '[WARN] Convecteur Temporel: 2,21 gigowatts...',
    '[INFO] Cartouche: soufflage manuel detecte.',
    '[WARN] Peritel: contact instable, ajustement en cours.',
    '[INFO] Commodore BASIC: token mal encode, correction heuristique.',
    '[INFO] Disquette: insertion a 37 degres (mode 3.5")',
    '[INFO] Megadrive: blast processing simule.',
    '[WARN] NES: faux contact sur port 2, nettoyage requis.',
    '[INFO] Game Boy: pile AAAAAA OK.',
    '[INFO] Amiga: Guru Meditation evite.',
    '[INFO] Vectrex: protection anti-flicker activee.',
    '[INFO] PS1: couvercle ouvert, swap trick annule.',
    '[INFO] Dreamcast: modem 56k muet.',
    '[INFO] PC 486: turbo ON.',
    '[INFO] BIOS: beep simple, RAM stable.',
    '[READY] Systeme retro-charge complet.'
  ];

  let index = 0;
  linesContainer.innerHTML = '';

  const loaderLine = document.createElement('div');
  loaderLine.className = 'intro-line';
  loaderLine.innerHTML = `
    <span class="intro-loader">
      <span class="intro-loader__dot"></span>
      <span class="intro-loader__dot"></span>
      <span class="intro-loader__dot"></span>
      <span>Chargement des modules...</span>
    </span>
  `;
  linesContainer.appendChild(loaderLine);

  const progressLine = document.createElement('div');
  progressLine.className = 'intro-line';
  linesContainer.appendChild(progressLine);

  const updateProgress = () => {
    const done = preload.getDone();
    const total = preload.total;
    const percent = Math.min(100, Math.round((done / total) * 100));
    progressLine.textContent = `[LOAD] Assets: ${percent}% (${done}/${total})`;
  };

  updateProgress();

  const addLine = () => {
    updateProgress();
    if (index >= lines.length) {
      Promise.resolve(preload.promise).then(() => {
        updateProgress();
        prompt.classList.remove('is-hidden');
      });
      return;
    }
    const line = document.createElement('div');
    line.className = 'intro-line';
    line.textContent = lines[index];
    linesContainer.insertBefore(line, progressLine);
    linesContainer.scrollTop = linesContainer.scrollHeight;
    index += 1;
    const remainingRatio = 1 - Math.min(1, preload.getDone() / preload.total);
    const delay = 180 + Math.floor(remainingRatio * 420) + Math.floor(Math.random() * 120);
    window.setTimeout(addLine, delay);
  };

  window.setTimeout(addLine, 200);

  yesBtn.addEventListener('click', () => finishIntro(true));
  noBtn.addEventListener('click', () => finishIntro(false));
};

document.addEventListener('DOMContentLoaded', initHomeIntro);
document.addEventListener('turbo:load', initHomeIntro);
