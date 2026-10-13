<?php
declare(strict_types=1);

namespace App\Core {
    final class Database
    {
        public static function getConnection(): \PDO
        {
            return new \ScheduleTestPdo();
        }
    }
}

namespace App\Models {
    final class User
    {
        public static array $fixture;
        public static int $loginUpdates = 0;

        public static function findByEmail(string $email): ?array
        {
            return self::$fixture;
        }

        public static function findByIdWithPassword(int $id): ?array
        {
            return self::$fixture;
        }

        public static function updateLastLogin(int $id): void
        {
            self::$loginUpdates++;
        }
    }

    final class RemoteAccessRequest
    {
        public static bool $consumed = false;
        public static bool $expired = false;

        public static function findApprovedByGrantToken(string $token): ?array
        {
            if (self::$expired) return null;
            return [
                'id' => 42, 'nurse_user_id' => 7, 'nurse_full_name' => 'Test Nurse',
                'nurse_phone' => '910000000', 'approved' => 1, 'deleted_at' => null,
                'role_name' => 'Enfermeiro',
            ];
        }

        public static function markConsumed(int $id): void
        {
            self::$consumed = true;
        }
    }
}

namespace App\Helpers {
    final class Logger
    {
        public static function login(string $message): void {}
    }
}

namespace App\Services {
    final class NurseServiceSmsNotifier
    {
        public static int $notifications = 0;

        public static function notify(array $user): void
        {
            self::$notifications++;
        }
    }
}

namespace {
    final class ScheduleTestPdo extends \PDO
    {
        public static array $rows = [];
        public static bool $unavailable = false;

        public function __construct() {}

        public function prepare(string $query, array $options = []): \PDOStatement|false
        {
            if (self::$unavailable) throw new \RuntimeException('Test database unavailable');
            if (!str_contains($query, 'a.work_date=?')) throw new \RuntimeException('Missing work date filter');
            return new ScheduleTestStatement();
        }
    }

    final class ScheduleTestStatement extends \PDOStatement
    {
        public function execute(?array $params = null): bool
        {
            if ($params !== [date('Y-m-d')]) throw new \RuntimeException('Not checking today');
            return true;
        }

        public function fetchAll(int $mode = \PDO::FETCH_DEFAULT, mixed ...$args): array
        {
            return ScheduleTestPdo::$rows;
        }
    }

    $cases = [
        'not-scheduled', 'scheduled-id', 'scheduled-name', 'scheduled-phone',
        'different-linked-user', 'invalid-password', 'unapproved', 'administrator',
        'continue', 'cancel', 'invalid-token', 'expired', 'revoked', 'unavailable',
        'forged-confirmation', 'remote-warning', 'remote-continue', 'remote-expired',
        'sso-continue', 'legacy-login', 'confirm-unavailable', 'warning-view',
    ];
    $case = $argv[1] ?? null;
    if ($case === null) {
        foreach ($cases as $name) {
            $process = proc_open([PHP_BINARY, __FILE__, $name], [
                0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
            ], $pipes);
            if (!is_resource($process)) throw new \RuntimeException('Cannot start test');
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $status = proc_close($process);
            if ($status !== 0 || $errors !== '') {
                fwrite(STDERR, "FAIL {$name}: {$output}{$errors}\n");
                exit(1);
            }
            echo $output;
        }
        echo count($cases) . " login schedule tests passed.\n";
        exit;
    }
    if (!in_array($case, $cases, true)) throw new \RuntimeException('Unknown test');

    $root = dirname(__DIR__, 2);
    require $root . '\vendor\autoload.php';
    require $root . '\src\Services\PdfScheduleImporter.php';
    require $root . '\src\Models\ParkSchedule.php';
    require $root . '\src\Controllers\AuthController.php';
    date_default_timezone_set('Europe/Lisbon');
    ini_set('session.use_cookies', '0');
    session_start();
    $_SESSION = [];
    $_GET = ['token' => 'test-grant'];
    $_POST = ['email' => 'test@example.invalid', 'password' => 'test-password'];
    \App\Models\User::$fixture = [
        'id' => 7, 'full_name' => 'Test Nurse', 'phone' => '910000000',
        'approved' => 1, 'deleted_at' => null, 'role_name' => 'Enfermeiro',
        'last_login' => null, 'password_hash' => password_hash('test-password', PASSWORD_DEFAULT),
    ];
    $scheduled = [
        'user_id' => 7, 'staff_name' => 'Different Name', 'staff_phone' => null,
        'user_name' => null, 'user_phone' => null,
    ];
    if ($case === 'scheduled-id') ScheduleTestPdo::$rows = [$scheduled];
    if ($case === 'scheduled-name') {
        $scheduled['user_id'] = null;
        $scheduled['staff_name'] = ' TEST   NURSE ';
        ScheduleTestPdo::$rows = [$scheduled];
    }
    if ($case === 'scheduled-phone') {
        $scheduled['user_id'] = null;
        $scheduled['staff_phone'] = '910 000 000';
        ScheduleTestPdo::$rows = [$scheduled];
    }
    if ($case === 'different-linked-user') {
        $scheduled['user_id'] = 99;
        $scheduled['staff_name'] = 'Test Nurse';
        $scheduled['staff_phone'] = '910000000';
        ScheduleTestPdo::$rows = [$scheduled];
    }
    if ($case === 'invalid-password') $_POST['password'] = 'wrong';
    if ($case === 'unapproved') \App\Models\User::$fixture['approved'] = 0;
    if ($case === 'administrator') \App\Models\User::$fixture['role_name'] = 'Administrador';
    if (in_array($case, ['unavailable', 'confirm-unavailable'], true)) ScheduleTestPdo::$unavailable = true;
    if ($case === 'forged-confirmation') {
        $_POST['decision'] = 'continue';
        $_POST['schedule_confirmation_token'] = 'forged';
    }

    $confirmationCases = [
        'continue', 'cancel', 'invalid-token', 'expired', 'revoked',
        'remote-continue', 'remote-expired', 'sso-continue', 'confirm-unavailable', 'warning-view',
    ];
    if (in_array($case, $confirmationCases, true)) {
        $_SESSION['login_schedule_confirmation'] = [
            'user_id' => 7, 'token' => 'test-confirmation', 'expires_at' => time() + 300,
            'source' => str_starts_with($case, 'remote-') ? 'remote' : ($case === 'sso-continue' ? 'sso' : 'login'),
            'grant_token' => str_starts_with($case, 'remote-') ? 'test-grant' : null,
        ];
        $_POST = ['decision' => 'continue', 'schedule_confirmation_token' => 'test-confirmation'];
    }
    if ($case === 'cancel') $_POST['decision'] = 'cancel';
    if ($case === 'invalid-token') $_POST['schedule_confirmation_token'] = 'wrong';
    if ($case === 'expired') $_SESSION['login_schedule_confirmation']['expires_at'] = time() - 1;
    if ($case === 'revoked') \App\Models\User::$fixture['approved'] = 0;
    if ($case === 'remote-expired') \App\Models\RemoteAccessRequest::$expired = true;

    register_shutdown_function(static function () use ($case): void {
        $shouldLogin = in_array($case, [
            'scheduled-id', 'scheduled-name', 'scheduled-phone', 'administrator',
            'continue', 'remote-continue', 'sso-continue',
        ], true);
        $shouldWarn = in_array($case, [
            'not-scheduled', 'different-linked-user', 'forged-confirmation', 'remote-warning', 'legacy-login', 'warning-view',
        ], true);
        $actualLogin = isset($_SESSION['user_id']);
        $actualWarning = isset($_SESSION['login_schedule_confirmation']);
        $ok = $actualLogin === $shouldLogin &&
            \App\Models\User::$loginUpdates === ($shouldLogin ? 1 : 0) &&
            \App\Services\NurseServiceSmsNotifier::$notifications === ($shouldLogin ? 1 : 0) &&
            \App\Models\RemoteAccessRequest::$consumed === ($case === 'remote-continue');
        $ok = $ok && $actualWarning === $shouldWarn;
        if ($shouldWarn && $case !== 'warning-view') $ok = $ok && strlen($_SESSION['login_schedule_confirmation']['token']) === 64;
        if ($case === 'warning-view') {
            $html = ob_get_clean();
            $ok = $ok && str_contains($html, 'Swal.fire(') &&
                str_contains($html, 'route=login_schedule_confirm') &&
                str_contains($html, 'value="test-confirmation"') &&
                !str_contains($html, 'id="loginPassword"');
        }
        session_destroy();
        if (!$ok) {
            fwrite(STDERR, "Unexpected login/confirmation state for {$case}\n");
            exit(1);
        }
        echo "PASS {$case}\n";
    });

    $controller = new \App\Controllers\AuthController();
    if ($case === 'warning-view') {
        ob_start();
        $controller->showLoginForm();
    } elseif (in_array($case, $confirmationCases, true)) $controller->confirmLoginSchedule();
    elseif ($case === 'remote-warning') $controller->consumeRemoteAccess();
    elseif ($case === 'legacy-login') $controller->login_submit();
    else $controller->login();
}
