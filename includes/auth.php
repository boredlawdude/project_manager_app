<?php
declare(strict_types=1);

/**
 * Shared login against the SAME `people` table as contracts_app.
 * Requires people.email, people.password_hash, people.can_login=1, people.is_active=1
 */

function current_person(): array
{
    if (empty($_SESSION['person']['person_id'])) {
        return [];
    }

    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $pdo = db();
    $stmt = $pdo->prepare("
      SELECT
        person_id,
        email,
        COALESCE(
          NULLIF(TRIM(full_name), ''),
          NULLIF(TRIM(display_name), ''),
          NULLIF(TRIM(CONCAT(COALESCE(first_name,''), ' ', COALESCE(last_name,''))), ''),
          email,
          'Unknown'
        ) AS name,
        department_id,
        is_active
      FROM people
      WHERE person_id = ?
      LIMIT 1
    ");
    $stmt->execute([$_SESSION['person']['person_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $roles = [];
    $role = null;
    if (!empty($user['person_id'])) {
        $roleStmt = $pdo->prepare("
            SELECT r.role_key
            FROM person_roles pr
            JOIN roles r ON r.role_id = pr.role_id AND r.is_active = 1
            WHERE pr.person_id = ?
        ");
        $roleStmt->execute([$user['person_id']]);
        $roles = $roleStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        if (!empty($roles)) {
            $role = strtoupper($roles[0]);
        }
    }

    $user['roles'] = $roles;
    $user['role'] = $role;
    $user['name'] = $user['name'] ?? $user['email'] ?? 'Unknown User';

    $_SESSION['person'] = array_merge($_SESSION['person'] ?? [], $user);
    $cached = $user;

    return $cached;
}

function current_person_id(): int
{
    $p = current_person();
    return (int)($p['person_id'] ?? 0);
}

function require_login(): void
{
    if (!current_person()) {
        $next = $_SERVER['REQUEST_URI'] ?? '/';
        header('Location: /login.php?next=' . urlencode($next));
        exit;
    }
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    }
}

function logout_person(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'] ?? '/',
            $params['domain'] ?? '',
            (bool)($params['secure'] ?? false),
            (bool)($params['httponly'] ?? true)
        );
    }

    session_destroy();
}

function login_person(string $email, string $password): bool
{
    $email = trim(strtolower($email));
    if ($email === '' || $password === '') {
        return false;
    }

    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT person_id, email, password_hash, can_login, is_active, full_name, first_name, last_name
        FROM people
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$p) return false;
    if ((int)$p['is_active'] !== 1) return false;
    if ((int)$p['can_login'] !== 1) return false;
    if (empty($p['password_hash'])) return false;
    if (!password_verify($password, (string)$p['password_hash'])) return false;

    if (password_needs_rehash((string)$p['password_hash'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE people SET password_hash = ? WHERE person_id = ?")
            ->execute([$newHash, (int)$p['person_id']]);
    }

    $pdo->prepare("UPDATE people SET last_login_at = CURRENT_TIMESTAMP WHERE person_id = ?")
        ->execute([(int)$p['person_id']]);

    session_regenerate_id(true);

    $name = trim((string)($p['full_name'] ?? ''));
    if ($name === '') {
        $name = trim((string)($p['first_name'] ?? '') . ' ' . (string)($p['last_name'] ?? ''));
    }
    if ($name === '') $name = (string)$p['email'];

    $_SESSION['person'] = [
        'person_id' => (int)$p['person_id'],
        'email'     => (string)$p['email'],
        'name'      => $name,
    ];

    return true;
}

function person_has_role_key(string $role_key): bool
{
    require_login();
    $pid = current_person_id();
    $stmt = db()->prepare("
        SELECT COUNT(*) FROM person_roles pr
        JOIN roles r ON r.role_id = pr.role_id AND r.is_active = 1
        WHERE pr.person_id = ? AND r.role_key = ?
    ");
    $stmt->execute([$pid, $role_key]);
    return (int)$stmt->fetchColumn() > 0;
}

function is_system_admin(): bool
{
    return person_has_role_key('SUPERUSER') || person_has_role_key('ADMIN');
}

function require_system_admin(): void
{
    require_login();
    if (!is_system_admin()) {
        http_response_code(403);
        echo "Forbidden (admin only).";
        exit;
    }
}
