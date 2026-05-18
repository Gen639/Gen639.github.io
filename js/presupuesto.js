document.addEventListener("DOMContentLoaded", () => {
  const productSelect = document.getElementById("productSelect");
  const deliveryInput = document.getElementById("deliveryTime");
  const extras = document.querySelectorAll(
    "label.extra input[type='checkbox']",
  );
  const totalEstimate = document.getElementById("totalEstimate");
  const termsCheckbox = document.getElementById("acceptTerms");
  const submitBtn = document.getElementById("submitBtn");

  function updateTotal() {
    if (!productSelect || !deliveryInput || !totalEstimate) {
      return;
    }

    const selectedOption = productSelect.selectedOptions[0];
    const basePrice = Number(selectedOption?.dataset.price ?? 0);
    const extrasTotal = Array.from(extras)
      .filter((extra) => extra.checked)
      .reduce((sum, extra) => sum + Number(extra.value || 0), 0);
    const deliveryDays = Number(deliveryInput.value || 0);
    const rushFee = deliveryDays > 0 && deliveryDays < 7 ? 50 : 0;

    totalEstimate.value = `€${basePrice + extrasTotal + rushFee}`;
  }

  if (termsCheckbox && submitBtn) {
    submitBtn.disabled = !termsCheckbox.checked;
    termsCheckbox.addEventListener("change", () => {
      submitBtn.disabled = !termsCheckbox.checked;
    });
  }

  productSelect?.addEventListener("change", updateTotal);
  deliveryInput?.addEventListener("input", updateTotal);
  extras.forEach((extra) => extra.addEventListener("change", updateTotal));
  updateTotal();
});
