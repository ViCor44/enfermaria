<?php
namespace App\Controllers;

use App\Models\User;

class AuthController
{
    private string $baseUrl = '/enfermaria/public/index.php';

    public function showLoginForm(): void
    {
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $_SESSION['error'] = 'Preencha todos os campos.';
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['error'] = 'Credenciais inválidas.';
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }

        if ((int)$user['approved'] !== 1) {
            $_SESSION['error'] = 'Conta ainda não aprovada pelo administrador.';
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }

        session_regenerate_id(true);

        // guardar último acesso ANTES de atualizar
        $_SESSION['last_login'] = $user['last_login']; // pode ser null na 1ª vez

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['role']      = $user['role_name'];
        $_SESSION['user_name'] = $user['full_name'];

        User::updateLastLogin($user['id']);

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
        $token = $_GET['token'] ?? null;

        if (!$token) {
            echo "Token inválido.";
            exit;
        }

        require __DIR__ . '/../Views/auth/reset_password.php';
    }
}
