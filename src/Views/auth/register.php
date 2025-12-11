<?php
$baseUrl = '/enfermaria/public/index.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Enfermaria | Registo</title>
    <style>
        body {
            margin: 0;
            font-family: system-ui, sans-serif;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #fff;
            padding: 2rem;
            width: 100%;
            max-width: 420px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,.15);
        }
        h1 {
            margin: 0 0 0.5rem;
            text-align: center;
            color: #333;
        }
        p.subtitle {
            margin: 0 0 1rem;
            text-align: center;
            font-size: .9rem;
            color: #666;
        }
        label {
            display: block;
            margin-top: 1rem;
            font-weight: 600;
            color: #555;
        }
        input {
            width: 100%;
            padding: .7rem;
            margin-top: .3rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }
        button {
            margin-top: 1.5rem;
            width: 100%;
            padding: .8rem;
            border: none;
            border-radius: 8px;
            background: #4caf50;
            color: white;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover {
            background: #43a047;
        }
        .error, .success {
            padding: .7rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            text-align: center;
            font-size: .9rem;
        }
        .error { background: #ffe0e0; color: #900; }
        .success { background: #e6ffed; color: #047857; }
        .links {
            margin-top: 1rem;
            text-align: center;
            font-size: .9rem;
        }
        .links a {
            color: #1f6feb;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="card">
    <h1>Novo Utilizador</h1>
    <p class="subtitle">Os dados serão revistos e aprovados por um administrador.</p>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="success">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
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
        Já tem conta? <a href="<?= $baseUrl ?>?route=login">Entrar</a>
    </div>
</div>

</body>
</html>
