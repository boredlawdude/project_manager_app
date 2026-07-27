<?php
declare(strict_types=1);

final class PmSetting
{
    public function __construct(private PDO $db) {}

    public function get(string $key, string $default = ''): string
    {
        $stmt = $this->db->prepare("SELECT setting_value FROM pm_settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false && $val !== null && $val !== '' ? (string)$val : $default;
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM pm_settings ORDER BY setting_key ASC");
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[$row['setting_key']] = $row;
        }
        return $rows;
    }

    public function set(string $key, string $value): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO pm_settings (setting_key, setting_value) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        return $stmt->execute([$key, $value]);
    }
}
