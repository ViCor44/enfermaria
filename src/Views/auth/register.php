<?php
$baseUrl = '/enfermaria/public/index.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>SAE | Registo</title>

<style>
    body {
        margin: 0;
        font-family: system-ui, sans-serif;
        height: 100vh;
        display: flex;
        background: linear-gradient(135deg, #6a11cb, #2575fc);
        color: #fff;
    }

    .container {
        display: flex;
        width: 100%;
        height: 100%;
    }

    /* Painel esquerdo */
    .left-panel {
        flex: 1.2;
        padding: 4rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        color: white;
    }

    .left-panel h1 {
        font-size: 2.3rem;
        margin-top: 2rem;
        line-height: 1.3;
    }

    .left-panel p {
        font-size: 1.1rem;
        max-width: 420px;
        opacity: 0.9;
    }

    /* Logo SAE */
    .logo {
        width: 180px;
        margin-bottom: 1rem;
    }

    /* Painel direito */
    .right-panel {
        flex: 1;
        background: #fff;
        border-radius: 25px 0 0 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        box-shadow: -10px 0 30px rgba(0,0,0,0.15);
        color: #333;
    }

    .card {
        width: 100%;
        max-width: 380px;
        text-align: center;
    }

    .card h2 {
        margin-bottom: 0.5rem;
        font-size: 1.8rem;
    }

    .subtitle {
        font-size: .9rem;
        color: #555;
        margin-bottom: 1.5rem;
    }

    label {
        display: block;
        text-align: left;
        margin-top: 1rem;
        font-weight: 600;
        color: #444;
    }

    input {
        width: 100%;
        padding: .7rem;
        border-radius: 6px;
        border: 1px solid #ccc;
        margin-top: .3rem;
        font-size: 1rem;
    }

    button {
        width: 100%;
        padding: .8rem;
        border: none;
        border-radius: 8px;
        margin-top: 1.5rem;
        background: #2575fc;
        color: white;
        font-size: 1rem;
        cursor: pointer;
    }

    button:hover {
        background: #1258d4;
    }

    .error, .success {
        padding: .7rem;
        border-radius: 6px;
        margin-bottom: 1rem;
        font-size: .9rem;
    }
    .error { background: #ffe0e0; color: #900; }
    .success { background: #e6ffed; color: #047857; }

    .links {
        margin-top: 1rem;
        font-size: .9rem;
        color: #333;
    }

    .links a {
        color: #2575fc;
        text-decoration: none;
        font-weight: 600;
    }
</style>

</head>
<body>

<div class="container">

    <!-- PAINEL ESQUERDO -->
    <div class="left-panel">

        <!-- Logo SAE -->
        <div class="logo">
            <!-- Inserir o SVG aqui -->
            <svg viewBox="0 0 300 360">
                <rect x="55" y="20" width="190" height="150" rx="20" stroke="#a8d4ff" stroke-width="12" fill="none"/>
                <rect x="95" y="120" width="30" height="50" fill="#a8d4ff"/>
                <rect x="135" y="90" width="30" height="80" fill="#a8d4ff"/>
                <rect x="175" y="110" width="30" height="60" fill="#a8d4ff"/>

                <polyline points="175,70 195,90 225,55"
                          fill="none" stroke="#a8d4ff" stroke-width="12" stroke-linecap="round" stroke-linejoin="round"/>

                <text x="150" y="240" text-anchor="middle"
                      font-family="Arial" font-size="80" font-weight="700" fill="#ffffff">SAE</text>
                <text x="150" y="285" text-anchor="middle" font-family="Arial" font-size="26" fill="#ffffff">
                    Sistema de Apoio
                </text>
                <text x="150" y="315" text-anchor="middle" font-family="Arial" font-size="26" fill="#ffffff">
                    à Enfermaria
                </text>
            </svg>
        </div>

        <h1>Crie a sua conta</h1>
        <p>O acesso será ativado após aprovação por um administrador.</p>
    </div>

    <!-- PAINEL DIREITO -->
    <div class="right-panel">
        <div class="card">

            <h2>Registo</h2>
            <p class="subtitle">Preencha os seus dados para criar acesso ao SAE.</p>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <form method="post" action="<?= $baseUrl ?>?route=register_submit">
                <label>Nome completo</label>
                <input type="text" name="full_name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <label>Confirmar password</label>
                <input type="password" name="password_confirmation" required>

                <button type="submit">Registar</button>
            </form>

            <div class="links">
                Já tem conta?
                <a href="<?= $baseUrl ?>?route=login">Entrar</a>
            </div>

        </div>
    </div>

</div>

</body>
</html>
