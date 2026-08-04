<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class ParkSchedule
{
    public static function nurses(): array
    {
        $pdo = Database::getConnection();
        $pdo->exec("INSERT INTO park_schedule_staff (user_id,full_name,phone)
                    SELECT u.id,u.full_name,u.phone FROM users u INNER JOIN roles r ON r.id=u.role_id
                    WHERE r.name='Enfermeiro' AND u.approved=1 AND u.deleted_at IS NULL
                    ON DUPLICATE KEY UPDATE full_name=VALUES(full_name),phone=VALUES(phone),active=1");
        return $pdo->query("SELECT id,full_name,phone,user_id FROM park_schedule_staff WHERE active=1 ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function assignments(int $year, int $month): array
    {
        $stmt = Database::getConnection()->prepare("SELECT a.work_date,a.staff_id AS nurse_user_id,a.shift_type,u.full_name FROM park_schedule_assignments a INNER JOIN park_schedules s ON s.id=a.schedule_id INNER JOIN park_schedule_staff u ON u.id=a.staff_id WHERE s.year=? AND s.month=? ORDER BY a.work_date,u.full_name");
        $stmt->execute([$year, $month]);
        $days = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $days[$row['work_date']][] = $row;
        return $days;
    }

    public static function replaceDay(int $year, int $month, string $date, int $creatorId, array $assignments): void
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO park_schedules (year,month,created_by) VALUES (?,?,?) ON DUPLICATE KEY UPDATE updated_at=CURRENT_TIMESTAMP");
            $stmt->execute([$year, $month, $creatorId]);
            $stmt = $pdo->prepare('SELECT id FROM park_schedules WHERE year=? AND month=?');
            $stmt->execute([$year, $month]);
            $scheduleId = (int)$stmt->fetchColumn();
            $stmt = $pdo->prepare('DELETE FROM park_schedule_assignments WHERE schedule_id=? AND work_date=?');
            $stmt->execute([$scheduleId, $date]);
            $stmt = $pdo->prepare('INSERT INTO park_schedule_assignments (schedule_id,work_date,staff_id,shift_type) VALUES (?,?,?,?)');
            foreach ($assignments as $item) $stmt->execute([$scheduleId, $date, $item['nurse_id'], $item['shift']]);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    public static function replaceMonth(int $year, int $month, int $creatorId, array $assignments): void
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO park_schedules (year,month,created_by) VALUES (?,?,?) ON DUPLICATE KEY UPDATE updated_at=CURRENT_TIMESTAMP");
            $stmt->execute([$year, $month, $creatorId]);
            $stmt = $pdo->prepare('SELECT id FROM park_schedules WHERE year=? AND month=?');
            $stmt->execute([$year, $month]);
            $scheduleId = (int)$stmt->fetchColumn();
            $pdo->prepare('DELETE FROM park_schedule_assignments WHERE schedule_id=?')->execute([$scheduleId]);
            $stmt = $pdo->prepare('INSERT INTO park_schedule_assignments (schedule_id,work_date,staff_id,shift_type) VALUES (?,?,?,?)');
            foreach ($assignments as $item) {
                $stmt->execute([$scheduleId, $item['date'], $item['nurse_id'], $item['shift']]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    public static function findOrCreateStaff(string $name, ?string $phone): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM park_schedule_staff WHERE full_name=? LIMIT 1');
        $stmt->execute([$name]);
        $id = $stmt->fetchColumn();
        if ($id) {
            if ($phone) $pdo->prepare('UPDATE park_schedule_staff SET phone=? WHERE id=? AND (phone IS NULL OR phone=\'\')')->execute([$phone, $id]);
            return (int)$id;
        }
        $stmt = $pdo->prepare('INSERT INTO park_schedule_staff (full_name,phone) VALUES (?,?)');
        $stmt->execute([$name, $phone]);
        return (int)$pdo->lastInsertId();
    }
}
