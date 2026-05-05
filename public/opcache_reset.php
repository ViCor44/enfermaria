<?php
// APAGAR ESTE FICHEIRO APÓS USO
// Aceder via browser: /enfermaria/public/opcache_reset.php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'OPcache limpo com sucesso.';
} else {
    echo 'OPcache não está ativo.';
}
