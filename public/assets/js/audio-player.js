const STORAGE_KEY = 'campus-audio-enabled';
const FADE_MS = 650;

const isEnabled = () => {
  const saved = window.localStorage.getItem(STORAGE_KEY);
  if (saved === null) {
    return true;
  }
  return saved === 'true';
};

const setEnabled = (value) => {
  window.localStorage.setItem(STORAGE_KEY, String(value));
};

const initAudioPlayer = () => {
  const body = document.body;
  const src = body?.dataset?.music?.trim();
  const toggle = document.getElementById('audio-toggle');

  if (!src || !toggle) {
    return;
  }

  let audio = window.campusBgm;
  if (!audio) {
    audio = new Audio(src);
    audio.loop = true;
    audio.preload = 'auto';
    window.campusBgm = audio;
    audio.volume = 0;
  }

  const updateButton = (enabled) => {
    toggle.setAttribute('aria-pressed', String(enabled));
    toggle.textContent = enabled ? 'Son: ON' : 'Son: OFF';
  };

  let enabled = isEnabled();
  updateButton(enabled);

  const fadeTo = (target) => {
    const start = audio.volume;
    const delta = target - start;
    if (delta === 0) {
      return;
    }
    const startTime = performance.now();
    const step = (now) => {
      const t = Math.min(1, (now - startTime) / FADE_MS);
      audio.volume = Math.max(0, Math.min(1, start + delta * t));
      if (t < 1) {
        requestAnimationFrame(step);
      }
    };
    requestAnimationFrame(step);
  };

  const tryPlay = () => {
    if (!enabled) {
      fadeTo(0);
      audio.pause();
      return;
    }
    audio.play()
      .then(() => fadeTo(1))
      .catch(() => {
        // Autoplay can be blocked; user interaction will unlock.
      });
  };

  tryPlay();

  if (toggle.dataset.audioBound !== 'true') {
    toggle.addEventListener('click', () => {
      enabled = !enabled;
      setEnabled(enabled);
      updateButton(enabled);
      tryPlay();
    });
    toggle.dataset.audioBound = 'true';
  }

  window.addEventListener('pagehide', () => {
    if (!audio.paused) {
      fadeTo(0);
    }
  });
};

window.initAudioPlayer = initAudioPlayer;
document.addEventListener('DOMContentLoaded', initAudioPlayer);
document.addEventListener('turbo:load', initAudioPlayer);
