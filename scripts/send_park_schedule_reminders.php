<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Lisbon');
require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
    $file = dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
});

try {
    $date = new DateTimeImmutable('tomorrow');
    $summary = (new App\Services\ParkScheduleReminderService())->sendForDate($date);
    echo sprintf(
        "[%s] Data=%s Escalas=%d Enviados=%d Falhados=%d Ignorados=%d%s",
        date('Y-m-d H:i:s'), $summary['date'], $summary['found'], $summary['sent'],
        $summary['failed'], $summary['skipped'], PHP_EOL
    );
    exit($summary['failed'] > 0 ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR, '[' . date('Y-m-d H:i:s') . '] ERRO: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
