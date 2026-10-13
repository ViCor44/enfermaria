<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\RemoteAccessRequest;
use App\Models\ParkSchedule;

class AuthController
{
    private string $baseUrl = '/enfermaria/public/index.php';

    public function showLoginForm(): void
    {
        $scheduleConfirmation = $_SESSION['login_schedule_confirmation'] ?? null;
        if (is_array($scheduleConfirmation) && (int)$scheduleConfirmation['expires_at'] < time()) {
            unset($_SESSION['login_schedule_confirmation']);
            $scheduleConfirmation = null;
            $_SESSION['error'] = 'A confirmação expirou. Inicie sessão novamente.';
        }
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // 1) Campos vazios
        if ($email === '' || $password === '') {

            \App\Helpers\Logger::login("FAIL (missing fields) | email='{$email}' | ip='{$ip}'");

            $_SESSION['error'] = 'Preencha todos os campos.';
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }

        // 2) Procurar utilizador
        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {

            \App\Helpers\Logger::login("FAIL (invalid credentials) | email='{$email}' | ip='{$ip}'");

            $_SESSION['error'] = 'Credenciais inválidas.';
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }

        // 3) Conta não aprovada
        if ((int)$user['approved'] !== 1) {

            \App\Helpers\Logger::login("FAIL (account not approved) | email='{$email}' | user_id='{$user['id']}' | ip='{$ip}'");

            $_SESSION['error'] = 'Conta ainda não aprovada pelo administrador.';
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }

        $this->requireLoginScheduleConfirmation($user, 'login');

        // SUCESSO
        \App\Helpers\Logger::login("SUCCESS | email='{$email}' | user_id='{$user['id']}' | ip='{$ip}'");

        $this->openLoginSession($user);
    }

    public function confirmLoginSchedule(): void
    {
        $pending = $_SESSION['login_schedule_confirmation'] ?? null;
        $token = (string)($_POST['schedule_confirmation_token'] ?? '');
        if (!is_array($pending) || (int)$pending['expires_at'] < time() ||
            !hash_equals((string)$pending['token'], $token)) {
            unset($_SESSION['login_schedule_confirmation']);
            $_SESSION['error'] = 'A confirmação é inválida ou expirou. Inicie sessão novamente.';
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }

        if (($_POST['decision'] ?? '') !== 'continue') {
            unset($_SESSION['login_schedule_confirmation']);
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }

        if ($pending['source'] === 'remote') {
            $this->consumeRemoteAccess((string)$pending['grant_token']);
            return;
        }

        $user = User::findByIdWithPassword((int)$pending['user_id']);
        if (!$user || (int)$user['approved'] !== 1 || !empty($user['deleted_at'])) {
            unset($_SESSION['login_schedule_confirmation']);
            $_SESSION['error'] = 'A conta já não está disponível. Inicie sessão novamente.';
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }

        $this->requireLoginScheduleConfirmation($user, (string)$pending['source']);
        \App\Helpers\Logger::login("SUCCESS (schedule confirmed) | user_id='{$user['id']}' | source='{$pending['source']}'");
        $this->openLoginSession($user);
    }

    private function requireLoginScheduleConfirmation(array $user, string $source, ?string $grantToken = null): void
    {
        if (($user['role_name'] ?? '') !== 'Enfermeiro') {
            return;
        }

        try {
            $isScheduled = ParkSchedule::isAssignedToday(
                (string)$user['full_name'],
                (string)($user['phone'] ?? ''),
                (int)$user['id']
            );
        } catch (\Throwable $e) {
            \App\Helpers\Logger::login("LOGIN SCHEDULE CHECK FAILED | user_id='{$user['id']}' | error='" . $e->getMessage() . "'");
            unset($_SESSION['login_schedule_confirmation']);
            $_SESSION['error'] = 'Não foi possível verificar a escala. Tente novamente mais tarde.';
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }

        $pending = $_SESSION['login_schedule_confirmation'] ?? null;
        $confirmed = is_array($pending) &&
            (int)$pending['user_id'] === (int)$user['id'] &&
            $pending['source'] === $source &&
            ($pending['grant_token'] ?? null) === $grantToken &&
            (int)$pending['expires_at'] >= time() &&
            ($_POST['decision'] ?? '') === 'continue' &&
            hash_equals((string)$pending['token'], (string)($_POST['schedule_confirmation_token'] ?? ''));

        if ($isScheduled || $confirmed) {
            unset($_SESSION['login_schedule_confirmation']);
            return;
        }

        $_SESSION['login_schedule_confirmation'] = [
            'user_id' => (int)$user['id'],
            'source' => $source,
            'grant_token' => $grantToken,
            'expires_at' => time() + 300,
            'token' => bin2hex(random_bytes(32)),
        ];
        header('Location: ' . $this->baseUrl . '?route=login');
        exit;
    }

    private function openLoginSession(array $user): void
    {
        unset($_SESSION['login_schedule_confirmation']);
        session_regenerate_id(true);
        $_SESSION['last_login'] = $user['last_login'] ?? null;
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['role'] = $user['role_name'];
        $_SESSION['user_name'] = $user['full_name'];

        User::updateLastLogin((int)$user['id']);
        $this->notifyNurseServiceRegistration($user);
        header('Location: ' . $this->baseUrl . '?route=dashboard');
        exit;
    }

    public function logout(): void
    {
        // limpar completamente a sessão
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        header('Location: /enfermaria/public/index.php?route=login');
        exit;
    }

    public function showRegisterForm(): void
    {
        require __DIR__ . '/../Views/auth/register.php';
    }

    public function checkNurseSchedule(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if ($fullName === '' || $phone === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Preencha o nome e o telefone para verificar a escala.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            echo json_encode(['isScheduled' => ParkSchedule::isAssignedToday($fullName, $phone)], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            error_log('Erro ao verificar a escala durante o registo: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Não foi possível verificar a escala. Tente novamente mais tarde.'], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    public function register(): void
    {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirmation'] ?? '';

        if ($fullName === '' || $email === '' || $password === '' || $confirm === '') {
            $_SESSION['error'] = 'Preencha todos os campos.';
            header('Location: ' . $this->baseUrl . '?route=register');
            exit;
        }

        if (empty($phone)) {
            $_SESSION['error'] = 'O telefone é obrigatório.';
            header('Location: ' . $this->baseUrl . '?route=register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email inválido.';
            header('Location: ' . $this->baseUrl . '?route=register');
            exit;
        }

        if ($password !== $confirm) {
            $_SESSION['error'] = 'As passwords não coincidem.';
            header('Location: ' . $this->baseUrl . '?route=register');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'A password deve ter pelo menos 6 caracteres.';
            header('Location: ' . $this->baseUrl . '?route=register');
            exit;
        }

        try {
            $isScheduled = ParkSchedule::isAssignedToday($fullName, $phone);
        } catch (\Throwable $e) {
            error_log('Erro ao verificar a escala durante o registo: ' . $e->getMessage());
            $_SESSION['error'] = 'Não foi possível verificar a escala. Tente novamente mais tarde.';
            header('Location: ' . $this->baseUrl . '?route=register');
            exit;
        }

        if (!$isScheduled && ($_POST['schedule_warning_confirmed'] ?? '') !== '1') {
            $_SESSION['error'] = 'Confirme se pretende continuar com o registo sem turno atribuído para hoje.';
            header('Location: ' . $this->baseUrl . '?route=register');
            exit;
        }

        try {
            // Todos registados como “Enfermeiro” por defeito
            \App\Models\User::createUser($email, $password, $fullName, $phone, 'Enfermeiro');

            $_SESSION['success_register'] = 'Registo efetuado. Aguarde aprovação do administrador.';
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        } catch (\RuntimeException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . $this->baseUrl . '?route=register');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Ocorreu um erro ao registar o utilizador.';
            header('Location: ' . $this->baseUrl . '?route=register');
            exit;
        }
    }

    public function forgot_submit()
    {
        $email = trim($_POST['email'] ?? '');

        $user = \App\Models\User::findByEmail($email);
        $_SESSION['msg'] = "Se o email existir, enviámos um link de recuperação.";

        if (!$user) {
            header("Location: ?route=forgot_password");
            exit;
        }

        // Gerar token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600);

        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at)
                            VALUES (:email, :token, :expires)");
        $stmt->execute([
            ':email'   => $email,
            ':token'   => $token,
            ':expires' => $expires
        ]);

        // Link
        $link = "http://localhost/enfermaria/public/index.php?route=reset_password&token=$token";

        // ENVIAR EMAIL VIA SMTP
        $mailer = new \App\Core\Mailer();

        $html = "
            <h2>Recuperação de Password</h2>
            <p>Recebemos um pedido para redefinir a sua password.</p>
            <p>Para definir uma nova password, clique no link:</p>
            <p><a href='$link'>$link</a></p>
            <br>
            <p>Se não pediu isto, ignore o email.</p>
        ";

        $mailer->send($email, "Recuperar Password - SAE", $html);

        header("Location: ?route=forgot_password");
        exit;
    }


    public function reset_submit()
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirmation'] ?? '';

        if ($password !== $confirm) {
            $_SESSION['msg'] = "As passwords não coincidem.";
            header("Location: ?route=reset_password&token=$token");
            exit;
        }

        $db = \App\Core\Database::getConnection();

        // Validar token
        $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW()");
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch();

        if (!$row) {
            $_SESSION['msg'] = "Token inválido ou expirado.";
            header("Location: ?route=forgot_password");
            exit;
        }

        $email = $row['email'];

        // Atualizar password
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password_hash = :hash WHERE email = :email");
        $stmt->execute([':hash' => $hash, ':email' => $email]);

        // Apagar token usado
        $db->prepare("DELETE FROM password_resets WHERE email = :email")->execute([':email' => $email]);

        $_SESSION['msg'] = "Password alterada com sucesso!";
        header("Location: ?route=login");
        exit;
    }

    public function forgotPassword()
    {
        require __DIR__ . '/../Views/auth/forgot_password.php';
    }

    public function showResetPasswordForm()
    {
        $token = $_GET['token'] ?? '';

        $db = \App\Core\Database::getConnection();

        $stmt = $db->prepare("SELECT id, expires_at FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        // Token inexistente ou expirado → mostrar página dedicada
        if (!$row || strtotime($row['expires_at']) < time()) {
            
            // Apagar tokens inválidos
            $del = $db->prepare("DELETE FROM password_resets WHERE token = ?");
            $del->execute([$token]);

            require __DIR__ . '/../Views/auth/token_expired.php';
            exit;
        }

        // Token válido → mostrar formulário
        require __DIR__ . '/../Views/auth/reset_password.php';
    }

    public function login_submit()
    {
        $this->login();
    }

    public function ssoLogin(): void
    {
        $token = $_GET['token'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (!$token) {
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }

        /* BD SUPER LOGIN */
        $pdoSuper = new \PDO(
            "mysql:host=localhost;dbname=super_login;charset=utf8mb4",
            "root",
            "",
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]
        );

        // 1️⃣ Validar token
        $stmt = $pdoSuper->prepare("
            SELECT admin_id, system_key
            FROM admin_tokens
            WHERE token = ?
            AND used = 0
            AND expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $t = $stmt->fetch();

        if (!$t) {
            \App\Helpers\Logger::login("SSO FAIL (invalid token) | ip='{$ip}'");
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }

        // 2️⃣ Marcar token como usado
        $pdoSuper->prepare("
            UPDATE admin_tokens SET used = 1 WHERE token = ?
        ")->execute([$token]);

        // 3️⃣ Mapear admin → user local
        $stmt = $pdoSuper->prepare("
            SELECT user_id
            FROM admin_user_map
            WHERE admin_id = ? AND system_key = ?
        ");
        $stmt->execute([$t['admin_id'], $t['system_key']]);
        $map = $stmt->fetch();

        if (!$map) {
            \App\Helpers\Logger::login("SSO FAIL (no mapping) | admin_id='{$t['admin_id']}' | ip='{$ip}'");
            exit('Admin não mapeado no SAE.');
        }

        // 4️⃣ Buscar utilizador local
        $user = User::findById($map['user_id']);

        if (!$user || (int)$user['approved'] !== 1) {
            \App\Helpers\Logger::login("SSO FAIL (user invalid/not approved) | user_id='{$map['user_id']}' | ip='{$ip}'");
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }

        $this->requireLoginScheduleConfirmation($user, 'sso');

        // 5️⃣ SUCESSO (igual ao login normal)
        \App\Helpers\Logger::login(
            "SSO SUCCESS | admin_id='{$t['admin_id']}' | user_id='{$user['id']}' | ip='{$ip}'"
        );

        $this->openLoginSession($user);
    }

    public function createRemoteAccessRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Metodo nao permitido']);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');

        $nurseName = trim((string)($_POST['nurse_name'] ?? ''));
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if ($nurseName === '') {
            echo json_encode(['ok' => false, 'message' => 'Indique o nome do enfermeiro.']);
            return;
        }

        try {
            $pdo = \App\Core\Database::getConnection();
            $stmt = $pdo->prepare(
                "SELECT u.id, u.full_name
                 FROM users u
                 JOIN roles r ON r.id = u.role_id
                 WHERE u.deleted_at IS NULL
                   AND u.approved = 1
                   AND r.name = 'Enfermeiro'
                   AND LOWER(TRIM(u.full_name)) = LOWER(TRIM(?))
                 LIMIT 2"
            );
            $stmt->execute([$nurseName]);
            $matches = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (count($matches) === 0) {
                echo json_encode(['ok' => false, 'message' => 'Enfermeiro nao encontrado ou nao aprovado.']);
                return;
            }

            if (count($matches) > 1) {
                echo json_encode(['ok' => false, 'message' => 'Existe mais de um enfermeiro com esse nome. Use o nome completo.']);
                return;
            }

            $nurse = $matches[0];
            $requestCode = RemoteAccessRequest::createPending((int)$nurse['id'], (string)$nurse['full_name'], $ip);

            \App\Helpers\Logger::login("REMOTE ACCESS REQUEST CREATED | nurse_id='{$nurse['id']}' | nurse_name='{$nurse['full_name']}' | ip='{$ip}'");

            echo json_encode([
                'ok' => true,
                'request_code' => $requestCode,
                'message' => 'Pedido enviado. Aguarde aprovacao do administrador.',
            ]);
            return;
        } catch (\Throwable $e) {
            \App\Helpers\Logger::login(
                "REMOTE ACCESS REQUEST FAILED | nurse_name='{$nurseName}' | ip='{$ip}' | error='" . $e->getMessage() . "'"
            );
            echo json_encode(['ok' => false, 'message' => 'Funcionalidade de acesso remoto indisponivel.']);
            return;
        }
    }

    public function remoteAccessRequestStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        $requestCode = trim((string)($_GET['code'] ?? ''));
        if ($requestCode === '') {
            echo json_encode(['ok' => false, 'message' => 'Codigo de pedido em falta.']);
            return;
        }

        try {
            RemoteAccessRequest::expireOldPending(20);
            $request = RemoteAccessRequest::findByCode($requestCode);

            if (!$request) {
                echo json_encode(['ok' => false, 'message' => 'Pedido nao encontrado.']);
                return;
            }

            $status = (string)($request['status'] ?? 'pending');

            if ($status === 'approved' && !empty($request['grant_token'])) {
                echo json_encode([
                    'ok' => true,
                    'status' => 'approved',
                    'redirect_url' => $this->baseUrl . '?route=remote_access_consume&token=' . urlencode((string)$request['grant_token']),
                ]);
                return;
            }

            echo json_encode([
                'ok' => true,
                'status' => $status,
            ]);
            return;
        } catch (\Throwable $e) {
            \App\Helpers\Logger::login(
                "REMOTE ACCESS STATUS FAILED | code='{$requestCode}' | error='" . $e->getMessage() . "'"
            );
            echo json_encode(['ok' => false, 'message' => 'Nao foi possivel consultar o pedido.']);
            return;
        }
    }

    public function consumeRemoteAccess(?string $grantToken = null): void
    {
        $token = $grantToken ?? trim((string)($_GET['token'] ?? ''));
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if ($token === '') {
            $_SESSION['error'] = 'Token de acesso remoto em falta.';
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }

        try {
            $request = RemoteAccessRequest::findApprovedByGrantToken($token);
            if (!$request) {
                unset($_SESSION['login_schedule_confirmation']);
                $_SESSION['error'] = 'Pedido remoto invalido ou expirado.';
                header('Location: ' . $this->baseUrl . '?route=login');
                exit;
            }

            if ((int)($request['approved'] ?? 0) !== 1 || !empty($request['deleted_at']) || ($request['role_name'] ?? '') !== 'Enfermeiro') {
                unset($_SESSION['login_schedule_confirmation']);
                $_SESSION['error'] = 'Conta alvo indisponivel para acesso remoto.';
                header('Location: ' . $this->baseUrl . '?route=login');
                exit;
            }

            $this->requireLoginScheduleConfirmation([
                'id' => (int)$request['nurse_user_id'],
                'full_name' => (string)$request['nurse_full_name'],
                'phone' => (string)($request['nurse_phone'] ?? ''),
                'role_name' => (string)$request['role_name'],
            ], 'remote', $token);

            RemoteAccessRequest::markConsumed((int)$request['id']);

            \App\Helpers\Logger::login("REMOTE ACCESS CONSUMED | request_id='{$request['id']}' | nurse_id='{$request['nurse_user_id']}' | ip='{$ip}'");

            $this->openLoginSession([
                'id' => (int)$request['nurse_user_id'],
                'full_name' => (string)$request['nurse_full_name'],
                'role_name' => (string)$request['role_name'],
            ]);
        } catch (\Throwable $e) {
            \App\Helpers\Logger::login("REMOTE ACCESS CONSUME FAILED | error='" . $e->getMessage() . "'");
            unset($_SESSION['login_schedule_confirmation']);
            $_SESSION['error'] = 'Nao foi possivel abrir a sessao remota.';
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }
    }

    private function notifyNurseServiceRegistration(array $user): void
    {
        try {
            \App\Services\NurseServiceSmsNotifier::notify($user);
        } catch (\Throwable $e) {
            // O login nunca deve falhar por indisponibilidade do modem ou migração em falta.
            \App\Helpers\Logger::login(
                "NURSE SERVICE SMS FAILED | nurse_id='" . (int)($user['id'] ?? 0) . "' | error='" . $e->getMessage() . "'"
            );
        }
    }
}
