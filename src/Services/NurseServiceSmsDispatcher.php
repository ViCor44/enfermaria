<?php
namespace App\Services;

use App\Core\Database;
use PDO;

final class NurseServiceSmsDispatcher
{
    /** @return array{sent:int,failed:int} */
    public function run(): array
    {
        $pdo = Database::getConnection();
        $summary = ['sent' => 0, 'failed' => 0];
        if ((int)$pdo->query("SELECT GET_LOCK('sae_nurse_service_sms_dispatch',0)")->fetchColumn() !== 1) {
            return $summary;
        }
        try {
            $rows = $pdo->query(
                "SELECT id,recipient_phone,message FROM nurse_service_sms_log
                 WHERE status IN ('pending','failed') AND attempts < 3
                   AND (last_attempt_at IS NULL OR last_attempt_at <= DATE_SUB(NOW(),INTERVAL 5 MINUTE))
                 ORDER BY created_at LIMIT 50"
            )->fetchAll(PDO::FETCH_ASSOC);
            $sms = new TeltonikaSmsClient();
            foreach ($rows as $row) {
                $pdo->prepare('UPDATE nurse_service_sms_log SET attempts=attempts+1,last_attempt_at=NOW() WHERE id=?')->execute([(int)$row['id']]);
                $result = $sms->send((string)$row['recipient_phone'], (string)$row['message']);
                $status = $result['ok'] ? 'sent' : 'failed';
                $pdo->prepare("UPDATE nurse_service_sms_log SET status=?,http_code=?,response=?,error_message=?,sent_at=IF(?='sent',NOW(),NULL) WHERE id=?")
                    ->execute([$status,$result['http_code'],json_encode($result['response'],JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE),$result['error'],$status,(int)$row['id']]);
                $summary[$result['ok'] ? 'sent' : 'failed']++;
            }
        } finally {
            $pdo->query("SELECT RELEASE_LOCK('sae_nurse_service_sms_dispatch')");
        }
        return $summary;
    }
}
