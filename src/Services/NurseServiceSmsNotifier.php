<?php
namespace App\Services;

use App\Core\Database;
use PDO;

final class NurseServiceSmsNotifier
{
    public static function notify(array $nurse): void
    {
        if (($nurse['role_name'] ?? '') !== 'Enfermeiro') return;
        $pdo = Database::getConnection();
        $recipients = $pdo->query("SELECT u.id,u.phone FROM users u INNER JOIN roles r ON r.id=u.role_id WHERE r.name IN ('Administrador','Manager') AND u.approved=1 AND u.deleted_at IS NULL AND u.receive_sms_notifications=1 AND u.phone IS NOT NULL AND TRIM(u.phone)<>''")->fetchAll(PDO::FETCH_ASSOC);
        $nurseId = (int)($nurse['id'] ?? $nurse['nurse_user_id'] ?? 0);
        $nurseName = trim((string)($nurse['full_name'] ?? $nurse['nurse_full_name'] ?? 'Enfermeiro'));
        $message = 'SAE: '.$nurseName.' registou-se como enfermeiro de serviço às '.date('H:i').'.';
        foreach ($recipients as $recipient) {
            $claim = $pdo->prepare("INSERT IGNORE INTO nurse_service_sms_log (service_date,nurse_user_id,recipient_user_id,recipient_phone,message,status) VALUES (CURDATE(),?,?,?,?,'pending')");
            $claim->execute([$nurseId,(int)$recipient['id'],(string)$recipient['phone'],$message]);
        }
    }

}
