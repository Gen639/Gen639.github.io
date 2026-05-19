document.addEventListener("DOMContentLoaded", () => {
  // Shared mobile navigation toggle used by every page header.
  const hamburger = document.getElementById("hamburger");
  const navLinks = document.getElementById("navLinks");

  if (hamburger && navLinks) {
    hamburger.addEventListener("click", () => {
      navLinks.classList.toggle("show");
    });
  }

  let currentSlide = 0;
  const track = document.querySelector(".carousel-track");
  const slides = document.querySelectorAll(".carousel-item");

  if (track && slides.length > 0) {
    // The home carousel advances by full-slide widths and wraps forever.
    function moveToSlide(index) {
      currentSlide = index % slides.length;
      track.style.transform = `translateX(-${currentSlide * 100}%)`;
    }

    setInterval(() => {
      moveToSlide(currentSlide + 1);
    }, 10000);

    track.addEventListener("click", () => {
      moveToSlide(currentSlide + 1);
    });
  }

  function randomlyHighlightUseCases() {
    const boxes = document.querySelectorAll(".usecase-box");
    if (boxes.length === 0) {
      return;
    }

    // Only one use-case badge is highlighted at a time.
    let previous;

    setInterval(() => {
      if (previous) previous.classList.remove("active");

      const randomIndex = Math.floor(Math.random() * boxes.length);
      boxes[randomIndex].classList.add("active");
      previous = boxes[randomIndex];
    }, 1000);
  }

  randomlyHighlightUseCases();
});
