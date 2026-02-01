const STORAGE_KEY = 'campus-audio-enabled';
const TIME_KEY = 'campus-audio-time';
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

  const audio = new Audio(src);
  audio.loop = true;
  audio.preload = 'auto';
  window.campusBgm = audio;
  audio.volume = 0;

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
    const savedTime = Number(window.localStorage.getItem(TIME_KEY));
    if (Number.isFinite(savedTime) && savedTime > 0) {
      audio.currentTime = savedTime;
    }
    audio.play()
      .then(() => fadeTo(1))
      .catch(() => {
        // Autoplay can be blocked; user interaction will unlock.
      });
  };

  tryPlay();

  toggle.addEventListener('click', () => {
    enabled = !enabled;
    setEnabled(enabled);
    updateButton(enabled);
    tryPlay();
  });

  const rememberTime = () => {
    if (!audio.paused && Number.isFinite(audio.currentTime)) {
      window.localStorage.setItem(TIME_KEY, String(audio.currentTime));
    }
  };

  window.addEventListener('pagehide', () => {
    if (!audio.paused) {
      rememberTime();
      fadeTo(0);
    }
  });

  window.addEventListener('beforeunload', rememberTime);
};

document.addEventListener('DOMContentLoaded', initAudioPlayer);
