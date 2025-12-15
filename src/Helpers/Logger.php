<?php

namespace App\Helpers;

class Logger
{
    public static function login($message)
    {
        self::write("login", $message);
    }

    private static function write($type, $message)
    {
        // Caminho do log (rota absoluta a partir da raiz da aplicação)
        $dir = __DIR__ . '/../../storage/logs/';
        
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        // Log rotativo diário
        $file = $dir . $type . '-' . date('Y-m-d') . '.log';

        $line = "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL;

        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
