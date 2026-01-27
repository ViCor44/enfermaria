<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\InternalRecord;
use App\Models\Location;

class AdminInternalController
{
    protected string $baseUrl = '/enfermaria/public/index.php';

    public function index(): void
    {
        Auth::requireRole(['Administrador', 'Manager']);

        $opts = [
            'fromDate'   => $_GET['from'] ?? null,
            'toDate'     => $_GET['to'] ?? null,
            'locationId' => isset($_GET['location_id']) ? (int)$_GET['location_id'] : null,
        ];

        $records = InternalRecord::search($opts);
        $locations = Location::all();

        require __DIR__ . '/../Views/admin/internal_list.php';
    }
}
