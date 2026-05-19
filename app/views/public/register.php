<?php
$activePage = 'register';
$pageTitle = 'Register | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="css/register.css" />';
$old = array_merge([
    'nombre' => '',
    'apellidos' => '',
    'telefono' => '',
    'fecha_nacimiento' => '',
    'direccion' => '',
    'sexo' => '',
    'email' => '',
    'usuario' => '',
], isset($old) && is_array($old) ? $old : []);
$errors = isset($errors) && is_array($errors) ? $errors : [];
$successMessage = $successMessage ?? '';
// Registration form view with sticky values supplied by AuthController.
include root_path('includes/header.php');
?>
<main class="register-page">
  <h1>Create Account</h1>
  <div class="register-intro">
    <p>Join JingleWorks and start creating amazing custom jingles for your brand. Fill out the form below to get started.</p>
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
    <div class="form-messages success"><?= e($successMessage) ?></div>
  <?php endif ?>

  <form method="POST" action="index.php?page=register-submit">
    <div class="form-columns">
      <fieldset class="form-column">
        <legend>Personal Information</legend>
        <label>First Name:<input type="text" name="nombre" maxlength="50" required value="<?= e($old['nombre']) ?>" /></label>
        <label>Last Name:<input type="text" name="apellidos" maxlength="50" required value="<?= e($old['apellidos']) ?>" /></label>
        <label>Phone Number:<input type="text" name="telefono" maxlength="20" required value="<?= e($old['telefono']) ?>" /></label>
        <label>Date of Birth:<input type="date" name="fecha_nacimiento" required value="<?= e($old['fecha_nacimiento']) ?>" /></label>
        <label>Address:<input type="text" name="direccion" maxlength="150" value="<?= e($old['direccion']) ?>" /></label>
        <label>
          Gender:
          <select name="sexo">
            <option value="">Optional</option>
            <option value="female"<?= $old['sexo'] === 'female' ? ' selected' : '' ?>>Female</option>
            <option value="male"<?= $old['sexo'] === 'male' ? ' selected' : '' ?>>Male</option>
            <option value="other"<?= $old['sexo'] === 'other' ? ' selected' : '' ?>>Other</option>
          </select>
        </label>
      </fieldset>

      <fieldset class="form-column">
        <legend>Account Details</legend>
        <label>Username:<input type="text" name="usuario" maxlength="50" required value="<?= e($old['usuario']) ?>" /></label>
        <label>Email Address:<input type="email" name="email" required value="<?= e($old['email']) ?>" /></label>
        <label>Password:<input type="password" name="password" required /></label>
        <label>Confirm Password:<input type="password" name="confirm-password" required /></label>
      </fieldset>
    </div>

    <div class="password-requirements">
      <h4>Password Requirements:</h4>
      <ul>
        <li>At least 8 characters long</li>
        <li>Contains uppercase and lowercase letters</li>
        <li>Contains at least one number</li>
        <li>Contains at least one special character (!@#$%^&*)</li>
      </ul>
    </div>

    <fieldset class="form-column-full">
      <legend>Agreement</legend>
      <label class="terms-agreement">
        <input type="checkbox" name="terms" required />
        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
      </label>
    </fieldset>

    <button type="submit" class="form-button">Create Account</button>
  </form>

  <div class="auth-link">
    Already have an account? <a href="index.php?page=login">Login here</a>
  </div>
</main>
<?php
$footerExtra = '';
include root_path('includes/footer.php');
?>
