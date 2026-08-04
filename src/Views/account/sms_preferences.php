<?php
$baseUrl = '/enfermaria/public/index.php';
$_SESSION['sms_preferences_csrf'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preferências de SMS | SAE</title>
    <link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">
    <style>
        body{margin:0;background:#f3f6fb;font-family:system-ui,sans-serif;color:#12396b}.preferences{max-width:680px;margin:32px auto;padding:0 20px}.card{background:#fff;border:1px solid #d7e4f5;border-radius:16px;padding:26px;box-shadow:0 8px 24px rgba(26,73,132,.08)}.choice{display:flex;gap:14px;align-items:flex-start;padding:18px;border:1px solid #ccdcf1;border-radius:12px;background:#f8fbff}.choice input{width:22px;height:22px;margin-top:2px}.choice strong{display:block;margin-bottom:5px}.muted{color:#58708f}.actions{display:flex;gap:12px;margin-top:22px}.btn{border:0;border-radius:9px;padding:11px 18px;text-decoration:none;cursor:pointer;font-weight:650}.primary{background:#1f6feb;color:#fff}.secondary{background:#eaf1fb;color:#174f96}.alert{padding:12px;border-radius:9px;margin-bottom:16px}.success{background:#e8f8ef;color:#17683b}.error{background:#fdecec;color:#962d2d}
    </style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main class="preferences">
    <div class="card">
        <h1>Notificações por SMS</h1>
        <p class="muted">Escolha se pretende receber no número associado à sua conta os avisos enviados pelo SAE.</p>
        <?php if (!empty($_SESSION['success'])): ?><div class="alert success"><?= htmlspecialchars((string)$_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?><div class="alert error"><?= htmlspecialchars((string)$_SESSION['error']); unset($_SESSION['error']); ?></div><?php endif; ?>
        <form method="post" action="<?= $baseUrl ?>?route=sms_preferences_update">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['sms_preferences_csrf']) ?>">
            <label class="choice">
                <input type="checkbox" name="receive_sms_notifications" value="1" <?= (int)$user['receive_sms_notifications'] === 1 ? 'checked' : '' ?>>
                <span><strong>Quero receber notificações por SMS</strong><span class="muted">Número atual: <?= htmlspecialchars((string)($user['phone'] ?: 'não definido')) ?></span></span>
            </label>
            <div class="actions"><button class="btn primary" type="submit">Guardar preferência</button><a class="btn secondary" href="<?= $baseUrl ?>?route=dashboard">Voltar</a></div>
        </form>
    </div>
</main>
</body>
</html>
