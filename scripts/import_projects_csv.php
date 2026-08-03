<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Project CSV Importer (CLI)
|--------------------------------------------------------------------------
| Usage:
|   php scripts/import_projects_csv.php /path/to/file.csv [--dry-run]
|
| --dry-run runs the whole import inside a transaction and rolls it back
| at the end, printing what WOULD have happened. Always run with
| --dry-run first and review the summary before committing for real.
|
| The actual import logic lives in app/models/ProjectCsvImporter.php and is
| shared with the Admin > Import Projects page in the web UI.
*/

require __DIR__ . '/../includes/bootstrap.php';
require APP_ROOT . '/app/models/ProjectCsvImporter.php';

$csvPath = null;
$dryRun = false;
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--dry-run') {
        $dryRun = true;
    } elseif ($csvPath === null) {
        $csvPath = $arg;
    }
}

if ($csvPath === null || !is_file($csvPath)) {
    fwrite(STDERR, "Usage: php scripts/import_projects_csv.php <path-to-csv> [--dry-run]\n");
    exit(1);
}

$importer = new ProjectCsvImporter(db());
$result = $importer->run($csvPath, $dryRun);

foreach ($result['log'] as $line) {
    echo $line . "\n";
}

echo "\n----------------------------------------\n";
echo "Inserted: {$result['inserted']}\n";
echo "Skipped:  {$result['skipped']}\n";
