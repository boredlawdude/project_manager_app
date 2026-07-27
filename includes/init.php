<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionName = $_ENV['SESSION_NAME'] ?? 'pma_app_sess';
    session_name($sessionName);

    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $domain = parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST) ?: '';
    if ($domain === 'localhost' || $domain === '127.0.0.1') {
        $domain = '';
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $domain,
        'httponly' => true,
        'secure' => $secure,
        'samesite' => 'Lax',
    ]);

    session_start();

    $fingerprint = hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . ($secure ? 'https' : 'http'));
    if (isset($_SESSION['_fingerprint'])) {
        if (!hash_equals($_SESSION['_fingerprint'], $fingerprint)) {
            session_unset();
            session_destroy();
            session_start();
        }
    } else {
        $_SESSION['_fingerprint'] = $fingerprint;
    }

    $maxLifetime = 8 * 3600;
    if (isset($_SESSION['_created_at'])) {
        if (time() - $_SESSION['_created_at'] > $maxLifetime) {
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['_fingerprint'] = $fingerprint;
            $_SESSION['_created_at'] = time();
        }
    } else {
        $_SESSION['_created_at'] = time();
    }
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
