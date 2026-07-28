<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Application Bootstrap (project_manager_app)
|--------------------------------------------------------------------------
| Single source of truth for env loading + DB connection. Deliberately kept
| to ONE bootstrap file (unlike contracts_app's includes/bootstrap.php +
| app/bootstrap.php split) to avoid the dual-env-loading gotcha documented
| in that project's memory notes.
|
| This app shares the SAME MySQL database as contracts_app (contract_manager)
| so that `projects` and `contracts.project_id` can reference each other.
*/

require_once __DIR__ . '/../vendor/autoload.php';

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

if (is_file(APP_ROOT . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
    $dotenv->safeLoad();
}

if (!defined('APP_NAME')) {
    define('APP_NAME', $_ENV['APP_NAME'] ?? 'Project Manager');
}

date_default_timezone_set('America/New_York');

if (!function_exists('db')) {
    function db(): PDO
    {
        static $pdo = null;

        if ($pdo === null) {
            try {
                $pdo = new PDO(
                    "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'] . ";charset=utf8mb4",
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASS'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
                try {
                    $pdo->exec("SET time_zone = 'America/New_York'");
                } catch (PDOException $tzEx) {
                    // Named timezone tables not loaded; PHP side is already set.
                }
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }

        return $pdo;
    }
}

if (!function_exists('pdo')) {
    function pdo(): PDO
    {
        return db();
    }
}

/*
|--------------------------------------------------------------------------
| OnlyOffice helpers (mirrors contracts_app's includes/config.php + auth.php)
|--------------------------------------------------------------------------
| OO_SECRET signs the short-lived download/callback/forcesave URLs the
| Document Server calls back on (no session cookie available there).
| ONLYOFFICE_JWT_SECRET, if set, additionally signs the editor config/
| command payloads when the Document Server itself has JWT enabled.
*/

defined('OO_SECRET') || define('OO_SECRET', $_ENV['OO_SECRET'] ?? '');
defined('ONLYOFFICE_JWT_SECRET') || define('ONLYOFFICE_JWT_SECRET', $_ENV['ONLYOFFICE_JWT_SECRET'] ?? '');

if (!function_exists('oo_sign')) {
    function oo_sign(array $params): string
    {
        ksort($params);
        $base = http_build_query($params);
        return hash_hmac('sha256', $base, OO_SECRET);
    }
}

if (!function_exists('oo_verify')) {
    function oo_verify(array $params, string $sig): bool
    {
        $expected = oo_sign($params);
        return hash_equals($expected, $sig);
    }
}

if (!function_exists('oo_jwt_sign')) {
    function oo_jwt_sign(array $payload, string $secret): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $b64 = fn($v) => rtrim(strtr(base64_encode(json_encode($v)), '+/', '-_'), '=');
        $h = $b64($header);
        $p = $b64($payload);
        $sig = rtrim(strtr(base64_encode(hash_hmac('sha256', "$h.$p", $secret, true)), '+/', '-_'), '=');
        return "$h.$p.$sig";
    }
}
