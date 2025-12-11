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
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirmation'] ?? '';

        if ($fullName === '' || $email === '' || $password === '' || $confirm === '') {
            $_SESSION['error'] = 'Preencha todos os campos.';
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
            \App\Models\User::createUser($email, $password, $fullName, 'Enfermeiro');

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

}
