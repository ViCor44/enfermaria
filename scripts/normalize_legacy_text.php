<?php
declare(strict_types=1);

use App\Core\Database;
use App\Helpers\Text;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

$args = $_SERVER['argv'] ?? [];
$apply = in_array('--apply', $args, true);
$includeInternal = !in_array('--skip-internal', $args, true);

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table'
    );
    $stmt->execute([':table' => $table]);
    return (int)$stmt->fetchColumn() > 0;
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM information_schema.columns
         WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column'
    );
    $stmt->execute([
        ':table' => $table,
        ':column' => $column,
    ]);
    return (int)$stmt->fetchColumn() > 0;
}

/**
 * @param array<int, array{table:string,column:string}> $references
 * @return array{renamed:int, merged:int, skipped:int, refUpdates:int}
 */
function normalizeLookupTable(
    PDO $pdo,
    string $table,
    string $idCol,
    string $nameCol,
    array $references
): array {
    if (!tableExists($pdo, $table)) {
        echo "[SKIP] Table {$table} nao existe.\n";
        return ['renamed' => 0, 'merged' => 0, 'skipped' => 0, 'refUpdates' => 0];
    }

    $sql = "SELECT {$idCol}, {$nameCol} FROM {$table} ORDER BY {$idCol} ASC";
    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $canonicalByKey = [];
    $renameById = [];
    $mergeByFromId = [];
    $skipCount = 0;

    foreach ($rows as $row) {
        $id = (int)$row[$idCol];
        $name = (string)$row[$nameCol];
        $normalized = Text::toPortugueseTitleCase($name);

        if ($normalized === '') {
            $skipCount++;
            continue;
        }

        $key = mb_strtolower($normalized, 'UTF-8');

        if (!isset($canonicalByKey[$key])) {
            $canonicalByKey[$key] = $id;
            if ($normalized !== $name) {
                $renameById[$id] = $normalized;
            }
            continue;
        }

        $toId = (int)$canonicalByKey[$key];
        if ($id !== $toId) {
            $mergeByFromId[$id] = $toId;
        }
    }

    $refUpdates = 0;
    foreach ($mergeByFromId as $fromId => $toId) {
        foreach ($references as $ref) {
            $refTable = $ref['table'];
            $refColumn = $ref['column'];

            if (!tableExists($pdo, $refTable) || !columnExists($pdo, $refTable, $refColumn)) {
                continue;
            }

            $up = $pdo->prepare("UPDATE {$refTable} SET {$refColumn} = :to WHERE {$refColumn} = :from");
            $up->execute([':to' => $toId, ':from' => $fromId]);
            $refUpdates += $up->rowCount();
        }

        if ($table === 'locations' && columnExists($pdo, 'locations', 'active')) {
            $act = $pdo->prepare('UPDATE locations SET active = GREATEST(active, (SELECT active FROM (SELECT active FROM locations WHERE id = :from) tmp)) WHERE id = :to');
            $act->execute([':from' => $fromId, ':to' => $toId]);
        }

        $del = $pdo->prepare("DELETE FROM {$table} WHERE {$idCol} = :id");
        $del->execute([':id' => $fromId]);
        unset($renameById[$fromId]);
    }

    $renamed = 0;
    foreach ($renameById as $id => $newName) {
        $upd = $pdo->prepare("UPDATE {$table} SET {$nameCol} = :name WHERE {$idCol} = :id");
        $upd->execute([':name' => $newName, ':id' => $id]);
        $renamed += $upd->rowCount();
    }

    return [
        'renamed' => $renamed,
        'merged' => count($mergeByFromId),
        'skipped' => $skipCount,
        'refUpdates' => $refUpdates,
    ];
}

/**
 * @return array{updated:int, skipped:int}
 */
function normalizeInternalRecordTreatment(PDO $pdo): array
{
    if (!tableExists($pdo, 'internal_records') || !columnExists($pdo, 'internal_records', 'treatment')) {
        echo "[SKIP] internal_records.treatment nao existe.\n";
        return ['updated' => 0, 'skipped' => 0];
    }

    $idColumn = columnExists($pdo, 'internal_records', 'id') ? 'id' : null;
    if ($idColumn === null) {
        echo "[SKIP] internal_records sem coluna id para atualizacao segura.\n";
        return ['updated' => 0, 'skipped' => 0];
    }

    $rows = $pdo->query("SELECT {$idColumn}, treatment FROM internal_records")->fetchAll(PDO::FETCH_ASSOC);

    $updated = 0;
    $skipped = 0;

    $stmt = $pdo->prepare("UPDATE internal_records SET treatment = :treatment WHERE {$idColumn} = :id");

    foreach ($rows as $row) {
        $id = (int)$row[$idColumn];
        $current = (string)($row['treatment'] ?? '');
        $normalized = Text::toPortugueseTitleCase($current);

        if ($normalized === '') {
            $skipped++;
            continue;
        }

        if ($normalized !== $current) {
            $stmt->execute([
                ':treatment' => $normalized,
                ':id' => $id,
            ]);
            $updated += $stmt->rowCount();
        }
    }

    return ['updated' => $updated, 'skipped' => $skipped];
}

$pdo = Database::getConnection();

echo "=== Normalizacao de dados antigos (title case PT) ===\n";
echo $apply
    ? "Modo: APPLY (com commit)\n"
    : "Modo: DRY-RUN (rollback automatico; use --apply para gravar)\n";

try {
    $pdo->beginTransaction();

    $incidentTypeStats = normalizeLookupTable(
        $pdo,
        'incident_types',
        'id',
        'name',
        [
            ['table' => 'incidents', 'column' => 'incident_type_id'],
        ]
    );

    $locationStats = normalizeLookupTable(
        $pdo,
        'locations',
        'id',
        'name',
        [
            ['table' => 'incidents', 'column' => 'location_id'],
            ['table' => 'internal_records', 'column' => 'location_id'],
        ]
    );

    $treatmentTypeStats = normalizeLookupTable(
        $pdo,
        'treatment_types',
        'id',
        'name',
        [
            ['table' => 'treatments', 'column' => 'treatment_type_id'],
        ]
    );

    $internalStats = ['updated' => 0, 'skipped' => 0];
    if ($includeInternal) {
        $internalStats = normalizeInternalRecordTreatment($pdo);
    }

    echo "\nResumo:\n";
    echo "- incident_types: renomeados={$incidentTypeStats['renamed']}, merges={$incidentTypeStats['merged']}, refs={$incidentTypeStats['refUpdates']}, ignorados={$incidentTypeStats['skipped']}\n";
    echo "- locations: renomeados={$locationStats['renamed']}, merges={$locationStats['merged']}, refs={$locationStats['refUpdates']}, ignorados={$locationStats['skipped']}\n";
    echo "- treatment_types: renomeados={$treatmentTypeStats['renamed']}, merges={$treatmentTypeStats['merged']}, refs={$treatmentTypeStats['refUpdates']}, ignorados={$treatmentTypeStats['skipped']}\n";
    if ($includeInternal) {
        echo "- internal_records.treatment: atualizados={$internalStats['updated']}, ignorados={$internalStats['skipped']}\n";
    }

    if ($apply) {
        $pdo->commit();
        echo "\nOK: alteracoes gravadas.\n";
    } else {
        $pdo->rollBack();
        echo "\nDry-run concluido. Nenhuma alteracao foi gravada.\n";
    }

    exit(0);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Erro: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
