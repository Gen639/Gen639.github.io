document.addEventListener("DOMContentLoaded", () => {
  const PLAY_ICON = "\u25B6";
  const PAUSE_ICON = "\u23F8";
  const cards = document.querySelectorAll(".project-card");

  // Gallery cards behave like an accordion so only one project is expanded.
  cards.forEach((card) => {
    const btn = card.querySelector(".discover-btn");
    if (!btn) {
      return;
    }

    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const isExpanded = card.classList.contains("expanded");

      cards.forEach((c) => c.classList.remove("expanded"));

      if (!isExpanded) {
        card.classList.add("expanded");
      }
    });
  });

  const players = document.querySelectorAll(".audio-player");

  players.forEach((player) => {
    const audioSrc = player.dataset.audio;
    const audio = new Audio(audioSrc);
    const playBtn = player.querySelector(".play-button");
    const progressFill = player.querySelector(".progress-fill");
    let animationFrame;

    // requestAnimationFrame keeps the progress bar in sync while audio plays.
    function updateProgress() {
      const percent = audio.duration
        ? (audio.currentTime / audio.duration) * 100
        : 0;
      progressFill.style.width = percent + "%";
      animationFrame = requestAnimationFrame(updateProgress);
    }

    playBtn.textContent = PLAY_ICON;

    playBtn.addEventListener("click", () => {
      // Starting one track pauses any other active preview.
      players.forEach((p) => {
        if (p !== player) {
          p.audio?.pause();
          p.querySelector(".play-button").textContent = PLAY_ICON;
          cancelAnimationFrame(p._frame);
        }
      });

      if (audio.paused) {
        audio.play();
        playBtn.textContent = PAUSE_ICON;
        updateProgress();
      } else {
        audio.pause();
        playBtn.textContent = PLAY_ICON;
        cancelAnimationFrame(animationFrame);
      }
    });

    player.audio = audio;
    player._frame = animationFrame;

    audio.addEventListener("ended", () => {
      playBtn.textContent = PLAY_ICON;
      progressFill.style.width = "0%";
      cancelAnimationFrame(animationFrame);
    });
  });
});
