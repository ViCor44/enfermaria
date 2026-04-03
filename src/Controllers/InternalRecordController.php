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
        Auth::requireRole(['Enfermeiro', 'Administrador']);

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
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');

        $locationId    = ($_POST['location_id'] ?? '') !== '' ? (int)$_POST['location_id'] : null;
        $locationInput = trim($_POST['location_input'] ?? '');

        $patientAge    = ($_POST['patient_age'] ?? '') !== '' ? (int)$_POST['patient_age'] : null;
        $patientGender = trim($_POST['patient_gender'] ?? '') ?: null;

        $treatment = Text::toPortugueseTitleCase((string)($_POST['treatment'] ?? ''));
        $description = trim($_POST['description'] ?? '');

        if ($treatment !== '') {
            // Mantem os tratamentos escritos no registo interno reutilizaveis nas listas.
            Treatment::createTypeIfNotExists($treatment);
        }

        /* -------------------- VALIDAÇÕES -------------------- */
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
                (user_id, occurred_at, location_id, patient_age, patient_gender, treatment, description)
                VALUES
                (:user_id, :occurred_at, :location_id, :age, :gender, :treatment, :descr)
            ");

            $stmt->execute([
                ':user_id'     => $userId,
                ':occurred_at' => $occurredAt,
                ':location_id' => $locationId,
                ':age'         => $patientAge,
                ':gender'      => $patientGender,
                ':treatment'  => $treatment,
                ':descr'       => $description,
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
