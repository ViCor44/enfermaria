<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>SAE | Login</title>
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
        font-size: 2.4rem;
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
        max-width: 350px;
        text-align: center;
    }

    .card h2 {
        margin-bottom: 1.5rem;
        font-size: 1.6rem;
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
        transition: 0.2s;
    }

    button:hover {
        background: #1258d4;
    }

    .error {
        background: #ffe0e0;
        color: #900;
        padding: .7rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }

    footer {
        margin-top: 1rem;
        font-size: .9rem;
        color: #666;
    }

    footer a {
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

        <!-- Logo SAE (SVG direto) -->
        <div class="logo">
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

        <h1>Bem-vindo ao Sistema de Apoio à Enfermaria</h1>
        <p>
            Aceda ao painel para gerir acidentes, tratamentos e utilizadores de forma simples e rápida.
        </p>
    </div>

    <!-- PAINEL DIREITO -->
    <div class="right-panel">
        <div class="card">

            <h2>Login</h2>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="error"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="post" action="/enfermaria/public/index.php?route=login_submit">

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <button type="submit">Entrar</button>
            </form>

            <footer>
                Não tem conta?
                <a href="/enfermaria/public/index.php?route=register">Registe-se</a>
                <p style="text-align:center; margin-top:1rem;">
                    <a href="?route=forgot_password">Esqueci-me da password</a>
                </p>
            </footer>            
        </div>
    </div>

</div>

</body>
</html>
