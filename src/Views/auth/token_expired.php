<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Link Expirado | SAE</title>

<style>
    body {
        margin: 0;
        font-family: system-ui, sans-serif;
        background: #f5f7fb;
        height: 100vh;
        display: flex;
    }

    /* Painel Esquerdo */
    .left-panel {
        width: 50%;
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 4rem;
    }

    .logo-area img {
        width: 140px;
        margin-bottom: 2rem;
    }

    .left-title {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    .left-subtitle {
        font-size: 1.1rem;
        opacity: .95;
        max-width: 360px;
    }

    /* Painel Direito */
    .right-panel {
        width: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 2rem;
    }

    .card {
        background: #fff;
        padding: 2.5rem;
        width: 100%;
        max-width: 420px;
        border-radius: 14px;
        box-shadow: 0 10px 35px rgba(0,0,0,.12);
        text-align: center;
    }

    h1 {
        margin: 0 0 1rem;
        color: #333;
    }

    p {
        color: #555;
        margin-bottom: 1.5rem;
        line-height: 1.4;
    }

    a.button {
        display: inline-block;
        padding: .9rem 1.2rem;
        background: #1f6feb;
        color: white;
        text-decoration: none;
        font-size: 1rem;
        border-radius: 8px;
        transition: .2s;
    }

    a.button:hover {
        background: #1459b3;
    }
</style>
</head>

<body>

<!-- Painel esquerdo -->
<div class="left-panel">
    <div class="logo-area">
        <img src="/enfermaria/public/assets/img/logo-sae.png" alt="SAE">
    </div>
    <div class="left-title">Link expirado</div>
    <div class="left-subtitle">
        O link utilizado já não é válido. Pode pedir um novo link de recuperação de password.
    </div>
</div>

<!-- Painel direito -->
<div class="right-panel">
    <div class="card">
        <h1>Este link expirou</h1>
        <p>Por motivos de segurança, os links de redefinição de password têm validade limitada.<br>
        Por favor solicite um novo link para continuar.</p>

        <a href="/enfermaria/public/index.php?route=forgot_password" class="button">
            Pedir novo link
        </a>
    </div>
</div>

</body>
</html>
