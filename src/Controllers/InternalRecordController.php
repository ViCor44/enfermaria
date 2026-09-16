<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Helpers\Text;
use App\Models\Location;
use App\Models\Treatment;

class InternalRecordController
{
    protected string $baseUrl = '/enfermaria/public/index.php';

    /* =====================================================
     * Mostrar formulário
     * ===================================================== */
    public function create(): void
    {
        Auth::requireRole(['Enfermeiro']);

        $locations = Location::all();
        $treatmentTypes = Treatment::getTypes();

        require __DIR__ . '/../Views/internal/create.php';
    }

    /* =====================================================
     * Guardar registo interno
     * ===================================================== */
    public function store(): void
    {
        Auth::requireRole(['Enfermeiro']);

        $user   = Auth::user();
        $userId = (int)$user['id'];

        /* -------------------- INPUT -------------------- */

        $firstName = Text::toPortugueseTitleCase((string)($_POST['first_name'] ?? ''));
        $lastName = Text::toPortugueseTitleCase((string)($_POST['last_name'] ?? ''));
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');

        $locationId    = ($_POST['location_id'] ?? '') !== '' ? (int)$_POST['location_id'] : null;
        $locationInput = trim($_POST['location_input'] ?? '');

        $patientAge    = ($_POST['patient_age'] ?? '') !== '' ? (int)$_POST['patient_age'] : null;
        $patientGender = trim($_POST['patient_gender'] ?? '') ?: null;
        $isEmployee    = isset($_POST['is_employee']) ? 1 : 0;

        $treatment = trim((string)($_POST['treatment'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $bodyMarksJson = trim((string)($_POST['body_marks'] ?? '[]'));
        $bodyMarksInput = json_decode($bodyMarksJson, true);

        if (!is_array($bodyMarksInput) || count($bodyMarksInput) > 20) {
            $_SESSION['error'] = 'As marcações corporais são inválidas.';
            header('Location: '.$this->baseUrl.'?route=internal_new');
            exit;
        }

        $bodyMarks = [];
        $validBodyMarkTypes = ['contusion', 'wound', 'burn', 'pain', 'insect_bite', 'epistaxis', 'other'];
        foreach ($bodyMarksInput as $mark) {
            if (
                !is_array($mark)
                || !in_array($mark['view'] ?? null, ['front', 'back'], true)
            || !in_array($mark['type'] ?? null, $validBodyMarkTypes, true)
                || !is_numeric($mark['x'] ?? null)
                || !is_numeric($mark['y'] ?? null)
            ) {
                $_SESSION['error'] = 'As marcações corporais são inválidas.';
                header('Location: '.$this->baseUrl.'?route=internal_new');
                exit;
            }

            $x = round((float)$mark['x'], 2);
            $y = round((float)$mark['y'], 2);
            if ($x < 0 || $x > 100 || $y < 0 || $y > 100) {
                $_SESSION['error'] = 'As marcações corporais são inválidas.';
                header('Location: '.$this->baseUrl.'?route=internal_new');
                exit;
            }

            $bodyMarks[] = [
                'view' => $mark['view'],
                'type' => $mark['type'],
                'x' => $x,
                'y' => $y,
            ];
        }

        $bodyMarksJson = $bodyMarks === []
            ? null
            : json_encode($bodyMarks, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        if ($treatment !== '') {
            $validTreatments = array_map(
                static fn (array $t): string => (string)$t['name'],
                Treatment::getTypes()
            );
            if (!in_array($treatment, $validTreatments, true)) {
                $_SESSION['error'] = 'Tratamento inválido. Escolha um dos tratamentos existentes.';
                header('Location: '.$this->baseUrl.'?route=internal_new');
                exit;
            }
        }

        /* -------------------- VALIDAÇÕES -------------------- */

        if ($firstName === '' || $lastName === '') {
            $_SESSION['error'] = 'Primeiro e último nome são obrigatórios.';
            header('Location: '.$this->baseUrl.'?route=internal_new');
            exit;
        }
        if ($date === '' || $time === '') {
            $_SESSION['error'] = 'Data e hora são obrigatórias.';
            header('Location: '.$this->baseUrl.'?route=internal_new');
            exit;
        }
        if ($description === '') {
            $_SESSION['error'] = 'Descrição obrigatória.';
            header('Location: '.$this->baseUrl.'?route=internal_new');
            exit;
        }

        /* -------------------- LOCAL -------------------- */
        if (!$locationId && $locationInput !== '') {
            $locationId = Location::createIfNotExists($locationInput);
        }

        $occurredAt = $date.' '.$time.':00';

        /* -------------------- INSERT -------------------- */
        $pdo = Database::getConnection();


        try {
            $stmt = $pdo->prepare("
                INSERT INTO internal_records
                (user_id, first_name, last_name, is_employee, occurred_at, location_id, patient_age, patient_gender, treatment, description, body_marks)
                VALUES
                (:user_id, :first_name, :last_name, :is_employee, :occurred_at, :location_id, :age, :gender, :treatment, :descr, :body_marks)
            ");

            $stmt->execute([
                ':user_id'     => $userId,
                ':first_name'  => $firstName,
                ':last_name'   => $lastName,
                ':is_employee' => $isEmployee,
                ':occurred_at' => $occurredAt,
                ':location_id' => $locationId,
                ':age'         => $patientAge,
                ':gender'      => $patientGender,
                ':treatment'   => $treatment,
                ':descr'       => $description,
                ':body_marks'  => $bodyMarksJson,
            ]);

            $_SESSION['success'] = 'Registo interno criado com sucesso.';
            header('Location: '.$this->baseUrl.'?route=dashboard');
            exit;

        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro ao guardar registo interno.';
            header('Location: '.$this->baseUrl.'?route=internal_new');
            exit;
        }
    }
}
