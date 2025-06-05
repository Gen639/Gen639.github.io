document.addEventListener("DOMContentLoaded", () => {
  const cards = document.querySelectorAll("[data-project]");

  cards.forEach((card) => {
    const btn = card.querySelector(".discover-btn");

    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const isExpanded = card.classList.contains("expanded");

      cards.forEach((c) => c.classList.remove("expanded"));

      if (!isExpanded) {
        card.classList.add("expanded");
      }
    });
  });

  //hamburger logic
  const hamburger = document.getElementById("hamburger");
  const navLinks = document.getElementById("navLinks");
  hamburger.addEventListener("click", () => {
    navLinks.classList.toggle("show");
  });

  //Player logic
  const players = document.querySelectorAll(".audio-player");

  players.forEach((player) => {
    const audioSrc = player.dataset.audio;
    const audio = new Audio(audioSrc);
    const playBtn = player.querySelector(".play-button");
    const progressFill = player.querySelector(".progress-fill");

    let playing = false;
    let animationFrame;

    function updateProgress() {
      const percent = (audio.currentTime / audio.duration) * 100;
      progressFill.style.width = percent + "%";

      animationFrame = requestAnimationFrame(updateProgress);
    }

    playBtn.addEventListener("click", () => {
      // Pause other players
      players.forEach((p) => {
        if (p !== player) {
          p.audio?.pause();
          p.querySelector(".play-button").textContent = "▶";
          cancelAnimationFrame(p._frame);
        }
      });

      if (audio.paused) {
        audio.play();
        playBtn.textContent = "⏸";
        updateProgress();
      } else {
        audio.pause();
        playBtn.textContent = "▶";
        cancelAnimationFrame(animationFrame);
      }
    });

    // Store audio reference on element
    player.audio = audio;
    player._frame = animationFrame;

    // Reset on audio end
    audio.addEventListener("ended", () => {
      playBtn.textContent = "▶";
      progressFill.style.width = "0%";
      if (coverImage) {
        coverImage.style.filter = "grayscale(100%)";
      }
      playing = false;
      cancelAnimationFrame(animationFrame);
    });
  });
});
