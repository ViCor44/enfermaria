<?php
namespace App\Services;

use App\Core\Database;
use DateTimeImmutable;
use PDO;

final class ParkScheduleReminderService
{
    private const SHIFTS = [
        'C' => ['Completo', '10:00-18:00'],
        'M' => ['Manhã', '10:00-13:45'],
        'T' => ['Tarde', '14:00-18:00'],
        'TE' => ['Tarde Extra', '13:00-encerramento'],
    ];

    /** @return array{date:string,found:int,sent:int,failed:int,skipped:int} */
    public function sendForDate(DateTimeImmutable $date): array
    {
        $pdo = Database::getConnection();
        $target = $date->format('Y-m-d');
        $summary = ['date' => $target, 'found' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0];
        $lockName = 'sae_schedule_reminders_' . $target;
        $lock = $pdo->prepare('SELECT GET_LOCK(?, 0)');
        $lock->execute([$lockName]);
        if ((int)$lock->fetchColumn() !== 1) {
            throw new \RuntimeException('Já existe outra execução dos lembretes em curso.');
        }

        try {
            $stmt = $pdo->prepare(
                "SELECT a.id AS assignment_id,a.work_date,a.staff_id,a.shift_type,s.full_name,
                        COALESCE(NULLIF(TRIM(u.phone),''),NULLIF(TRIM(s.phone),'')) AS phone,
                        u.id AS user_id,
                        CASE WHEN s.user_id IS NULL THEN 1
                             WHEN u.deleted_at IS NULL AND u.approved=1 THEN u.receive_sms_notifications
                             ELSE 0 END AS sms_enabled
                 FROM park_schedule_assignments a
                 INNER JOIN park_schedule_staff s ON s.id=a.staff_id
                 LEFT JOIN users u ON u.id=s.user_id
                 WHERE a.work_date=? AND s.active=1
                 ORDER BY s.full_name"
            );
            $stmt->execute([$target]);
            $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $summary['found'] = count($assignments);
            $sms = new TeltonikaSmsClient();

            foreach ($assignments as $assignment) {
                $shift = self::SHIFTS[(string)$assignment['shift_type']] ?? null;
                if ($shift === null || (int)$assignment['sms_enabled'] !== 1 || trim((string)$assignment['phone']) === '') {
                    $summary['skipped']++;
                    continue;
                }

                $existing = $pdo->prepare('SELECT status FROM park_schedule_sms_log WHERE work_date=? AND staff_id=?');
                $existing->execute([$target, (int)$assignment['staff_id']]);
                if ($existing->fetchColumn() === 'sent') {
                    $summary['skipped']++;
                    continue;
                }

                $message = sprintf(
                    'SAE: Lembrete para amanhã, %s. Turno %s, horário %s, no parque.',
                    $date->format('d/m/Y'), $shift[0], $shift[1]
                );
                $claim = $pdo->prepare(
                    "INSERT INTO park_schedule_sms_log
                        (work_date,staff_id,assignment_id,shift_type,recipient_phone,message,status,attempts)
                     VALUES (?,?,?,?,?,?,'pending',1)
                     ON DUPLICATE KEY UPDATE assignment_id=VALUES(assignment_id),shift_type=VALUES(shift_type),
                        recipient_phone=VALUES(recipient_phone),message=VALUES(message),status='pending',
                        attempts=attempts+1,error_message=NULL,http_code=NULL,response=NULL"
                );
                $claim->execute([$target,(int)$assignment['staff_id'],(int)$assignment['assignment_id'],(string)$assignment['shift_type'],(string)$assignment['phone'],$message]);

                $result = $sms->send((string)$assignment['phone'], $message);
                $status = $result['ok'] ? 'sent' : 'failed';
                $update = $pdo->prepare(
                    "UPDATE park_schedule_sms_log SET status=?,http_code=?,response=?,error_message=?,
                     sent_at=IF(?='sent',NOW(),NULL),last_attempt_at=NOW()
                     WHERE work_date=? AND staff_id=?"
                );
                $update->execute([$status,$result['http_code'],json_encode($result['response'],JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE),$result['error'],$status,$target,(int)$assignment['staff_id']]);
                $summary[$result['ok'] ? 'sent' : 'failed']++;
            }
        } finally {
            $release = $pdo->prepare('SELECT RELEASE_LOCK(?)');
            $release->execute([$lockName]);
        }
        return $summary;
    }
}
