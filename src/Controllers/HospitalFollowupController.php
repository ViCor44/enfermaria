<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Incident;
use App\Models\HospitalFollowup;

class HospitalFollowupController
{
    public function create()
    {
        Auth::requireRole(['Administrador','Enfermeiro']);

        $incidentId = (int)($_GET['id'] ?? 0);

        $incident = Incident::find($incidentId);

        $followups = HospitalFollowup::findByIncident($incidentId);

        require __DIR__.'/../Views/admin/hospital_followup_form.php';
    }

    public function store()
    {
        Auth::requireRole(['Administrador','Enfermeiro']);

        $incidentId = (int)$_POST['incident_id'];

        $docPath = null;

        if (!empty($_FILES['document']['name'])) {
            $dir = __DIR__.'/../../public/uploads/hospital/';

            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $name = time().'_'.basename($_FILES['document']['name']);
            move_uploaded_file($_FILES['document']['tmp_name'], $dir.$name);

            $docPath = '/enfermaria/public/uploads/hospital/'.$name;
        }

        HospitalFollowup::create([
            'incident_id' => $incidentId,
            'went_to_hospital' => 1,
            'visit_date' => $_POST['visit_date'],
            'hospital_name' => trim($_POST['hospital_name']),
            'notes' => trim($_POST['notes']),
            'document_path' => $docPath,
            'created_by' => $_SESSION['user_id'],
        ]);

        header("Location: index.php?route=admin_incident_detail&id=$incidentId");
        exit;
    }
}
