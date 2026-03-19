<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

logoutUser();
flash('success', 'You have been signed out.');
redirect('login_form.php');
