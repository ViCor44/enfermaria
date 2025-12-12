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
}
