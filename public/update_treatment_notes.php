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

require_once __DIR__ . '/../src/Core/Database.php';
$db = Database::getInstance();

// Atualizar notas, user e data/hora
$sql = "UPDATE treatments SET notes = ?, notes_edited_by = ?, notes_edited_at = NOW() WHERE id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$notes, $userId, $treatmentId]);

// Buscar nome do utilizador
$sqlUser = "SELECT name FROM users WHERE id = ?";
$stmtUser = $db->prepare($sqlUser);
$stmtUser->execute([$userId]);
$userName = $stmtUser->fetchColumn() ?: 'Utilizador';

$editInfo = 'Editado por ' . htmlspecialchars($userName) . ' em ' . date('Y-m-d H:i');

echo json_encode([
    'success' => true,
    'editinfo' => $editInfo,
    'notes' => $notes
]);
