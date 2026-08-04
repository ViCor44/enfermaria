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
        $recipients = $pdo->query("SELECT u.id,u.phone FROM users u INNER JOIN roles r ON r.id=u.role_id WHERE r.name IN ('Administrador','Manager') AND u.approved=1 AND u.deleted_at IS NULL AND u.phone IS NOT NULL AND TRIM(u.phone)<>''")->fetchAll(PDO::FETCH_ASSOC);
        $nurseId = (int)($nurse['id'] ?? $nurse['nurse_user_id'] ?? 0);
        $nurseName = trim((string)($nurse['full_name'] ?? $nurse['nurse_full_name'] ?? 'Enfermeiro'));
        $message = self::gsmText('SAE: '.$nurseName.' registou-se como enfermeiro de servico as '.date('H:i').'.');
        $sms = new TeltonikaSmsClient();
        foreach ($recipients as $recipient) {
            $claim = $pdo->prepare("INSERT IGNORE INTO nurse_service_sms_log (service_date,nurse_user_id,recipient_user_id,recipient_phone,message,status) VALUES (CURDATE(),?,?,?,?,'pending')");
            $claim->execute([$nurseId,(int)$recipient['id'],(string)$recipient['phone'],$message]);
            if ($claim->rowCount() !== 1) continue;
            $result = $sms->send((string)$recipient['phone'],$message);
            $status = $result['ok'] ? 'sent' : 'failed';
            $update = $pdo->prepare("UPDATE nurse_service_sms_log SET status=?,http_code=?,response=?,error_message=?,sent_at=IF(?='sent',NOW(),NULL) WHERE service_date=CURDATE() AND nurse_user_id=? AND recipient_user_id=?");
            $update->execute([$status,$result['http_code'],json_encode($result['response'],JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE),$result['error'],$status,$nurseId,(int)$recipient['id']]);
        }
    }

    private static function gsmText(string $text): string
    {
        $converted = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$text);
        return substr($converted === false ? $text : $converted,0,160);
    }
}
