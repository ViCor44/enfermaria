<?php
$baseUrl = '/enfermaria/public/index.php';
if (empty($_SESSION['sms_preferences_csrf'])) {
    $_SESSION['sms_preferences_csrf'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Definições do utilizador | SAE</title>
    <link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">
    <style>
        body{margin:0;background:#f3f6fb;font-family:"Segoe UI",sans-serif;color:#12396b}.preferences{max-width:880px;margin:32px auto;padding:0 20px}.page-title{margin:0 0 6px;font-size:1.8rem;color:#10213f}.page-intro{margin:0 0 22px}.settings-grid{display:grid;grid-template-columns:minmax(0,.8fr) minmax(0,1.2fr);gap:16px}.card{background:#fff;border:1px solid #d7e4f5;border-radius:8px;padding:24px;box-shadow:0 8px 24px rgba(26,73,132,.06)}.card h2{margin:0 0 6px;font-size:1.05rem;color:#10213f}.account-list{display:grid;gap:14px;margin-top:20px}.account-item{padding-bottom:12px;border-bottom:1px solid #e7edf5}.account-item:last-child{padding-bottom:0;border-bottom:0}.account-item small{display:block;margin-bottom:4px;color:#6a7f99}.account-item strong{color:#1d3553}.choice{display:flex;gap:14px;align-items:flex-start;margin-top:20px;padding:18px;border:1px solid #ccdcf1;border-radius:8px;background:#f8fbff}.choice input{width:22px;height:22px;margin-top:2px;accent-color:#1f6feb}.choice strong{display:block;margin-bottom:5px}.muted{color:#58708f}.actions{display:flex;gap:12px;margin-top:22px}.btn{border:0;border-radius:7px;padding:11px 18px;text-decoration:none;cursor:pointer;font-weight:650}.primary{background:#1f6feb;color:#fff}.secondary{background:#eaf1fb;color:#174f96}.alert{padding:12px;border-radius:7px;margin-bottom:16px}.success{background:#e8f8ef;color:#17683b}.error{background:#fdecec;color:#962d2d}@media(max-width:720px){.preferences{margin:22px auto;padding:0 14px}.settings-grid{grid-template-columns:1fr}.actions{flex-direction:column}.btn{text-align:center}}
    </style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main class="preferences">
    <h1 class="page-title">Definições do utilizador</h1>
    <p class="page-intro muted">Consulte os dados da conta e configure as suas notificações.</p>
    <?php if (!empty($_SESSION['success'])): ?><div class="alert success"><?= htmlspecialchars((string)$_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?><div class="alert error"><?= htmlspecialchars((string)$_SESSION['error']); unset($_SESSION['error']); ?></div><?php endif; ?>

    <div class="settings-grid">
        <section class="card" aria-labelledby="account-title">
            <h2 id="account-title">Conta</h2>
            <p class="muted">Dados associados ao seu acesso ao SAE.</p>
            <div class="account-list">
                <div class="account-item"><small>Nome</small><strong><?= htmlspecialchars((string)$user['full_name']) ?></strong></div>
                <div class="account-item"><small>Email</small><strong><?= htmlspecialchars((string)$user['email']) ?></strong></div>
                <div class="account-item"><small>Telefone</small><strong><?= htmlspecialchars((string)($user['phone'] ?: 'Não definido')) ?></strong></div>
                <div class="account-item"><small>Perfil</small><strong><?= htmlspecialchars((string)($_SESSION['role'] ?? '')) ?></strong></div>
            </div>
        </section>

        <section class="card" aria-labelledby="notifications-title">
            <h2 id="notifications-title">Notificações</h2>
            <p class="muted">Escolha se pretende receber no número associado à conta os avisos enviados pelo SAE.</p>
            <form method="post" action="<?= $baseUrl ?>?route=user_settings_update">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['sms_preferences_csrf']) ?>">
                <label class="choice">
                    <input type="checkbox" name="receive_sms_notifications" value="1" <?= (int)$user['receive_sms_notifications'] === 1 ? 'checked' : '' ?>>
                    <span><strong>Receber notificações por SMS</strong><span class="muted">Número atual: <?= htmlspecialchars((string)($user['phone'] ?: 'não definido')) ?></span></span>
                </label>
                <div class="actions"><button class="btn primary" type="submit">Guardar definições</button><a class="btn secondary" href="<?= $baseUrl ?>?route=dashboard">Voltar ao painel</a></div>
            </form>
        </section>
    </div>
</main>
</body>
</html>
