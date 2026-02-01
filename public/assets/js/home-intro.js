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

    if (enableAudio && window.campusBgm) {
      window.campusBgm.play().catch(() => {});
    }
  };

  if (saved === 'true') {
    return;
  }

  document.body.classList.add('intro-active');
  intro.classList.add('is-visible');
  intro.setAttribute('aria-hidden', 'false');

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

  const addLine = () => {
    if (index >= lines.length) {
      prompt.classList.remove('is-hidden');
      return;
    }
    const line = document.createElement('div');
    line.className = 'intro-line';
    line.textContent = lines[index];
    linesContainer.appendChild(line);
    linesContainer.scrollTop = linesContainer.scrollHeight;
    index += 1;
    const delay = 120 + Math.floor(Math.random() * 120);
    window.setTimeout(addLine, delay);
  };

  window.setTimeout(addLine, 200);

  yesBtn.addEventListener('click', () => finishIntro(true));
  noBtn.addEventListener('click', () => finishIntro(false));
};

document.addEventListener('DOMContentLoaded', initHomeIntro);
