<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Incident;
use App\Models\Treatment;

class AdminStatsController
{
    public function index(): void
    {
        Auth::requireAdmin();

        $ageStats = Incident::statsByAge();
        $genderStats = Incident::statsByGender();
        $locationStats = Incident::statsByLocation();
        $typeStats = Incident::statsByIncidentType();
        $treatmentStats = Treatment::statsByType();

        require __DIR__ . '/../Views/admin/stats.php';
    }
}
