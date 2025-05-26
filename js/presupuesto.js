document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("quoteForm");
  const productSelect = document.getElementById("productSelect");
  const deliveryDays = document.getElementById("deliveryDays");
  const extras = document.querySelectorAll(".extra");
  const totalEstimate = document.getElementById("totalEstimate");

  const hamburger = document.getElementById("hamburger");
  const navLinks = document.getElementById("navLinks");

  hamburger.addEventListener("click", () => {
    navLinks.classList.toggle("show");
  });
  function calculateTotal() {
    let basePrice = parseFloat(productSelect.selectedOptions[0].dataset.price);
    let extraTotal = 0;

    extras.forEach((extra) => {
      if (extra.checked) {
        extraTotal += parseFloat(extra.value);
      }
    });

    let total = basePrice + extraTotal;

    const days = parseInt(deliveryDays.value);
    if (!isNaN(days) && days > 15) {
      total *= 0.9;
    }

    totalEstimate.value = `€${total.toFixed(2)}`;
  }

  productSelect.addEventListener("change", calculateTotal);
  deliveryDays.addEventListener("input", calculateTotal);
  extras.forEach((checkbox) =>
    checkbox.addEventListener("change", calculateTotal)
  );

  // Validate contact fields on form submit
  form.addEventListener("submit", (e) => {
    const name = form.name.value.trim();
    const surname = form.surname.value.trim();
    const phone = form.phone.value.trim();
    const email = form.email.value.trim();
    const termsAccepted = document.getElementById("acceptTerms").checked;

    const nameRegex = /^[a-zA-Z]{1,15}$/;
    const surnameRegex = /^[a-zA-Z\s]{1,40}$/;
    const phoneRegex = /^[0-9]{9}$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!nameRegex.test(name)) {
      alert("Please enter a valid name (letters only, max 15 characters).");
      e.preventDefault();
      return;
    }

    if (!surnameRegex.test(surname)) {
      alert("Please enter a valid surname (letters only, max 40 characters).");
      e.preventDefault();
      return;
    }

    if (!phoneRegex.test(phone)) {
      alert("Please enter a valid phone number (9 digits).");
      e.preventDefault();
      return;
    }

    if (!emailRegex.test(email)) {
      alert("Please enter a valid email address.");
      e.preventDefault();
      return;
    }

    if (!termsAccepted) {
      alert("You must accept the terms and privacy policy.");
      e.preventDefault();
      return;
    }

    alert("Form submitted successfully!"); // Replace with actual handling logic
  });

  // Initial calculation
  calculateTotal();
});
