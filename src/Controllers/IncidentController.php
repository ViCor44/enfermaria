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
        // Apenas enfermeiros podem registar incidentes
        Auth::requireRole(['Enfermeiro']);

        $user = Auth::user();
        $userId = (int)$user['id'];

        // ---------- Ler campos do formulário ----------
        // incident type: pode vir como id (hidden) ou texto (datalist input)
        $incidentTypeId    = isset($_POST['incident_type_id']) && $_POST['incident_type_id'] !== '' ? (int)$_POST['incident_type_id'] : 0;
        $incidentTypeInput = trim($_POST['incident_type_input'] ?? '');

        // location: id or text
        $locationId    = isset($_POST['location_id']) && $_POST['location_id'] !== '' ? (int)$_POST['location_id'] : 0;
        $locationInput = trim($_POST['location_input'] ?? '');

        // date/time
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');

        // patient basic fields stored on incident
        $patientAge    = $_POST['patient_age'] !== '' ? (int)$_POST['patient_age'] : null;
        $patientGender = trim($_POST['patient_gender'] ?? '');

        $description = trim($_POST['description'] ?? '');

        // treatment block
        $addTreatment = isset($_POST['treatment_type_input']) || (isset($_POST['treatment_type_id']) && $_POST['treatment_type_id'] !== '');
        $treatmentTypeId    = isset($_POST['treatment_type_id']) && $_POST['treatment_type_id'] !== '' ? (int)$_POST['treatment_type_id'] : 0;
        $treatmentTypeInput = trim($_POST['treatment_type_input'] ?? '');
        $treatmentStatus    = trim($_POST['treatment_status'] ?? 'em_curso');
        $treatmentNotes     = trim($_POST['treatment_notes'] ?? '');

        // patient details for hospital transfer (only used if treatment is hospital)
        $patientName        = trim($_POST['patient_name'] ?? '');
        $patientNationality = trim($_POST['patient_nationality'] ?? '');
        $patientAddress     = trim($_POST['patient_address'] ?? '');
        $patientPhone       = trim($_POST['patient_phone'] ?? '');
        $patientDob         = trim($_POST['patient_dob'] ?? '');
        $patientIdType      = trim($_POST['patient_id_type'] ?? '');
        $patientIdNumber    = trim($_POST['patient_id_number'] ?? '');

        // ---------- Validações mínimas ----------
        if ($incidentTypeId <= 0 && $incidentTypeInput === '') {
            $_SESSION['error'] = 'Tipo de incidente obrigatório.';
            header('Location: ' . $this->baseUrl . '?route=incidents_new');
            exit;
        }

        if ($locationId <= 0 && $locationInput === '') {
            $_SESSION['error'] = 'Local / Atração obrigatório.';
            header('Location: ' . $this->baseUrl . '?route=incidents_new');
            exit;
        }

        if ($date === '' || $time === '') {
            $_SESSION['error'] = 'Data e hora são obrigatórios.';
            header('Location: ' . $this->baseUrl . '?route=incidents_new');
            exit;
        }

        // Normalizar / criar tipos e local se vier texto (datalist)
        // 1) incident type
        if ($incidentTypeId <= 0 && $incidentTypeInput !== '') {
            // cria se não existir (método no model Incident::createTypeIfNotExists)
            $incidentTypeId = \App\Models\Incident::createTypeIfNotExists($incidentTypeInput);
        }

        // 2) location
        if ($locationId <= 0 && $locationInput !== '') {
            $locationId = \App\Models\Location::createIfNotExists($locationInput);
        }

        // 3) treatment type (se houver tratamento)
        if ($treatmentTypeId <= 0 && $treatmentTypeInput !== '') {
            $treatmentTypeId = \App\Models\Treatment::createTypeIfNotExists($treatmentTypeInput);
        }

        // após criação, validar que temos ids válidos
        if ($incidentTypeId <= 0) {
            $_SESSION['error'] = 'Erro ao determinar o tipo de incidente.';
            header('Location: ' . $this->baseUrl . '?route=incidents_new');
            exit;
        }
        if ($locationId <= 0) {
            $_SESSION['error'] = 'Erro ao determinar o local.';
            header('Location: ' . $this->baseUrl . '?route=incidents_new');
            exit;
        }

        // preparar occurred_at
        $occurredAt = $date . ' ' . $time . ':00';

        // Determinar se o tratamento seleccionado é "Enviado para hospital"
        // Usa o helper que procura o tipo com nome exato
        $hospitalTypeId = \App\Models\Treatment::getHospitalTransferTypeId(); // pode retornar null
        $isHospitalTreatment = ($treatmentTypeId > 0 && $hospitalTypeId && ((int)$treatmentTypeId === (int)$hospitalTypeId))
                            || (strcasecmp($treatmentTypeInput, 'Enviado para hospital') === 0);

        // Se é hospital transfer, valida dados obrigatórios do paciente
        if ($isHospitalTreatment) {
            if ($patientName === '') {
                $_SESSION['error'] = 'Nome do utente é obrigatório para envio ao hospital.';
                header('Location: ' . $this->baseUrl . '?route=incidents_new');
                exit;
            }
            // validar data de nascimento se preenchida
            if ($patientDob !== '') {
                $d = \DateTime::createFromFormat('Y-m-d', $patientDob);
                if (!$d || $d->format('Y-m-d') !== $patientDob) {
                    $_SESSION['error'] = 'Data de nascimento inválida (use AAAA-MM-DD).';
                    header('Location: ' . $this->baseUrl . '?route=incidents_new');
                    exit;
                }
            }
            // validar id_type se preenchido
            if ($patientIdType !== '' && !in_array($patientIdType, ['CC', 'Passaporte'], true)) {
                $_SESSION['error'] = 'Tipo de identificação inválido.';
                header('Location: ' . $this->baseUrl . '?route=incidents_new');
                exit;
            }
        }

        // ---------- Inserção em transacção ----------
        $pdo = \App\Core\Database::getConnection();

        try {
            $pdo->beginTransaction();

            // criar incidente
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
                ':patient_gender'   => $patientGender !== '' ? $patientGender : null,
                ':description'      => $description !== '' ? $description : null,
            ]);

            $incidentId = (int)$pdo->lastInsertId();

            // criar tratamento (se foi pedido)
            if ($treatmentTypeId > 0) {
                $treatmentData = [
                    'incident_id'       => $incidentId,
                    'user_id'           => $userId,
                    'treatment_type_id' => $treatmentTypeId,
                    'status'            => in_array($treatmentStatus, ['em_curso','concluido'], true) ? $treatmentStatus : 'em_curso',
                    'notes'             => $treatmentNotes !== '' ? $treatmentNotes : null,
                ];

                // usa o model para criar (assume Treatment::create existe)
                $treatmentId = \App\Models\Treatment::create($treatmentData);

                // se for envio para hospital, criar ficha de paciente ligada ao incidente
                if ($isHospitalTreatment) {
                    \App\Models\Patient::createForIncident(
                        $incidentId,
                        $patientName,
                        $patientNationality !== '' ? $patientNationality : null,
                        $patientAddress !== '' ? $patientAddress : null,
                        $patientPhone !== '' ? $patientPhone : null,
                        $patientDob !== '' ? $patientDob : null,
                        $patientIdType !== '' ? $patientIdType : null,
                        $patientIdNumber !== '' ? $patientIdNumber : null
                    );
                }
            }

            $pdo->commit();

            $_SESSION['success'] = 'Incidente registado com sucesso.' . ($treatmentTypeId > 0 ? ' Tratamento também registado.' : '') . ($isHospitalTreatment ? ' Utente enviado para hospital registado.' : '');
            header('Location: ' . $this->baseUrl . '?route=incidents_my');
            exit;

        } catch (\Throwable $e) {
            $pdo->rollBack();
            // Em dev podes fazer: $_SESSION['error'] = 'Erro: '.$e->getMessage();
            $_SESSION['error'] = 'Erro ao guardar o incidente. Tente novamente.';
            header('Location: ' . $this->baseUrl . '?route=incidents_new');
            exit;
        }
    }

    public function myIncidents(): void
    {
        Auth::requireRole(['Enfermeiro']);

        $user   = Auth::user();
        $userId = (int)$user['id'];

        $fromDate   = $_GET['from'] ?? '';
        $toDate     = $_GET['to'] ?? '';
        $locationId = isset($_GET['location_id']) ? (int)$_GET['location_id'] : 0;
        $episode    = isset($_GET['episode']) ? trim($_GET['episode']) : '';

        $locations = \App\Models\Location::allActive();

        $incidents = \App\Models\Incident::search([
            'fromDate'   => $fromDate !== '' ? $fromDate : null,
            'toDate'     => $toDate !== '' ? $toDate : null,
            'locationId' => $locationId > 0 ? $locationId : null,
            'userId'     => $userId,
            'episode'    => $episode !== '' ? $episode : null,
        ]);

        require __DIR__ . '/../Views/incidents/my_list.php';
    }

}
