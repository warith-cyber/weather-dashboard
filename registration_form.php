<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (authUser() !== null) {
    redirect('dashboard.php');
}

$formData = [
    'full_name' => '',
    'username' => '',
    'email' => '',
    'terms' => false,
];

$error = pullFlash('error');
$success = pullFlash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
    $formData['username'] = trim((string) ($_POST['username'] ?? ''));
    $formData['email'] = trim((string) ($_POST['email'] ?? ''));
    $formData['terms'] = isset($_POST['terms']);
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'The form session expired. Please try again.';
    } elseif ($formData['full_name'] === '' || $formData['username'] === '' || $formData['email'] === '' || $password === '' || $confirmPassword === '') {
        $error = 'Complete every field before creating your account.';
    } elseif (!$formData['terms']) {
        $error = 'You must accept the terms to register.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $formData['username'])) {
        $error = 'Usernames must be 3 to 50 characters and use letters, numbers, dots, underscores, or hyphens.';
    } elseif (strlen($password) < 8) {
        $error = 'Choose a password with at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $fullName = $formData['full_name'];
            $username = strtolower($formData['username']);
            $email = strtolower($formData['email']);

            $existingStatement = db()->prepare(
                'SELECT username, email
                 FROM users
                 WHERE username = :username OR email = :email
                 LIMIT 1'
            );
            $existingStatement->execute([
                'username' => $username,
                'email' => $email,
            ]);
            $existingUser = $existingStatement->fetch();

            if ($existingUser !== false) {
                if ($existingUser['username'] === $username) {
                    $error = 'That username is already taken.';
                } else {
                    $error = 'That email address is already registered.';
                }
            } else {
                $insertStatement = db()->prepare(
                    'INSERT INTO users (full_name, username, email, password_hash)
                     VALUES (:full_name, :username, :email, :password_hash)'
                );
                $insertStatement->execute([
                    'full_name' => $fullName,
                    'username' => $username,
                    'email' => $email,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);

                flash('success', 'Registration complete. Sign in with your new account.');
                redirect('login_form.php');
            }
        } catch (Throwable $exception) {
            $error = 'Unable to save your account. Confirm PostgreSQL is running and PHP has pdo_pgsql enabled.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="register-page">
    <main class="box">
        <section class="brand-panel">
            <p class="eyebrow">Create Access</p>
            <h1>Join the workspace.</h1>
            <p class="intro">
                Create your account to manage projects, organize files, and collaborate from a single secure dashboard.
            </p>

            <div class="highlights">
                <span>Fast onboarding</span>
                <span>Protected profiles</span>
            </div>

            <div class="stats">
                <article>
                    <strong>5 min</strong>
                    <span>average setup time</span>
                </article>
                <article>
                    <strong>Secure</strong>
                    <span>verified account creation</span>
                </article>
            </div>
        </section>

        <section class="form-panel">
            <div class="form">
                <form method="post" action="registration_form.php" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                    <p class="form-kicker">Create Account</p>
                    <h2>Register</h2>
                    <p class="form-text">Fill in your details below to create a new account.</p>

                    <?php if ($error !== null): ?>
                        <div class="alert alert-error"><?= e($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success !== null): ?>
                        <div class="alert alert-success"><?= e($success) ?></div>
                    <?php endif; ?>

                    <label class="input-group">
                        <span>Full Name</span>
                        <input
                            type="text"
                            name="full_name"
                            value="<?= e($formData['full_name']) ?>"
                            placeholder="Jane Doe"
                            autocomplete="name"
                            required
                        >
                    </label>

                    <label class="input-group">
                        <span>Username</span>
                        <input
                            type="text"
                            name="username"
                            value="<?= e($formData['username']) ?>"
                            placeholder="jane.doe"
                            autocomplete="username"
                            required
                        >
                    </label>

                    <label class="input-group">
                        <span>Email</span>
                        <input
                            type="email"
                            name="email"
                            value="<?= e($formData['email']) ?>"
                            placeholder="name@example.com"
                            autocomplete="email"
                            required
                        >
                    </label>

                    <label class="input-group">
                        <span>Password</span>
                        <input
                            type="password"
                            name="password"
                            placeholder="Create a password"
                            autocomplete="new-password"
                            required
                        >
                    </label>

                    <label class="input-group">
                        <span>Confirm Password</span>
                        <input
                            type="password"
                            name="confirm_password"
                            placeholder="Repeat your password"
                            autocomplete="new-password"
                            required
                        >
                    </label>

                    <div class="form-row">
                        <label class="remember">
                            <input type="checkbox" name="terms" <?= $formData['terms'] ? 'checked' : '' ?>>
                            <span>I agree to the terms</span>
                        </label>
                        <a href="login_form.php">Back to sign in</a>
                    </div>

                    <input type="submit" value="Create Account">

                    <div class="links">
                        <span>Already have an account?</span>
                        <a href="login_form.php">Sign In</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
