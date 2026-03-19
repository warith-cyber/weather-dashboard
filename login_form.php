<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (authUser() !== null) {
    redirect('dashboard.php');
}

$formData = [
    'identifier' => '',
];

$error = pullFlash('error');
$success = pullFlash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['identifier'] = trim((string) ($_POST['identifier'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'The form session expired. Please try again.';
    } elseif ($formData['identifier'] === '' || $password === '') {
        $error = 'Enter your username or email and password.';
    } else {
        try {
            $identifier = strtolower($formData['identifier']);
            $statement = db()->prepare(
                'SELECT id, full_name, username, email, password_hash
                 FROM users
                 WHERE username = :identifier OR email = :identifier
                 LIMIT 1'
            );
            $statement->execute(['identifier' => $identifier]);
            $user = $statement->fetch();

            if ($user === false || !password_verify($password, $user['password_hash'])) {
                $error = 'Invalid login details.';
            } else {
                loginUser($user);
                flash('success', 'Signed in successfully.');
                redirect('dashboard.php');
            }
        } catch (Throwable $exception) {
            $error = 'Unable to reach PostgreSQL. Confirm the server is running and PHP has pdo_pgsql enabled.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <main class="box">
        <section class="brand-panel">
            <p class="eyebrow">Secure Access</p>
            <h1>Welcome back.</h1>
            <p class="intro">
                Sign in to review your projects, recent activity, and shared resources in one focused space.
            </p>

            <div class="highlights">
                <span>Focused workflow</span>
                <span>Protected sessions</span>
            </div>

            <div class="stats">
                <article>
                    <strong>24/7</strong>
                    <span>account availability</span>
                </article>
                <article>
                    <strong>256-bit</strong>
                    <span>encrypted sign-in</span>
                </article>
            </div>
        </section>

        <section class="form-panel">
            <div class="form">
                <form method="post" action="login_form.php" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                    <p class="form-kicker">Member Login</p>
                    <h2>Sign In</h2>
                    <p class="form-text">Use your details below to continue to your account.</p>

                    <?php if ($error !== null): ?>
                        <div class="alert alert-error"><?= e($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success !== null): ?>
                        <div class="alert alert-success"><?= e($success) ?></div>
                    <?php endif; ?>

                    <label class="input-group">
                        <span>Username or Email</span>
                        <input
                            type="text"
                            name="identifier"
                            value="<?= e($formData['identifier']) ?>"
                            placeholder="jane.doe or name@example.com"
                            autocomplete="username"
                            required
                        >
                    </label>

                    <label class="input-group">
                        <span>Password</span>
                        <input
                            type="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >
                    </label>

                    <div class="form-row">
                        <span class="form-note">Passwords are checked securely with PHP password hashing.</span>
                        <a href="registration_form.php">Create an account</a>
                    </div>

                    <input type="submit" value="Access Account">

                    <div class="links">
                        <span>Need an account?</span>
                        <a href="registration_form.php">Register</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
