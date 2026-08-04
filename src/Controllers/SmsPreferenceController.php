<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;

final class SmsPreferenceController
{
    private string $baseUrl = '/enfermaria/public/index.php';

    public function edit(): void
    {
        Auth::requireLogin();
        $stmt = Database::getConnection()->prepare('SELECT full_name, phone, receive_sms_notifications FROM users WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([(int)$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if (!$user) {
            http_response_code(404);
            exit('Utilizador não encontrado.');
        }
        require __DIR__ . '/../Views/account/sms_preferences.php';
    }

    public function update(): void
    {
        Auth::requireLogin();
        if (!hash_equals((string)($_SESSION['sms_preferences_csrf'] ?? ''), (string)($_POST['csrf_token'] ?? ''))) {
            $_SESSION['error'] = 'Pedido inválido. Tente novamente.';
            header('Location: ' . $this->baseUrl . '?route=sms_preferences');
            exit;
        }
        $enabled = isset($_POST['receive_sms_notifications']) ? 1 : 0;
        $stmt = Database::getConnection()->prepare('UPDATE users SET receive_sms_notifications = ? WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$enabled, (int)$_SESSION['user_id']]);
        $_SESSION['success'] = $enabled ? 'Notificações por SMS ativadas.' : 'Notificações por SMS desativadas.';
        header('Location: ' . $this->baseUrl . '?route=sms_preferences');
        exit;
    }
}
