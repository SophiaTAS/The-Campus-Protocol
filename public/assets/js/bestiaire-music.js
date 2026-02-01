const initBestiaireMusic = () => {
  const panel = document.querySelector('.music-panel');
  if (!panel) {
    return;
  }

  let currentAudio = null;
  let currentButton = null;
  let currentProgress = null;
  let currentDuration = null;
  let rafId = null;

  const formatTime = (value) => {
    if (!Number.isFinite(value) || value <= 0) {
      return '--:--';
    }
    const minutes = Math.floor(value / 60);
    const seconds = Math.floor(value % 60);
    return `${minutes}:${String(seconds).padStart(2, '0')}`;
  };

  const pauseBgm = () => {
    if (window.campusBgm && !window.campusBgm.paused) {
      window.campusBgm.pause();
      window.campusBgm.dataset = window.campusBgm.dataset || {};
      window.campusBgm.dataset.wasPlaying = 'true';
    }
  };

  const resumeBgm = () => {
    if (window.campusBgm && window.campusBgm.dataset?.wasPlaying === 'true') {
      window.campusBgm.play().catch(() => {});
      window.campusBgm.dataset.wasPlaying = 'false';
    }
  };

  const updateProgress = () => {
    if (!currentAudio || !currentProgress) {
      return;
    }
    const percent = currentAudio.duration
      ? (currentAudio.currentTime / currentAudio.duration) * 100
      : 0;
    const bar = currentProgress.querySelector('.music-progress__bar');
    if (bar) {
      bar.style.width = `${Math.min(100, Math.max(0, percent))}%`;
    }
    rafId = window.requestAnimationFrame(updateProgress);
  };

  const stopCurrent = () => {
    if (currentAudio) {
      currentAudio.pause();
      currentAudio.currentTime = 0;
      currentAudio = null;
    }
    if (currentButton) {
      currentButton.classList.remove('is-playing');
      currentButton.textContent = '▶ Ecouter';
      currentButton = null;
    }
    if (currentProgress) {
      const bar = currentProgress.querySelector('.music-progress__bar');
      if (bar) {
        bar.style.width = '0%';
      }
      currentProgress = null;
    }
    if (currentDuration) {
      currentDuration.textContent = '--:--';
      currentDuration = null;
    }
    if (rafId) {
      window.cancelAnimationFrame(rafId);
      rafId = null;
    }
    resumeBgm();
  };

  panel.addEventListener('click', (event) => {
    const button = event.target.closest('.music-play');
    if (!button) {
      const progress = event.target.closest('.music-progress');
      if (progress && currentAudio && currentProgress === progress) {
        const rect = progress.getBoundingClientRect();
        const ratio = (event.clientX - rect.left) / rect.width;
        if (Number.isFinite(currentAudio.duration)) {
          currentAudio.currentTime = Math.max(0, Math.min(currentAudio.duration, currentAudio.duration * ratio));
        }
      }
      return;
    }
    const src = button.dataset.src;
    if (!src) {
      return;
    }

    if (currentButton === button) {
      stopCurrent();
      return;
    }

    stopCurrent();
    pauseBgm();
    currentAudio = new Audio(src);
    currentAudio.preload = 'auto';
    currentButton = button;
    const item = button.closest('.music-item');
    currentProgress = item ? item.querySelector('.music-progress') : null;
    currentDuration = item ? item.querySelector('.music-duration') : null;
    button.classList.add('is-playing');
    button.textContent = '⏸ Pause';

    currentAudio.addEventListener('ended', () => {
      stopCurrent();
    });

    currentAudio.addEventListener('loadedmetadata', () => {
      if (currentDuration) {
        currentDuration.textContent = formatTime(currentAudio.duration);
      }
    });

    currentAudio.play().catch(() => {
      stopCurrent();
    });

    if (rafId) {
      window.cancelAnimationFrame(rafId);
    }
    rafId = window.requestAnimationFrame(updateProgress);
  });
};

document.addEventListener('DOMContentLoaded', initBestiaireMusic);
