<?php
namespace App\Services;

final class TeltonikaSmsClient
{
    private string $baseUrl;
    private string $user;
    private string $password;
    private string $modemId;
    private string $tokenFile;
    private int $timeout;
    private bool $verifySsl;

    public function __construct()
    {
        $this->loadSharedWorkLogSecret();
        $scheme = $this->env('MODEM_SCHEME', 'https');
        $host = $this->env('MODEM_HOST', '192.168.63.253:8443');
        $this->baseUrl = rtrim($scheme . '://' . $host, '/');
        $this->user = $this->env('MODEM_USER', 'admin');
        $this->password = $this->env('MODEM_PASS', defined('MODEM_PASS') ? (string)MODEM_PASS : '');
        $this->modemId = $this->env('MODEM_ID', '3-1');
        $this->timeout = max(1, (int)$this->env('MODEM_TIMEOUT', '8'));
        $this->verifySsl = filter_var($this->env('MODEM_VERIFY_SSL', 'false'), FILTER_VALIDATE_BOOL);
        $this->tokenFile = dirname(__DIR__, 2) . '/storage/modem_token.json';
    }

    public function send(string $number, string $message): array
    {
        if (!filter_var($this->env('SMS_ENABLED', 'true'), FILTER_VALIDATE_BOOL)) return $this->result(false, 0, 'Envio de SMS desativado.', null);
        if ($this->password === '') return $this->result(false, 0, 'MODEM_PASS não configurada.', null);
        $number = $this->normalizeNumber($number);
        if ($number === '') return $this->result(false, 0, 'Número inválido.', null);
        $token = $this->token();
        if ($token === null) return $this->result(false, 0, 'Não foi possível autenticar no modem.', null);
        $result = $this->sendWithToken($number, $message, $token);
        if (!$result['ok'] && in_array($result['http_code'], [401, 403], true)) {
            @unlink($this->tokenFile);
            $token = $this->token();
            if ($token !== null) $result = $this->sendWithToken($number, $message, $token);
        }
        return $result;
    }

    private function loadSharedWorkLogSecret(): void
    {
        if ($this->env('MODEM_PASS', '') !== '' || defined('MODEM_PASS')) return;
        $configured = $this->env('MODEM_CONFIG_FILE', '');
        $file = $configured !== '' ? $configured : dirname(__DIR__, 3) . '/work_log/config.local.php';
        if (is_file($file)) require_once $file;
    }

    private function normalizeNumber(string $number): string
    {
        $digits = preg_replace('/\D+/', '', trim($number)) ?? '';
        if (strlen($digits) === 9) $digits = '351' . $digits;
        return strlen($digits) >= 11 ? '+' . $digits : '';
    }

    private function token(): ?string
    {
        $cached = @file_get_contents($this->tokenFile);
        $data = $cached === false ? null : json_decode($cached, true);
        if (is_array($data) && !empty($data['token']) && (int)($data['expires_at'] ?? 0) > time() + 20) return (string)$data['token'];
        $response = $this->request('/api/login', ['username' => $this->user, 'password' => $this->password]);
        if (!$response['ok'] || !is_array($response['response'])) return null;
        $body = $response['response'];
        $token = (string)($body['data']['token'] ?? $body['token'] ?? '');
        if ($token === '') return null;
        $expires = max(60, (int)($body['data']['expires'] ?? 299));
        @file_put_contents($this->tokenFile, json_encode(['token' => $token, 'expires_at' => time() + $expires]), LOCK_EX);
        return $token;
    }

    private function sendWithToken(string $number, string $message, string $token): array
    {
        return $this->request('/api/messages/actions/send', ['data' => ['number' => $number, 'message' => $message, 'modem' => $this->modemId]], $token);
    }

    private function request(string $path, array $payload, ?string $token = null): array
    {
        if (!function_exists('curl_init')) return $this->result(false, 0, 'Extensão cURL indisponível.', null);
        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        if ($token !== null) $headers[] = 'Authorization: Bearer ' . $token;
        $curl = curl_init($this->baseUrl . $path);
        curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE), CURLOPT_HTTPHEADER => $headers, CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => min(4, $this->timeout), CURLOPT_TIMEOUT => $this->timeout, CURLOPT_SSL_VERIFYPEER => $this->verifySsl, CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0]);
        $raw = curl_exec($curl); $code = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE); $error = curl_error($curl); curl_close($curl);
        if ($raw === false) return $this->result(false, 0, $error, null);
        $decoded = json_decode((string)$raw, true); $body = json_last_error() === JSON_ERROR_NONE ? $decoded : (string)$raw;
        $ok = $code >= 200 && $code < 300 && (!is_array($body) || ($body['success'] ?? true) !== false);
        return $this->result($ok, $code, $ok ? '' : 'Resposta HTTP ' . $code, $body);
    }

    private function result(bool $ok, int $code, string $error, $response): array { return ['ok' => $ok, 'http_code' => $code, 'error' => $error, 'response' => $response]; }
    private function env(string $name, string $default): string { return isset($_ENV[$name]) ? (string)$_ENV[$name] : (($value = getenv($name)) !== false ? (string)$value : $default); }
}
