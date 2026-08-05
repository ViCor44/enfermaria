<?php
declare(strict_types=1);
date_default_timezone_set('Europe/Lisbon');
require dirname(__DIR__) . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
spl_autoload_register(static function (string $class): void {
    $prefix='App\\'; if (strncmp($class,$prefix,strlen($prefix))!==0) return;
    $file=dirname(__DIR__).'/src/'.str_replace('\\','/',substr($class,strlen($prefix))).'.php';
    if (is_file($file)) require $file;
});
try {
    $result=(new App\Services\NurseServiceSmsDispatcher())->run();
    echo sprintf("[%s] Enviados=%d Falhados=%d%s",date('Y-m-d H:i:s'),$result['sent'],$result['failed'],PHP_EOL);
    exit($result['failed'] > 0 ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR,'['.date('Y-m-d H:i:s').'] ERRO: '.$e->getMessage().PHP_EOL); exit(1);
}
