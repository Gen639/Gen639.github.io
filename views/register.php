<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['idUser'])) {
    header('Location: profile.php');
    exit;
}
$activePage = 'register';
$prefix = '../';
$pageTitle = 'Register | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="../css/register.css" />';
$errors = [];
$successMessage = '';
$old = [
    'nombre' => '',
    'apellidos' => '',
    'telefono' => '',
    'fecha_nacimiento' => '',
    'direccion' => '',
    'sexo' => '',
    'email' => '',
    'usuario' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'apellidos' => trim($_POST['apellidos'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
        'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
        'direccion' => trim($_POST['direccion'] ?? ''),
        'sexo' => trim($_POST['sexo'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'usuario' => trim($_POST['usuario'] ?? ''),
    ];

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm-password'] ?? '';
    $termsAccepted = isset($_POST['terms']);

    if ($old['nombre'] === '') {
        $errors[] = 'First name is required.';
    }

    if ($old['apellidos'] === '') {
        $errors[] = 'Last name is required.';
    }

    if ($old['telefono'] === '') {
        $errors[] = 'Phone number is required.';
    }

    if ($old['fecha_nacimiento'] === '') {
        $errors[] = 'Date of birth is required.';
    } elseif (!DateTime::createFromFormat('Y-m-d', $old['fecha_nacimiento']) || DateTime::createFromFormat('Y-m-d', $old['fecha_nacimiento'])->format('Y-m-d') !== $old['fecha_nacimiento']) {
        $errors[] = 'Date of birth must be a valid date.';
    }

    if ($old['direccion'] === '') {
        $errors[] = 'Address is required.';
    }

    if ($old['sexo'] === '') {
        $errors[] = 'Gender is required.';
    }

    if ($old['email'] === '') {
        $errors[] = 'Email direccion is required.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email direccion.';
    }

    if ($old['usuario'] === '') {
        $errors[] = 'Username is required.';
    }

    if ($password === '' || $confirmPassword === '') {
        $errors[] = 'Password and confirm password are required.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    } else {
       // if (strlen($password) < 8) {
         //   $errors[] = 'Password must be at least 8 characters long.';
       // }
        //if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[!@#$%^&*]/', $password)) {
        //    $errors[] = 'Password must contain uppercase and lowercase letters, a number, and a special character.';
       // }
    }

    if (!$termsAccepted) {
        $errors[] = 'You must accept the Terms of Service and Privacy Policy.';
    }

    if (empty($errors)) {
        require __DIR__ . '/../includes/db.php';

        if ($dbError !== null) {
            $errors[] = $dbError;
        } else {
            $stmt = $mysqli->prepare('SELECT idUser FROM users_data WHERE email = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('s', $old['email']);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $errors[] = 'The email direccion is already registered.';
                }
                $stmt->close();
            }

            $stmt = $mysqli->prepare('SELECT idLogin FROM users_login WHERE usuario = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('s', $old['usuario']);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $errors[] = 'The usuario is already taken.';
                }
                $stmt->close();
            }

            if (empty($errors)) {
$stmt = $mysqli->prepare('INSERT INTO users_data (nombre, apellidos, email, telefono, fecha_nacimiento, direccion, sexo) VALUES (?, ?, ?, ?, ?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param(
                    'sssssss',
                    $old['nombre'],
                    $old['apellidos'],
                    $old['email'],
                    $old['telefono'],
                    $old['fecha_nacimiento'],
                    $old['direccion'],
                    $old['sexo']
                    );

                    if ($stmt->execute()) {
                        $idUser = $mysqli->insert_id;
                        $stmt->close();

                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        $rol = 'user';

                        $stmt = $mysqli->prepare('INSERT INTO users_login (usuario, password, idUser, rol) VALUES (?, ?, ?, ?)');
                        if ($stmt) {
                            $stmt->bind_param('ssis', $old['usuario'], $passwordHash, $idUser, $rol);
                            if ($stmt->execute()) {
                                $successMessage = 'Registration successful! Redirecting to login page...';
                                $old = [
                                    'nombre' => '',
                                    'apellidos' => '',
                                    'telefono' => '',
                                    'fecha_nacimiento' => '',
                                    'direccion' => '',
                                    'sexo' => '',
                                    'email' => '',
                                    'usuario' => ''
                                ];
                            } else {
                                $errors[] = 'Could not create the login record. Please try again.';
                            }
                            $stmt->close();
                        } else {
                            $errors[] = 'Failed to prepare login insert statement.';
                        }
                    } else {
                        $errors[] = 'Could not save your personal data. Please try again.';
                    }
                } else {
                    $errors[] = 'Failed to prepare personal data insert statement.';
                }
            }

            $mysqli->close();
        }
    }
}

include __DIR__ . '/../includes/header.php';
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
          <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach ?>
      </ul>
    </div>
  <?php endif ?>

  <?php if ($successMessage) : ?>
    <div class="form-messages success"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
    <script>setTimeout(function() { window.location.href = 'login.php'; }, 3000);</script>
  <?php endif ?>
  
  <form method="POST" action="#">
    <div class="form-columns">
      <fieldset class="form-column">
        <legend>Personal Information</legend>
        <label>
          First Name:
          <input 
            type="text" 
            name="nombre" 
            maxlength="50"
            required
            value="<?= htmlspecialchars($old['nombre'], ENT_QUOTES, 'UTF-8') ?>"
          />
        </label>
        <label>
          Last Name:
          <input 
            type="text" 
            name="apellidos" 
            maxlength="50"
            required
            value="<?= htmlspecialchars($old['apellidos'], ENT_QUOTES, 'UTF-8') ?>"
          />
        </label>
        <label>
          Phone Number:
          <input 
            type="text" 
            name="telefono" 
            maxlength="20"
            required
            value="<?= htmlspecialchars($old['telefono'], ENT_QUOTES, 'UTF-8') ?>"
          />
        </label>
        <label>
          Date of Birth:
          <input 
            type="date" 
            name="fecha_nacimiento" 
            required
            value="<?= htmlspecialchars($old['fecha_nacimiento'], ENT_QUOTES, 'UTF-8') ?>"
          />
        </label>
        <label>
          Address:
          <input 
            type="text" 
            name="direccion" 
            maxlength="150"
            required
            value="<?= htmlspecialchars($old['direccion'], ENT_QUOTES, 'UTF-8') ?>"
          />
        </label>
        <label>
          Gender:
          <select name="sexo" required>
            <option value="">Select your sexo</option>
            <option value="female"<?= $old['sexo'] === 'female' ? ' selected' : '' ?>>Female</option>
            <option value="male"<?= $old['sexo'] === 'male' ? ' selected' : '' ?>>Male</option>
            <!--<option value="nonbinary"<?= $old['sexo'] === 'nonbinary' ? ' selected' : '' ?>>Non-binary</option>-->
            <option value="other"<?= $old['sexo'] === 'other' ? ' selected' : '' ?>>Other</option>
          </select>
        </label>
      </fieldset>

      <fieldset class="form-column">
        <legend>Account Details</legend>
        <label>
          Username:
          <input 
            type="text" 
            name="usuario" 
            maxlength="50"
            required
            value="<?= htmlspecialchars($old['usuario'], ENT_QUOTES, 'UTF-8') ?>"
          />
        </label>
        <label>
          Email Address:
          <input 
            type="email" 
            name="email" 
            required
            value="<?= htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8') ?>"
          />
        </label>
        <label>
          Password:
          <input 
            type="password" 
            name="password" 
            required
          />
        </label>
        <label>
          Confirm Password:
          <input 
            type="password" 
            name="confirm-password" 
            required
          />
        </label>
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
    Already have an account? <a href="login.php">Login here</a>
  </div>
</main>
<?php
$footerExtra = '';
include __DIR__ . '/../includes/footer.php';
?>

