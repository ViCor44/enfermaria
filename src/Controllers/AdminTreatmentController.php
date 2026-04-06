<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Treatment;
use App\Models\Incident;
use App\Models\Location;

class AdminTreatmentController
{
    private string $baseUrl = '/enfermaria/public/index.php';

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
        ob_start();
        header('Content-Type: application/json');

        try {
            Auth::requireRole(['Administrador', 'Manager', 'Enfermeiro']);

            $treatmentId = isset($_POST['treatment_id']) ? (int)$_POST['treatment_id'] : 0;
            $notes       = trim((string)($_POST['notes'] ?? ''));
            $userId      = (int)($_SESSION['user_id'] ?? 0);
            $userName    = (string)($_SESSION['user_name'] ?? 'Utilizador');

            if ($treatmentId <= 0) {
                echo json_encode(['success' => false, 'error' => 'invalid_treatment']);
                return;
            }

            if ($notes === '') {
                echo json_encode(['success' => false, 'error' => 'empty_notes']);
                return;
            }

            $pdo = \App\Core\Database::getConnection();
            $stmt = $pdo->prepare(
                "UPDATE treatments
                 SET notes = :notes, notes_edited_by = :user_id, notes_edited_at = NOW()
                 WHERE id = :id"
            );

            $ok = $stmt->execute([
                ':notes'   => $notes,
                ':user_id' => $userId,
                ':id'      => $treatmentId,
            ]);

            if (!$ok) {
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
            if (ob_get_length() !== false && ob_get_length() > 0) {
                ob_clean();
            }
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'server_error']);
        } finally {
            if (ob_get_level() > 0) {
                ob_end_flush();
            }
        }
    }
}
