<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Location
{
    public static function allActive(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, name FROM locations WHERE active = 1 ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(string $name): int
    {
        $pdo = Database::getConnection();

        // opcional: evitar duplicados simples
        $stmt = $pdo->prepare("SELECT id FROM locations WHERE name = ? LIMIT 1");
        $stmt->execute([$name]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            return (int)$existing;
        }

        $ins = $pdo->prepare("INSERT INTO locations (name, active) VALUES (?, 1)");
        $ins->execute([$name]);

        return (int)$pdo->lastInsertId();
    }
}
