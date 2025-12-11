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
        Auth::requireRole(['Enfermeiro']);

        $types          = Incident::getTypes();
        $locations      = Location::allActive();
        $treatmentTypes = Treatment::getTypes();

        // ID do tipo "Enviado para hospital" (se existir)
        $hospitalTreatmentTypeId = Treatment::getHospitalTransferTypeId();

        require __DIR__ . '/../Views/incidents/create.php';
    }

    public function store(): void
    {
        Auth::requireRole(['Enfermeiro']);

        $user   = Auth::user();
        $userId = (int)$user['id'];

        $incidentTypeId = (int)($_POST['incident_type_id'] ?? 0);
        $locationId     = (int)($_POST['location_id'] ?? 0);
        $newLocation    = trim($_POST['new_location'] ?? '');
        $date           = trim($_POST['date'] ?? '');
        $time           = trim($_POST['time'] ?? '');
        $patientAge     = $_POST['patient_age'] !== '' ? (int)$_POST['patient_age'] : null;
        $patientGender  = $_POST['patient_gender'] ?? null;
        $description    = trim($_POST['description'] ?? '');

        if ($incidentTypeId <= 0 || $date === '' || $time === '') {
            $_SESSION['error'] = 'Preencha os campos obrigatórios (tipo, data e hora).';
            header('Location: ' . $this->baseUrl . '?route=incidents_new');
            exit;
        }

        if ($locationId <= 0 && $newLocation === '') {
            $_SESSION['error'] = 'Selecione um local ou indique um novo.';
            header('Location: ' . $this->baseUrl . '?route=incidents_new');
            exit;
        }

        if ($newLocation !== '') {
            $locationId = Location::create($newLocation);
        }

        $occurredAt = $date . ' ' . $time . ':00';

        // Dados do bloco de tratamento
        $treatmentTypeId = (int)($_POST['treatment_type_id'] ?? 0);
        $treatmentStatus = $_POST['treatment_status'] ?? 'em_curso';
        $treatmentNotes  = trim($_POST['treatment_notes'] ?? '');

        // Dados do paciente (apenas usados se o tratamento for "Enviado para hospital")
        $patientName        = trim($_POST['patient_name'] ?? '');
        $patientNationality = trim($_POST['patient_nationality'] ?? '');
        $patientHotel       = trim($_POST['patient_hotel'] ?? '');
        $patientRoom        = trim($_POST['patient_room'] ?? '');

        $hospitalTypeId = Treatment::getHospitalTransferTypeId();
        $isHospitalTreatment = $hospitalTypeId && $treatmentTypeId === $hospitalTypeId;

        // Se é envio para hospital, o nome do paciente torna-se obrigatório
        if ($isHospitalTreatment && $patientName === '') {
            $_SESSION['error'] = 'Para tratamento "Enviado para hospital" é obrigatório indicar o nome do utente.';
            header('Location: ' . $this->baseUrl . '?route=incidents_new');
            exit;
        }

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            // Criar incidente
            $stmt = $pdo->prepare("
                INSERT INTO incidents
                    (user_id, incident_type_id, location_id, occurred_at, patient_age, patient_gender, description)
                VALUES
                    (:user_id, :incident_type_id, :location_id, :occurred_at, :patient_age, :patient_gender, :description)
            ");
            $stmt->execute([
                ':user_id'          => $userId,
                ':incident_type_id' => $incidentTypeId,
                ':location_id'      => $locationId,
                ':occurred_at'      => $occurredAt,
                ':patient_age'      => $patientAge,
                ':patient_gender'   => $patientGender ?: null,
                ':description'      => $description,
            ]);

            $incidentId = (int)$pdo->lastInsertId();

            // Criar tratamento (se preenchido)
            if ($treatmentTypeId > 0) {
                Treatment::create([
                    'incident_id'       => $incidentId,
                    'user_id'           => $userId,
                    'treatment_type_id' => $treatmentTypeId,
                    'status'            => in_array($treatmentStatus, ['em_curso', 'concluido'], true)
                                            ? $treatmentStatus
                                            : 'em_curso',
                    'notes'             => $treatmentNotes ?: null,
                ]);

                // Se for "Enviado para hospital", criar ficha de paciente
                if ($isHospitalTreatment) {
                    Patient::createForIncident(
                        $incidentId,
                        $patientName,
                        $patientNationality ?: null,
                        $patientHotel ?: null,
                        $patientRoom ?: null
                    );
                }
            }

            $pdo->commit();

            $_SESSION['success'] =
                'Incidente registado com sucesso.' .
                ($treatmentTypeId > 0 ? ' Tratamento também registado.' : '') .
                ($isHospitalTreatment ? ' Utente marcado como enviado para hospital.' : '');

            header('Location: ' . $this->baseUrl . '?route=incidents_my');
            exit;

        } catch (\Throwable $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'Erro ao guardar incidente. Tente novamente.';
            header('Location: ' . $this->baseUrl . '?route=incidents_new');
            exit;
        }
    }


    public function myIncidents(): void
    {
        Auth::requireRole(['Enfermeiro']);

        $user   = Auth::user();
        $userId = (int)$user['id'];

        $incidents = Incident::listByUser($userId);

        require __DIR__ . '/../Views/incidents/my_list.php';
    }
}
