<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

session_start();

// Carregar .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Autoload simples das classes do src/
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Roteamento baseado em ?route=
$route = $_GET['route'] ?? 'login';

if ($route === 'login') {
    $controller = new App\Controllers\AuthController();
    $controller->showLoginForm();

} elseif ($route === 'login_submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\AuthController();
    $controller->login();

} elseif ($route === 'register') {
    $controller = new App\Controllers\AuthController();
    $controller->showRegisterForm();

} elseif ($route === 'register_submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\AuthController();
    $controller->register();

} elseif ($route === 'logout') {
    $controller = new App\Controllers\AuthController();
    $controller->logout();

} elseif ($route === 'dashboard') {
    $controller = new App\Controllers\DashboardController();
    $controller->index();

} elseif ($route === 'admin_users') {
    $controller = new App\Controllers\AdminUserController();
    $controller->pending();

} elseif ($route === 'admin_users_action' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\AdminUserController();
    $controller->handleAction();

} elseif ($route === 'admin_users_list') {
    $controller = new App\Controllers\AdminUserController();
    $controller->listUsers();

} elseif ($route === 'admin_users_change_role' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\AdminUserController();
    $controller->changeRoleAction();
} elseif ($route === 'admin_user_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\AdminUserController();
    $controller->deleteUser();

} elseif ($route === 'incidents_new') {
    $controller = new App\Controllers\IncidentController();
    $controller->create();

} elseif ($route === 'incidents_store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\IncidentController();
    $controller->store();

} elseif ($route === 'treatments_new') {
    $controller = new App\Controllers\TreatmentController();
    $controller->create();

} elseif ($route === 'treatments_store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\TreatmentController();
    $controller->store();

} elseif ($route === 'admin_incidents') {
    $controller = new App\Controllers\AdminIncidentController();
    $controller->index();
    
} elseif ($route === 'admin_incident_detail') {
    $controller = new App\Controllers\AdminIncidentController();
    $controller->show();

} elseif ($route === 'treatments_change_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\TreatmentController();
    $controller->changeStatus();
} elseif ($route === 'admin_treatments') {
    $controller = new App\Controllers\AdminTreatmentController();
    $controller->index();
} elseif ($route === 'treatment_conclude') {
    $controller = new App\Controllers\TreatmentController();
    $controller->conclude();
} elseif ($route === 'admin_incident_print') {
    $controller = new App\Controllers\AdminIncidentController();
    $controller->printPdf();
} elseif ($route === 'admin_stats') {
    $controller = new App\Controllers\AdminStatsController();
    $controller->index();

} else {
    http_response_code(404);
    echo 'Página não encontrada';
}
