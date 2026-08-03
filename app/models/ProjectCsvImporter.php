<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Project CSV Importer
|--------------------------------------------------------------------------
| Imports rows from an exported "Project Manager" style CSV into the
| `projects` table (plus find-or-create support rows in `people` and
| `project_types`). Used by both the CLI script
| (scripts/import_projects_csv.php) and the Admin > Import Projects page.
|
| Fields imported from the CSV:
|   PROJECT NAME        -> projects.project_name
|   PROJ NUM (TOHS)      -> projects.project_code (cleaned; falls back to
|                            PROJ NUM (OTHER), then a generated slug code)
|   PROJ DESCRIPTION     -> projects.description
|   PROJ MANAGER         -> projects.project_manager_person_id
|                            (first name before ";" is used; person is
|                            looked up by first+last name, created if
|                            missing)
|   PROJECT TYPE         -> projects.project_type_id (find-or-create in
|                            project_types)
|   PROJECT OWNER        -> projects.department_id (mapped via
|                            DEPARTMENT_CODE_MAP below)
|   PRIORITY             -> projects.priority (mapped via PRIORITY_MAP)
|   PROJ PHASE           -> projects.status (mapped via PROPOSED_PHASES)
|   APPROVED BUDGET      -> projects.estimated_budget (numeric parse only)
|   SCHED CONS START     -> projects.start_date
|   SCHED CONS COMPLETE  -> projects.target_end_date
|
| Rows with a blank PROJECT NAME, or a name that looks like a template
| placeholder (e.g. "!SAMPLE PROJECT!"), are skipped.
|
| Re-running is safe: rows whose resolved project_code already existed in
| the `projects` table before the run are skipped rather than re-inserted.
*/

final class ProjectCsvImporter
{
    private const DEPARTMENT_CODE_MAP = [
        'U&I' => 'UI',
        'P&R' => 'PARKS',
        'PWD' => 'PW',
        'FIRE' => 'FIRE',
        'IT' => 'IT',
    ];

    private const PRIORITY_MAP = [
        'high priority' => 'high',
        'normal priority' => 'medium',
        'low priority' => 'low',
        'critical priority' => 'critical',
    ];

    // PROJ PHASE values that indicate the project hasn't started yet.
    // Everything else non-blank is treated as "active".
    private const PROPOSED_PHASES = ['conceptual', 'planning'];

    public function __construct(private PDO $pdo) {}

    /**
     * @return array{log: string[], inserted: int, skipped: int}
     */
    public function run(string $csvPath, bool $dryRun): array
    {
        $log = [];
        $rows = $this->readCsv($csvPath);

        $log[] = 'Read ' . count($rows) . " data rows from {$csvPath}";
        if ($dryRun) {
            $log[] = '*** DRY RUN: no changes will be committed ***';
        }

        $pdo = $this->pdo;

        $departmentsByCode = [];
        foreach ($pdo->query('SELECT department_id, department_code FROM departments') as $r) {
            $departmentsByCode[strtoupper((string)$r['department_code'])] = (int)$r['department_id'];
        }

        $projectTypesByName = [];
        foreach ($pdo->query('SELECT project_type_id, project_type_name FROM project_types') as $r) {
            $projectTypesByName[self::normalizeKey((string)$r['project_type_name'])] = (int)$r['project_type_id'];
        }

        $peopleByName = [];
        foreach ($pdo->query('SELECT person_id, first_name, last_name FROM people') as $r) {
            $peopleByName[self::normalizeKey($r['first_name'] . ' ' . $r['last_name'])] = (int)$r['person_id'];
        }

        // Snapshot of codes already in the DB before this run (used to detect
        // re-runs of the same import so we don't insert duplicates).
        $dbExistingCodes = [];
        foreach ($pdo->query('SELECT project_code FROM projects') as $r) {
            $dbExistingCodes[(string)$r['project_code']] = true;
        }
        // Codes consumed so far during this run (starts as a copy of the DB
        // snapshot, grows as rows are inserted). Used to auto-suffix
        // collisions between two different rows *within the CSV itself*.
        $usedCodesThisRun = $dbExistingCodes;

        $pdo->beginTransaction();

        $inserted = 0;
        $skipped = 0;

        $insertStmt = $pdo->prepare('
            INSERT INTO projects (
                project_code, project_name, description, status, priority,
                project_type_id, department_id, project_manager_person_id,
                start_date, target_end_date, estimated_budget
            ) VALUES (
                :project_code, :project_name, :description, :status, :priority,
                :project_type_id, :department_id, :project_manager_person_id,
                :start_date, :target_end_date, :estimated_budget
            )
        ');

        foreach ($rows as $i => $row) {
            $name = trim((string)($row['PROJECT NAME'] ?? ''));
            if (self::isSkippableProjectName($name)) {
                $log[] = 'Skipping row ' . ($i + 1) . ': blank/placeholder project name';
                $skipped++;
                continue;
            }

            // --- project_code ---
            $code = self::extractProjectCode((string)($row['PROJ NUM (TOHS)'] ?? ''));
            if ($code === '') {
                $code = self::extractProjectCode((string)($row['PROJ NUM (OTHER)'] ?? ''));
            }
            if ($code === '') {
                $code = 'IMPORT-' . strtoupper(substr(self::slugify($name), 0, 30));
            }

            if (isset($dbExistingCodes[$code])) {
                $log[] = "Skipping '{$name}': project_code '{$code}' already exists";
                $skipped++;
                continue;
            }

            if (isset($usedCodesThisRun[$code])) {
                // Two different rows in this CSV resolved to the same code -
                // auto-suffix so we don't silently drop a real project.
                $baseCode = $code;
                $suffix = 2;
                while (isset($usedCodesThisRun[$code])) {
                    $code = $baseCode . '-' . $suffix;
                    $suffix++;
                }
                $log[] = "  ! code collision on '{$baseCode}', using '{$code}' instead";
            }

            // --- department ---
            $ownerRaw = strtoupper(trim((string)($row['PROJECT OWNER'] ?? '')));
            $departmentId = null;
            if ($ownerRaw !== '' && isset(self::DEPARTMENT_CODE_MAP[$ownerRaw])) {
                $departmentId = $departmentsByCode[self::DEPARTMENT_CODE_MAP[$ownerRaw]] ?? null;
            }

            // --- project manager (first name if multiple, separated by ";") ---
            $managerRaw = (string)($row['PROJ MANAGER'] ?? '');
            $managerFirst = trim(explode(';', $managerRaw)[0]);
            $projectManagerId = $this->findOrCreatePerson($peopleByName, $managerFirst, $departmentId, $log);

            // --- project type ---
            $projectTypeId = $this->findOrCreateProjectType($projectTypesByName, (string)($row['PROJECT TYPE'] ?? ''), $log);

            // --- priority ---
            $priorityKey = self::normalizeKey((string)($row['PRIORITY'] ?? ''));
            $priority = self::PRIORITY_MAP[$priorityKey] ?? 'medium';

            // --- status (derived from PROJ PHASE) ---
            $phaseKey = self::normalizeKey((string)($row['PROJ PHASE'] ?? ''));
            $status = ($phaseKey === '' || in_array($phaseKey, self::PROPOSED_PHASES, true)) ? 'proposed' : 'active';

            // --- budget / dates ---
            $budget = self::parseBudget($row['APPROVED BUDGET'] ?? null);
            $startDate = self::parseDate($row['SCHED CONS START'] ?? null);
            $endDate = self::parseDate($row['SCHED CONS COMPLETE'] ?? null);

            $description = trim((string)($row['PROJ DESCRIPTION'] ?? ''));

            $insertStmt->execute([
                'project_code' => $code,
                'project_name' => $name,
                'description' => $description !== '' ? $description : null,
                'status' => $status,
                'priority' => $priority,
                'project_type_id' => $projectTypeId,
                'department_id' => $departmentId,
                'project_manager_person_id' => $projectManagerId,
                'start_date' => $startDate,
                'target_end_date' => $endDate,
                'estimated_budget' => $budget,
            ]);

            $usedCodesThisRun[$code] = true;
            $inserted++;
            $log[] = "Imported '{$name}' as project_code '{$code}'";
        }

        if ($dryRun) {
            $pdo->rollBack();
            $log[] = 'DRY RUN complete - no changes were saved.';
        } else {
            $pdo->commit();
            $log[] = 'Import committed.';
        }

        return ['log' => $log, 'inserted' => $inserted, 'skipped' => $skipped];
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function readCsv(string $csvPath): array
    {
        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            throw new RuntimeException("Could not open file: {$csvPath}");
        }

        // Strip a UTF-8 BOM if present so the header row parses cleanly.
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle, 0, ',', '"', '\\');
        if ($header === false) {
            fclose($handle);
            throw new RuntimeException('CSV appears to be empty.');
        }

        $rows = [];
        while (($line = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($line) === 1 && $line[0] === null) {
                continue; // blank line
            }
            $row = @array_combine($header, array_pad($line, count($header), null));
            if ($row === false) {
                continue; // column count mismatch, skip malformed row
            }
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function findOrCreateProjectType(array &$cache, string $name, array &$log): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }
        $key = self::normalizeKey($name);
        if (isset($cache[$key])) {
            return $cache[$key];
        }
        $pdo = $this->pdo;
        $maxSort = (int)$pdo->query('SELECT COALESCE(MAX(sort_order), 0) FROM project_types')->fetchColumn();
        $stmt = $pdo->prepare('INSERT INTO project_types (project_type_name, sort_order) VALUES (:name, :sort)');
        $stmt->execute(['name' => $name, 'sort' => $maxSort + 10]);
        $id = (int)$pdo->lastInsertId();
        $cache[$key] = $id;
        $log[] = "  + created project_type '{$name}' (id {$id})";
        return $id;
    }

    private function findOrCreatePerson(array &$cache, string $rawName, ?int $departmentId, array &$log): ?int
    {
        $rawName = trim($rawName);
        if ($rawName === '') {
            return null;
        }
        $isInactive = (bool)preg_match('/\(inactive\)/i', $rawName);
        $cleanName = trim(preg_replace('/\(inactive\)/i', '', $rawName) ?? '');
        if ($cleanName === '') {
            return null;
        }
        $key = self::normalizeKey($cleanName);
        if (isset($cache[$key])) {
            return $cache[$key];
        }
        $parts = preg_split('/\s+/', $cleanName, 2) ?: [$cleanName];
        $first = $parts[0];
        $last = $parts[1] ?? '';
        $pdo = $this->pdo;
        $stmt = $pdo->prepare('
            INSERT INTO people (first_name, last_name, department_id, is_active)
            VALUES (:first, :last, :department_id, :is_active)
        ');
        $stmt->execute([
            'first' => $first,
            'last' => $last,
            'department_id' => $departmentId,
            'is_active' => $isInactive ? 0 : 1,
        ]);
        $id = (int)$pdo->lastInsertId();
        $cache[$key] = $id;
        $log[] = "  + created person '{$cleanName}'" . ($isInactive ? ' (inactive)' : '') . " (id {$id})";
        return $id;
    }

    private static function normalizeKey(string $s): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $s) ?? ''));
    }

    private static function slugify(string $s): string
    {
        $s = strtolower($s);
        $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
        return trim($s, '-');
    }

    private static function extractProjectCode(string $raw): string
    {
        $clean = preg_replace('/(?i)tohs|proj|#/', '', $raw) ?? '';
        return trim(preg_replace('/\s+/', ' ', $clean) ?? '');
    }

    private static function parseBudget(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        $clean = preg_replace('/[^0-9.\-]/', '', $raw) ?? '';
        if ($clean === '' || !is_numeric($clean)) {
            return null;
        }
        return number_format((float)$clean, 2, '.', '');
    }

    private static function parseDate(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        $dt = DateTime::createFromFormat('n/j/Y', $raw);
        if ($dt === false) {
            return null;
        }
        return $dt->format('Y-m-d');
    }

    private static function isSkippableProjectName(string $name): bool
    {
        $name = trim($name);
        if ($name === '') {
            return true;
        }
        if (preg_match('/^!.*!$/', $name) === 1) {
            return true; // e.g. "!SAMPLE PROJECT!" placeholder rows
        }
        return false;
    }
}
