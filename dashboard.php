<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$user = requireAuth();
$success = pullFlash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-page">
    <main class="box">
        <section class="brand-panel">
            <p class="eyebrow">Active Session</p>
            <h1>Hello, <?= e($user['full_name']) ?>.</h1>
            <p class="intro">
                Your PHP session is active and was created from a PostgreSQL-backed login using the stored credentials in this project.
            </p>

            <div class="highlights">
                <span>Session protected</span>
                <span>Database connected</span>
            </div>

            <div class="stats">
                <article>
                    <strong><?= e($user['username']) ?></strong>
                    <span>signed-in username</span>
                </article>
                <article>
                    <strong><?= e($user['email']) ?></strong>
                    <span>registered email</span>
                </article>
            </div>
        </section>

        <section class="form-panel">
            <div class="form dashboard-shell">
                <div class="dashboard-card">
                    <p class="form-kicker">Workspace Access</p>
                    <h2>Account Overview</h2>
                    <p class="form-text">This page is only available after a successful login.</p>

                    <?php if ($success !== null): ?>
                        <div class="alert alert-success"><?= e($success) ?></div>
                    <?php endif; ?>

                    <div class="detail-grid">
                        <article class="detail-item">
                            <span>Full Name</span>
                            <strong><?= e($user['full_name']) ?></strong>
                        </article>
                        <article class="detail-item">
                            <span>Username</span>
                            <strong><?= e($user['username']) ?></strong>
                        </article>
                        <article class="detail-item">
                            <span>Email</span>
                            <strong><?= e($user['email']) ?></strong>
                        </article>
                    </div>

                    <div class="page-actions">
                        <a class="button-link" href="logout.php">Sign Out</a>
                        <a href="login_form.php">Return to login</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
