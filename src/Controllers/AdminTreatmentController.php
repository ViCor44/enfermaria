<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Treatment;
use App\Models\Incident;
use App\Models\Location;

class AdminTreatmentController
{
    private string $baseUrl = '/enfermaria/public/index.php';
    private static array $columnExistsCache = [];

    public function index(): void
    {
        // quem pode ver: Admin e Manager (e podes adicionar Enfermeiro se quiseres)
        Auth::requireRole(['Administrador', 'Manager', 'Enfermeiro']);

        // filtros
        $status = $_GET['status'] ?? '';
        $from   = $_GET['from'] ?? '';
        $to     = $_GET['to'] ?? '';
        $locationId = isset($_GET['location_id']) ? (int)$_GET['location_id'] : 0;

        $locations = Location::allActive();

        // devolve tratamentos com join a incident e user
        $treatments = Treatment::search([
            'status' => $status !== '' ? $status : null,
            'fromDate' => $from !== '' ? $from : null,
            'toDate'   => $to   !== '' ? $to   : null,
            'locationId' => $locationId > 0 ? $locationId : null,
        ]);

        require __DIR__ . '/../Views/admin/treatments_list.php';
    }

    public function updateNotes(): void
    {
        header('Content-Type: application/json');

        try {
            Auth::requireRole(['Administrador', 'Manager', 'Enfermeiro']);

            $treatmentId = isset($_POST['treatment_id']) ? (int)$_POST['treatment_id'] : 0;
            $notes       = trim((string)($_POST['notes'] ?? ''));
            $userId      = (int)($_SESSION['user_id'] ?? 0);
            $userName    = (string)($_SESSION['user_name'] ?? 'Utilizador');

            if ($treatmentId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'invalid_treatment']);
                return;
            }

            if ($notes === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'empty_notes']);
                return;
            }

            $pdo = \App\Core\Database::getConnection();
            $checkStmt = $pdo->prepare('SELECT id FROM treatments WHERE id = :id LIMIT 1');
            $checkStmt->execute([':id' => $treatmentId]);
            if (!$checkStmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'treatment_not_found']);
                return;
            }

            $setParts = ['notes = :notes'];
            $params = [
                ':notes' => $notes,
                ':id'    => $treatmentId,
            ];

            $hasEditedBy = self::columnExists($pdo, 'treatments', 'notes_edited_by');
            $hasEditedAt = self::columnExists($pdo, 'treatments', 'notes_edited_at');

            if ($hasEditedBy) {
                $setParts[] = 'notes_edited_by = :user_id';
                $params[':user_id'] = $userId > 0 ? $userId : null;
            }

            if ($hasEditedAt) {
                $setParts[] = 'notes_edited_at = NOW()';
            }

            $sql = 'UPDATE treatments SET ' . implode(', ', $setParts) . ' WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $ok = $stmt->execute($params);

            if (!$ok) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'update_failed']);
                return;
            }

            echo json_encode([
                'success'     => true,
                'notes'       => $notes,
                'editinfo'    => 'Editado por ' . $userName . ' em ' . date('d/m/Y H:i'),
                'edited_by'   => $userName,
                'edited_at'   => date('d/m/Y H:i'),
                'treatmentId' => $treatmentId,
            ]);
        } catch (\Throwable $e) {
            error_log('[AdminTreatmentController::updateNotes] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'server_error']);
        }
    }

    private static function columnExists(\PDO $pdo, string $table, string $column): bool
    {
        $cacheKey = $table . '.' . $column;
        if (array_key_exists($cacheKey, self::$columnExistsCache)) {
            return self::$columnExistsCache[$cacheKey];
        }

        $stmt = $pdo->prepare("SHOW COLUMNS FROM {$table} LIKE :column");
        $stmt->execute([':column' => $column]);
        $exists = (bool)$stmt->fetch();
        self::$columnExistsCache[$cacheKey] = $exists;

        return $exists;
    }
}
