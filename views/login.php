<?php
session_start();

$activePage = 'login';
$prefix = '../';
$pageTitle = 'Login | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="../css/login.css" />';

$errors = [];
$successMessage = '';
$rememberedEmail = $_COOKIE['remember_user'] ?? '';

if (isset($_SESSION['idUser'])) {
    header('Location: profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email direccion.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        require __DIR__ . '/../includes/db.php';

        if ($dbError !== null) {
            $errors[] = $dbError;
        } else {
            $stmt = $mysqli->prepare('
                SELECT ul.idLogin, ul.idUser, ul.password, ul.rol, ud.nombre, ud.apellidos
                FROM users_login ul
                JOIN users_data ud ON ul.idUser = ud.idUser
                WHERE ud.email = ? LIMIT 1
            ');
            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($idLogin, $idUser, $hashedPassword, $rol, $nombre, $apellidos);
                    $stmt->fetch();

                    if (is_string($hashedPassword) && password_verify($password, $hashedPassword)) {
                        $_SESSION['idUser'] = $idUser;
                        $_SESSION['rol'] = $rol;
                        $_SESSION['user_name'] = $nombre . ' ' . $apellidos;
                        $_SESSION['user_email'] = $email;
                        $_SESSION['flash_success'] = 'Login successful. Welcome back!';

                        if ($remember) {
                            // Set cookie for 30 days
                            setcookie('remember_user', $email, time() + (30 * 24 * 60 * 60), '/');
                        }

                        header('Location: ../index.php');
                        exit;
                    } else {
                        $errors[] = 'Invalid email or password.';
                    }
                } else {
                    $errors[] = 'Invalid email or password.';
                }
                $stmt->close();
            } else {
                $errors[] = 'Database query failed. Please try again later.';
            }

            $mysqli->close();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<main class="login-page">
  <h1>Login</h1>
  <div class="login-intro">
    <p>Welcome back to JingleWorks! Log in to access your account and manage your projects.</p>
  </div>
  
  <form method="POST" action="#">
    <fieldset>
      <legend>Account Credentials</legend>
      <label>
        Email Address:
        <input 
          type="email" 
          name="email" 
          placeholder="your.email@example.com" value="<?= htmlspecialchars($rememberedEmail, ENT_QUOTES, 'UTF-8') ?>" 
          required
        />
      </label>

      <label>
        Password:
        <input 
          type="password" 
          name="password" 
          placeholder="Enter your password" 
          required
        />
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
    Don't have an account? <a href="register.php">Create one here</a>
  </div>
</main>
<?php
$footerExtra = '';
include __DIR__ . '/../includes/footer.php';
?>


