<?php
$activePage = 'quote';
$prefix = '../';
$pageTitle = 'Request a Quote | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="../css/presupuesto.css" />';
include __DIR__ . '/../includes/header.php';
?>

    <main class="quote-page">
      <h1>Request a Custom Quote</h1>
      <div class="quote-intro">
        <p>
          Ready to bring your brand to life with a unique sound? Fill out the
          form below to get a personalized quote. Let us know your project
          goals, timeline, and preferences — and we’ll handle the rest.
        </p>
      </div>

      <form id="quoteForm">
        <div class="form-columns">
          <div class="form-column">
            <fieldset>
              <legend>Contact Information</legend>
              <label>
                Name:
                <input type="text" name="name" maxlength="15" required />
              </label>
              <label>
                Surname:
                <input type="text" name="surname" maxlength="40" required />
              </label>
              <label>
                Phone:
                <input type="text" name="phone" maxlength="9" required />
              </label>
              <label>
                Email:
                <input type="email" name="email" required />
              </label>
              <label class="terms">
                <input type="checkbox" id="acceptTerms" required />
                I accept the terms and privacy policy.
              </label>
            </fieldset>
          </div>
          <div class="form-column">
            <fieldset>
              <legend>Project Details</legend>

              <label>
                Choose a product:
                <select id="productSelect" name="product">
                  <option value="jingle" data-price="300">
                    Advertising Jingle – €300
                  </option>
                  <option value="logo" data-price="150">
                    Sonic Logo – €150
                  </option>
                  <option value="podcast" data-price="250">
                    Podcast Package – €250
                  </option>
                </select>
              </label>
              <label>
                Delivery Deadline (in days):&nbsp;&nbsp;&nbsp;
                <input
                  type="number"
                  name="deliveryTime"
                  id="deliveryTime"
                  min="5"
                  required
                />
              </label>
              <fieldset>
                <legend>Extras:</legend>

                <label class="extra">
                  <input type="checkbox" value="50" /> Voice-over (+€50)</label
                >
                <label class="extra"
                  ><input type="checkbox" value="30" /> Express Delivery
                  (+€30)</label
                >
                <label class="extra"
                  ><input type="checkbox" value="20" /> Multiple Revisions
                  (+€20)</label
                >
              </fieldset>

              <div class="total-estimate">
                <label>Estimated Total:</label>
                <input type="text" id="totalEstimate" readonly value="€0" />
              </div>
            </fieldset>
          </div>
        </div>

        <div class="form-buttons">
          <button type="submit" id="submitBtn" disabled>Send</button>
          <button type="reset">Reset</button>
        </div>
      </form>
    </main>
    <!-- 🔚 Footer -->
<?php
$footerExtra = '<script src="../js/presupuesto.js"></script>';
include __DIR__ . '/../includes/footer.php';
?>
