<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(400);
        exit('Invalid form token. Please go back, refresh the page, and try again.');
    }
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /hotel_housekeeping_department/login.php');
        exit;
    }
}

function require_role(array $roles): void
{
    require_login();
    if (!in_array((string) ($_SESSION['user_role'] ?? ''), $roles, true)) {
        http_response_code(403);
        exit('You do not have permission to access this page.');
    }
}

function current_user(): array
{
    return [
        'id' => (int) ($_SESSION['user_id'] ?? 0),
        'name' => (string) ($_SESSION['user_name'] ?? ''),
        'role' => (string) ($_SESSION['user_role'] ?? ''),
    ];
}

function set_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}
