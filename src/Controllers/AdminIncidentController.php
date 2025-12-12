<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Incident;
use App\Models\Location;

class AdminIncidentController
{
    private string $baseUrl = '/enfermaria/public/index.php';

    public function index(): void
    {
        Auth::requireRole(['Administrador', 'Manager', 'Enfermeiro']);

        $fromDate   = $_GET['from'] ?? '';
        $toDate     = $_GET['to'] ?? '';
        $locationId = isset($_GET['location_id']) ? (int)$_GET['location_id'] : 0;
        $episode    = isset($_GET['episode']) ? trim($_GET['episode']) : '';

        $locations  = \App\Models\Location::allActive();

        $incidents = \App\Models\Incident::search([
            'fromDate'   => $fromDate !== '' ? $fromDate : null,
            'toDate'     => $toDate !== '' ? $toDate : null,
            'locationId' => $locationId > 0 ? $locationId : null,
            'episode'    => $episode !== '' ? $episode : null,
        ]);

        require __DIR__ . '/../Views/admin/incidents_list.php';
    }

    public function show(): void
    {
        Auth::requireRole(['Administrador', 'Manager', 'Enfermeiro']);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'Acidente inválido.';
            header('Location: ' . $this->baseUrl . '?route=admin_incidents');
            exit;
        }

        $incident = Incident::findWithDetailsForAdmin($id);

        if (!$incident) {
            $_SESSION['error'] = 'Acidente não encontrado.';
            header('Location: ' . $this->baseUrl . '?route=admin_incidents');
            exit;
        }

        $treatments = Incident::getTreatmentsForIncident($id);
        $role          = $_SESSION['role'] ?? '';
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);

        $canSeePatient = false;

        if ($role === 'Administrador') {
            // Admin vê sempre
            $canSeePatient = true;

        } elseif ($role === 'Enfermeiro') {
            // Enfermeiro só vê se tiver tratado este Acidente
            foreach ($treatments as $tr) {
                if ((int)$tr['user_id'] === $currentUserId) {
                    $canSeePatient = true;
                    break;
                }
            }
        }

        require __DIR__ . '/../Views/admin/incidents_detail.php';
    }

}
