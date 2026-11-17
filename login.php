<?php
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    redirect('/hotel_housekeeping_department/dashboard.php');
}

$userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$isSetup = $userCount === 0;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if ($isSetup) {
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10) {
            $error = 'Enter your name, a valid email, and a password of at least 10 characters.';
        } else {
            $lockAcquired = false;
            try {
                $lockAcquired = (int) $pdo->query("SELECT GET_LOCK('housekeeping_initial_admin', 5)")->fetchColumn() === 1;
                if (!$lockAcquired) {
                    $error = 'Account setup is busy. Please refresh and try again.';
                } else {
                    $pdo->beginTransaction();
                    if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() !== 0) {
                        $pdo->rollBack();
                        $pdo->query("SELECT RELEASE_LOCK('housekeeping_initial_admin')");
                        $lockAcquired = false;
                        redirect('/hotel_housekeeping_department/login.php');
                    }
                    $statement = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
                    $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), 'admin']);
                    $id = (int) $pdo->lastInsertId();
                    $pdo->commit();
                    $pdo->query("SELECT RELEASE_LOCK('housekeeping_initial_admin')");
                    $lockAcquired = false;
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_role'] = 'admin';
                    redirect('/hotel_housekeeping_department/dashboard.php');
                }
            } catch (PDOException $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if ($lockAcquired) {
                    $pdo->query("SELECT RELEASE_LOCK('housekeeping_initial_admin')");
                }
                $error = 'Account setup failed. The email may already be in use; please try another.';
            }
        }
    } else {
        $statement = $pdo->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
        $statement->execute([$email]);
        $user = $statement->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'That email and password combination was not recognized.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            redirect('/hotel_housekeeping_department/dashboard.php');
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $isSetup ? 'Set up your account' : 'Sign in' ?> · Hearth & Haven</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/hotel_housekeeping_department/assets/css/style.css">
</head>
<body class="login-page">
<main class="login-card">
    <a class="brand login-brand" href="/hotel_housekeeping_department/"><span class="brand-mark">H</span><span><strong>Hearth & Haven</strong><small>HOUSEKEEPING</small></span></a>
    <p class="eyebrow"><?= $isSetup ? 'A FRESH START' : 'WELCOME BACK' ?></p>
    <h1><?= $isSetup ? 'Create your admin account' : 'Good to see you.' ?></h1>
    <p class="muted"><?= $isSetup ? 'Set up the first administrator to get your housekeeping workspace ready.' : 'Sign in to your housekeeping workspace.' ?></p>
    <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form-stack">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <?php if ($isSetup): ?>
        <label>Your name<input name="name" required autocomplete="name" value="<?= e($_POST['name'] ?? '') ?>"></label>
        <?php endif; ?>
        <label>Email address<input type="email" name="email" required autocomplete="username" value="<?= e($_POST['email'] ?? '') ?>"></label>
        <label>Password<input type="password" name="password" required autocomplete="<?= $isSetup ? 'new-password' : 'current-password' ?>" <?= $isSetup ? 'minlength="10"' : '' ?>></label>
        <?php if ($isSetup): ?><small class="form-hint">Use at least 10 characters.</small><?php endif; ?>
        <button class="button button-primary button-wide" type="submit"><?= $isSetup ? 'Create workspace' : 'Sign in' ?><span>→</span></button>
    </form>
</main>
<div class="login-decoration"><span>✿</span><p>Every room, a welcome.<br>Every detail, a little brighter.</p></div>
</body>
</html>
