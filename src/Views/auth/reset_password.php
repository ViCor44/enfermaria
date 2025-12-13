<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Nova Password | SAE</title>

<style>
    body {
        margin: 0;
        font-family: system-ui, sans-serif;
        background: #f5f7fb;
        height: 100vh;
        display: flex;
    }

    /* Painel esquerdo com branding */
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
        width: 150px;
        margin-bottom: 2rem;
    }

    .left-title {
        font-size: 2.3rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    .left-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        max-width: 360px;
    }

    /* Painel direito com o formulário */
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
        max-width: 380px;
        border-radius: 14px;
        box-shadow: 0 10px 35px rgba(0,0,0,.10);
    }

    h1 {
        margin: 0 0 1rem;
        text-align: center;
        color: #333;
    }

    label {
        display: block;
        margin-top: 1rem;
        font-weight: 600;
        color: #555;
    }

    input {
        width: 100%;
        padding: .8rem;
        margin-top: .3rem;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 1rem;
    }

    button {
        margin-top: 1.5rem;
        width: 100%;
        padding: .9rem;
        border: none;
        border-radius: 8px;
        background: #1f6feb;
        color: white;
        font-size: 1rem;
        cursor: pointer;
        transition: .2s;
    }

    button:hover {
        background: #1459b3;
    }

    .msg {
        text-align: center;
        margin-bottom: 1rem;
        padding: .7rem;
        border-radius: 6px;
        background: #ffeccc;
        color: #8a5a00;
        border: 1px solid #f3c78e;
        font-size: .95rem;
    }
</style>

</head>
<body>

<!-- Painel esquerdo -->
<div class="left-panel">
    <div class="logo-area">
        <img src="/enfermaria/public/assets/img/logo-sae.png" alt="SAE">
    </div>
    <div class="left-title">Definir Nova Password</div>
    <div class="left-subtitle">
        Introduza a nova password para concluir a redefinição do seu acesso ao sistema.
    </div>
</div>

<!-- Painel direito -->
<div class="right-panel">
    <div class="card">

        <h1>Nova Password</h1>

        <?php if (!empty($_SESSION['msg'])): ?>
            <div class="msg"><?= htmlspecialchars($_SESSION['msg']) ?></div>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

        <form method="post" action="/enfermaria/public/index.php?route=reset_submit">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">

            <label>Nova Password</label>
            <input type="password" name="password" required>

            <label>Confirmar Password</label>
            <input type="password" name="password_confirmation" required>

            <button type="submit">Alterar Password</button>
        </form>

    </div>
</div>

</body>
</html>
