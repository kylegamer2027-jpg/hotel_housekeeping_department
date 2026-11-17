<?php
declare(strict_types=1);

function load_environment_file(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        throw new RuntimeException('Unable to read the application .env file.');
    }

    foreach ($lines as $lineNumber => $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }

        if (!preg_match('/^([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/', $line, $matches)) {
            throw new RuntimeException('Invalid .env entry on line ' . ($lineNumber + 1) . '.');
        }

        $name = $matches[1];
        if (getenv($name) !== false) {
            continue;
        }

        $value = trim($matches[2]);
        if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
            $quote = $value[0];
            if (strlen($value) < 2 || substr($value, -1) !== $quote) {
                throw new RuntimeException('Unclosed quoted value in .env on line ' . ($lineNumber + 1) . '.');
            }
            $value = substr($value, 1, -1);
        }

        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

load_environment_file(dirname(__DIR__) . '/.env');

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'housekeeping';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

if (!ctype_digit($dbPort) || (int) $dbPort < 1 || (int) $dbPort > 65535) {
    http_response_code(500);
    exit('Invalid DB_PORT in the application environment.');
}

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    http_response_code(500);
    exit('Database connection failed. Check config/database.php settings and import database/housekeeping.sql.');
}
