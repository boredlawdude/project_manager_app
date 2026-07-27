<?php
declare(strict_types=1);

if (!function_exists('h')) {
    function h(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('e')) {
    function e(mixed $value): void
    {
        echo h($value);
    }
}

if (!function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        $base = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__);

        return $path !== ''
            ? $base . '/' . ltrim($path, '/')
            : $base;
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        $base = app_path('storage');

        return $path !== ''
            ? $base . '/' . ltrim($path, '/')
            : $base;
    }
}

if (!function_exists('fmt_date')) {
    function fmt_date(?string $date): string
    {
        if (!$date) {
            return '';
        }
        $ts = strtotime($date);
        return $ts ? date('m/d/Y', $ts) : '';
    }
}

if (!function_exists('fmt_money')) {
    function fmt_money(mixed $amount): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }
        return '$' . number_format((float)$amount, 2);
    }
}

if (!function_exists('contracts_app_url')) {
    /**
     * Builds a link into contracts_app (a separate, standalone codebase/deploy
     * that shares this app's database). Base URL comes from CONTRACTS_APP_URL
     * in .env, e.g. https://pact.schifano.com or http://contracts_app.test
     */
    function contracts_app_url(string $path = ''): string
    {
        $base = rtrim((string)($_ENV['CONTRACTS_APP_URL'] ?? ''), '/');
        return $base !== '' ? $base . '/' . ltrim($path, '/') : '#';
    }
}
