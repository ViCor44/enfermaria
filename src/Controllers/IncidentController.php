<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Incident;
use App\Models\Location;
use App\Models\Treatment;
use App\Models\Patient;

class IncidentController
{
    private string $baseUrl = '/enfermaria/public/index.php';

    public function create(): void
    {
        Auth::requireRole(['Enfermeiro', 'Administrador']);

        $types          = Incident::getTypes();
        $locations      = Location::allActive();
        $treatmentTypes = Treatment::getTypes();

        // ID do tipo "Enviado para hospital"
        $hospitalTreatmentTypeId = Treatment::getHospitalTransferTypeId();

        require __DIR__ . '/../Views/incidents/create.php';
    }

public function store(): void
{
    Auth::requireRole(['Enfermeiro']);

    $user   = Auth::user();
    $userId = (int)$user['id'];

    /* -------------------- INCIDENTE -------------------- */
    $incidentTypeId    = ($_POST['incident_type_id'] ?? '') !== '' ? (int)$_POST['incident_type_id'] : 0;
    $incidentTypeInput = trim($_POST['incident_type_input'] ?? '');

    $locationId    = ($_POST['location_id'] ?? '') !== '' ? (int)$_POST['location_id'] : 0;
    $locationInput = trim($_POST['location_input'] ?? '');

    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');

    /* -------------------- PACIENTE (BÁSICO) -------------------- */
    $patientName   = trim($_POST['patient_name'] ?? '');
    $patientAge    = ($_POST['patient_age'] ?? '') !== '' ? (int)$_POST['patient_age'] : null;
    $patientGender = trim($_POST['patient_gender'] ?? '') ?: null;

    $description = trim($_POST['description'] ?? '') ?: null;

    /* -------------------- TRATAMENTO -------------------- */
    $treatmentTypeId    = ($_POST['treatment_type_id'] ?? '') !== '' ? (int)$_POST['treatment_type_id'] : 0;
    $treatmentTypeInput = trim($_POST['treatment_type_input'] ?? '');
    $treatmentStatus    = in_array($_POST['treatment_status'] ?? '', ['em_curso','concluido'], true)
                            ? $_POST['treatment_status']
                            : 'em_curso';
    $treatmentNotes     = trim($_POST['treatment_notes'] ?? '') ?: null;

    /* -------------------- DADOS HOSPITAL -------------------- */
    $patientNationality = trim($_POST['patient_nationality'] ?? '') ?: null;
    $patientAddress     = trim($_POST['patient_address'] ?? '') ?: null;
    $patientPostalCode  = trim($_POST['patient_postal_code'] ?? '') ?: null;
    $patientCity        = trim($_POST['patient_city'] ?? '') ?: null;
    $patientPhone       = trim($_POST['patient_phone'] ?? '') ?: null;
    $patientDob         = trim($_POST['patient_dob'] ?? '') ?: null;
    $patientIdType      = trim($_POST['patient_id_type'] ?? '') ?: null;
    $patientIdNumber    = trim($_POST['patient_id_number'] ?? '') ?: null;

    $patientRefusedHospital = isset($_POST['patient_refused_hospital']) ? 1 : 0;

    /* -------------------- VALIDAÇÕES -------------------- */

    if ($incidentTypeId <= 0 && $incidentTypeInput === '') {
        $_SESSION['error'] = 'Tipo de acidente obrigatório.';
        header('Location: '.$this->baseUrl.'?route=incidents_new'); exit;
    }

    if ($locationId <= 0 && $locationInput === '') {
        $_SESSION['error'] = 'Local obrigatório.';
        header('Location: '.$this->baseUrl.'?route=incidents_new'); exit;
    }

    if ($date === '' || $time === '') {
        $_SESSION['error'] = 'Data e hora obrigatórias.';
        header('Location: '.$this->baseUrl.'?route=incidents_new'); exit;
    }

    /* -------------------- NORMALIZAÇÃO -------------------- */

    if ($incidentTypeId <= 0) {
        $incidentTypeId = Incident::createTypeIfNotExists($incidentTypeInput);
    }

    if ($locationId <= 0) {
        $locationId = Location::createIfNotExists($locationInput);
    }

    if ($treatmentTypeId <= 0 && $treatmentTypeInput !== '') {
        $treatmentTypeId = Treatment::createTypeIfNotExists($treatmentTypeInput);
    }

    $occurredAt = $date.' '.$time.':00';

    $hospitalTypeId = Treatment::getHospitalTransferTypeId();

    $isHospitalTreatment =
        ($hospitalTypeId && $treatmentTypeId === (int)$hospitalTypeId) ||
        strcasecmp($treatmentTypeInput, 'Enviado para hospital') === 0;

    $pdo = Database::getConnection();

    try {

        $pdo->beginTransaction();

        /* -------------------- PATIENT BÁSICO -------------------- */

        $patientId = Patient::createBasic(
            $patientName,
            $patientAge,
            $patientGender
        );

        /* -------------------- INCIDENT -------------------- */

        $stmt = $pdo->prepare("
            INSERT INTO incidents
            (user_id, incident_type_id, location_id, occurred_at, patient_id, description)
            VALUES
            (:user_id, :type, :loc, :occurred, :patient_id, :descr)
        ");

        $stmt->execute([
            ':user_id'    => $userId,
            ':type'       => $incidentTypeId,
            ':loc'        => $locationId,
            ':occurred'   => $occurredAt,
            ':patient_id' => $patientId,
            ':descr'      => $description,
        ]);

        $incidentId = (int)$pdo->lastInsertId();

        /* -------------------- TRATAMENTO -------------------- */

        if ($treatmentTypeId > 0) {

            Treatment::create([
                'incident_id'       => $incidentId,
                'user_id'           => $userId,
                'treatment_type_id' => $treatmentTypeId,
                'status'            => $treatmentStatus,
                'notes'             => $treatmentNotes,
            ]);

            /* -------------------- UPDATE PATIENT HOSPITAL -------------------- */

            if ($isHospitalTreatment) {

                Patient::updateHospitalData(
                    $patientId,
                    $patientNationality,
                    $patientAddress,
                    $patientPostalCode,
                    $patientCity,
                    $patientPhone,
                    $patientDob,
                    $patientIdType,
                    $patientIdNumber,
                    $patientRefusedHospital
                );

            }

        }

        $pdo->commit();

        $_SESSION['success'] = 'Acidente registado com sucesso.';
        header('Location: '.$this->baseUrl.'?route=admin_incidents');
        exit;

    } catch (\Throwable $e) {

    $pdo->rollBack();

    die($e->getMessage());

}
}
    public function insuranceTerm()
    {
        Auth::requireRole(['Administrador','Enfermeiro']);

        $id = (int)($_GET['id'] ?? 0);

        $incident = Incident::findWithDetailsForAdmin($id);

        if (!$incident) {
            die('Ocorrência não encontrada');
        }

        require_once __DIR__.'/../../vendor/autoload.php';

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new \Dompdf\Dompdf($options);

        ob_start();
        require __DIR__.'/../Views/incidents/insurance_term_pdf.php';
        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        $dompdf->stream(
            "termo-seguro-{$incident['id']}.pdf",
            ["Attachment" => false] // false = abre no browser
        );

        exit;
    }

}
