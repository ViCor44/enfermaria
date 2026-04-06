<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'not_authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'invalid_method']);
    exit;
}

$treatmentId = isset($_POST['treatment_id']) ? (int)$_POST['treatment_id'] : 0;
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
$userId = (int)$_SESSION['user_id'];

if ($treatmentId <= 0 || $notes === '') {
    echo json_encode(['success' => false, 'error' => 'invalid_data']);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$db = \App\Core\Database::getConnection();

// Atualizar notas, user e data/hora
$stmt = $db->prepare("UPDATE treatments SET notes = ?, notes_edited_by = ?, notes_edited_at = NOW() WHERE id = ?");
$stmt->execute([$notes, $userId, $treatmentId]);

// Buscar nome do utilizador
$stmtUser = $db->prepare("SELECT full_name FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$userName = $stmtUser->fetchColumn() ?: 'Utilizador';

$editInfo = 'Editado por ' . htmlspecialchars($userName) . ' em ' . date('d/m/Y H:i');

echo json_encode([
    'success'  => true,
    'editinfo' => $editInfo,
    'notes'    => $notes
]);
