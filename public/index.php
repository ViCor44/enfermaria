<?php
declare(strict_types=1);
date_default_timezone_set('Europe/Lisbon');

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

session_name('SAESESSID');
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
} elseif ($route === 'remote_access_request_create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\AuthController();
    $controller->createRemoteAccessRequest();
} elseif ($route === 'remote_access_request_status') {
    $controller = new App\Controllers\AuthController();
    $controller->remoteAccessRequestStatus();
} elseif ($route === 'remote_access_consume') {
    $controller = new App\Controllers\AuthController();
    $controller->consumeRemoteAccess();

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
} elseif ($route === 'admin_open_nurse_session' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\AdminUserController();
    $controller->openNurseSession();
} elseif ($route === 'admin_restore_session') {
    $controller = new App\Controllers\AdminUserController();
    $controller->restoreAdminSession();
} elseif ($route === 'admin_remote_access_approve' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\AdminUserController();
    $controller->approveRemoteAccess();
} elseif ($route === 'admin_remote_access_reject' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\AdminUserController();
    $controller->rejectRemoteAccess();

} elseif ($route === 'incidents_new') {
    $controller = new App\Controllers\IncidentController();
    $controller->create();

} elseif ($route === 'internal_new') {

    $controller = new App\Controllers\InternalRecordController();
    $controller->create();

} elseif ($route === 'admin_internal_records') {

    $controller = new App\Controllers\AdminInternalController();
    $controller->index();

} elseif ($route === 'internal_store' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $controller = new App\Controllers\InternalRecordController();
    $controller->store();

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

} elseif ($route === 'incident_patient_edit') {
    $controller = new App\Controllers\AdminIncidentController();
    $controller->editPatient();

} elseif ($route === 'incident_patient_update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\AdminIncidentController();
    $controller->updatePatient();

} elseif ($route === 'treatments_change_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\TreatmentController();
    $controller->changeStatus();
} elseif ($route === 'admin_treatments') {
    $controller = new App\Controllers\AdminTreatmentController();
    $controller->index();
} elseif ($route === 'admin_treatment_update_notes' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\AdminTreatmentController();
    $controller->updateNotes();
} elseif ($route === 'treatment_conclude') {
    $controller = new App\Controllers\TreatmentController();
    $controller->conclude();
} elseif ($route === 'admin_incident_print') {
    $controller = new App\Controllers\AdminIncidentController();
    $controller->printPdf();
} elseif ($route === 'admin_incident_print_refusal') {
    $controller = new App\Controllers\AdminIncidentController();
    $controller->printRefusalPdf();
} elseif ($route === 'admin_stats') {
    $controller = new App\Controllers\AdminStatsController();
    $controller->index();
} elseif ($route === 'admin_stats_export') {
    $controller = new App\Controllers\AdminStatsController();
    $controller->exportCsv();
} elseif ($route === 'admin_stats_export_pdf') {
    $controller = new App\Controllers\AdminStatsController();
    $controller->exportPdf();
} elseif ($route === 'admin_stats_internal') {
    $controller = new App\Controllers\AdminStatsController();
    $controller->indexInternal();
} elseif ($route === 'admin_stats_internal_export') {
    $controller = new App\Controllers\AdminStatsController();
    $controller->exportCsvInternal();
} elseif ($route === 'forgot_password') {
    // Mostrar formulário para pedir recuperação
    $controller = new App\Controllers\AuthController();
    $controller->forgotPassword();

} elseif ($route === 'forgot_submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Submeter pedido e enviar email
    $controller = new App\Controllers\AuthController();
    $controller->forgot_submit();

} elseif ($route === 'reset_password' && isset($_GET['token'])) {
    // Mostrar formulário de nova password
    $controller = new App\Controllers\AuthController();
    $controller->showResetPasswordForm();

} elseif ($route === 'reset_submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Submeter nova password
    $controller = new App\Controllers\AuthController();
    $controller->reset_submit();

} elseif ($route === 'about') {
    require __DIR__ . '/../src/Views/about.php';

} elseif ($route === 'sso') {
    $controller = new App\Controllers\AuthController();
    $controller->ssoLogin();
} elseif ($route === 'incident_hospital_followup') {
    $controller = new App\Controllers\HospitalFollowupController();
    $controller->create();

} elseif ($route === 'incident_hospital_followup_store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new App\Controllers\HospitalFollowupController();
    $controller->store();

} elseif ($route === 'incident_insurance_term') {
    (new App\Controllers\IncidentController())->insuranceTerm();

} else {
    http_response_code(404);
    echo 'Página não encontrada';
}
