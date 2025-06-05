document.addEventListener("DOMContentLoaded", () => {
  //Expand logic
  // const cards = document.querySelectorAll("[data-project]");

  // cards.forEach((card) => {
  //   const btn = card.querySelector(".discover-btn");
  //   const link = card.querySelector(".lightbox-link");
  //   const summary = card.querySelector(".project-summary");
  //   const img = card.querySelector(".project-cover img");

  //   // Set image as background initially
  //   const imgSrc = img?.getAttribute("src");
  //   if (imgSrc && summary) {
  //     summary.style.backgroundImage = `url('${imgSrc}')`;
  //   }

  //   // Disable lightbox unless expanded
  //   link?.addEventListener("click", (e) => {
  //     if (!card.classList.contains("expanded")) {
  //       e.preventDefault();
  //     }
  //   });

  //   // Toggle expansion
  //   btn?.addEventListener("click", (e) => {
  //     e.stopPropagation();
  //     const isExpanded = card.classList.contains("expanded");

  //     // Collapse all
  //     cards.forEach((c) => c.classList.remove("expanded"));

  //     // Expand if not already
  //     if (!isExpanded) {
  //       card.classList.add("expanded");
  //     }
  //   });
  // });
  const cards = document.querySelectorAll("[data-project]");

  cards.forEach((card) => {
    const btn = card.querySelector(".discover-btn");
    const overlay = card.querySelector(".project-overlay");

    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const isExpanded = card.classList.contains("expanded");

      // Collapse all
      cards.forEach((c) => c.classList.remove("expanded"));

      // Toggle this one
      if (!isExpanded) {
        card.classList.add("expanded");
      }
    });
  });

  //hamburger logic
  const hamburger = document.getElementById("hamburger");
  hamburger.addEventListener("click", () => {
    navLinks.classList.toggle("show");
  });

  //Player logic
  const players = document.querySelectorAll(".audio-player");

  const navLinks = document.getElementById("navLinks");

  players.forEach((player) => {
    const audioSrc = player.dataset.audio;
    const audio = new Audio(audioSrc);
    const playBtn = player.querySelector(".play-button");
    const progressFill = player.querySelector(".progress-fill");
    const coverImage = player.closest(".project-card").querySelector("img");

    let playing = false;
    let animationFrame;

    function updateProgress() {
      const percent = (audio.currentTime / audio.duration) * 100;
      progressFill.style.width = percent + "%";

      // Apply grayscale to color effect
      coverImage.style.filter = `grayscale(${100 - percent}%)`;

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
      coverImage.style.filter = "grayscale(100%)";
      playing = false;
      cancelAnimationFrame(animationFrame);
    });
  });
});
