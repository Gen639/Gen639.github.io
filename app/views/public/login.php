<?php
$activePage = 'login';
$pageTitle = 'Login | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="css/login.css" />';
$oldEmail = $old['email'] ?? $rememberedEmail ?? '';
$redirectUrl = $redirectUrl ?? '';
$redirectDelay = (int) ($redirectDelay ?? 0);
if ($redirectUrl !== '' && $redirectDelay > 0) {
    $pageHeadExtras .= '<meta http-equiv="refresh" content="' . e((string) ($redirectDelay / 1000)) . ';url=' . e($redirectUrl) . '" />';
}
// Login form view; authentication decisions happen in AuthController.
include root_path('includes/header.php');
?>
<main class="login-page<?= $redirectUrl !== '' ? ' login-success-page' : '' ?>">
  <h1>Login</h1>
  <div class="login-intro">
    <p>Welcome back to JingleWorks! Log in to access your account and manage your projects.</p>
  </div>

  <?php if (!empty($errors)) : ?>
    <div class="form-messages error">
      <ul>
        <?php foreach ($errors as $error) : ?>
          <li><?= e($error) ?></li>
        <?php endforeach ?>
      </ul>
    </div>
  <?php endif ?>

  <?php if (!empty($successMessage)) : ?>
    <div class="form-messages success">
      <p><?= e($successMessage) ?></p>
    </div>
  <?php endif ?>

  <?php if ($redirectUrl === '') : ?>
    <form method="POST" action="index.php?page=login-submit">
      <fieldset>
        <legend>Account Credentials</legend>
        <label>
          Email Address:
          <input type="email" name="email" placeholder="your.email@example.com" value="<?= e($oldEmail) ?>" required />
        </label>

        <label>
          Password:
          <input type="password" name="password" placeholder="Enter your password" required />
        </label>

        <div class="remember-forgot">
          <label style="margin-bottom: 0; font-weight: normal;">
            <input type="checkbox" name="remember" />
            Remember me
          </label>
          <a href="#">Forgot password?</a>
        </div>
      </fieldset>

      <button type="submit" class="form-button">Login</button>
    </form>

    <div class="auth-link">
      Don't have an account? <a href="index.php?page=register">Create one here</a>
    </div>
  <?php endif ?>
</main>
<?php
$footerExtra = '';
include root_path('includes/footer.php');
?>
