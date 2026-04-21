<?php
$baseUrl = '/enfermaria/public/index.php';
?>
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

    .success {
        background: #e6f9ec;
        border: 1px solid #2ecc71;
        color: #1e7e34;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 15px;
        font-size: 0.95rem;
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

    .link-button {
        background: transparent;
        border: none;
        color: #2575fc;
        font-weight: 600;
        cursor: pointer;
        padding: 0;
        font-size: .95rem;
    }

    .link-button:hover {
        background: transparent;
        color: #1258d4;
        text-decoration: underline;
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .45);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2000;
    }

    .modal-overlay.is-open {
        display: flex;
    }

    .modal-card {
        width: min(92vw, 430px);
        background: #fff;
        border-radius: 12px;
        padding: 1rem 1rem 1.2rem;
        box-shadow: 0 12px 30px rgba(0,0,0,.18);
        color: #2b2b2b;
    }

    .modal-card h3 {
        margin: .2rem 0 .4rem;
    }

    .modal-actions {
        display: flex;
        gap: .6rem;
        justify-content: flex-end;
        margin-top: 1rem;
    }

    .btn-secondary {
        border: 1px solid #cfd9ea;
        background: #fff;
        color: #36516f;
        border-radius: 8px;
        padding: .6rem .9rem;
        cursor: pointer;
    }

    .request-status {
        margin-top: .8rem;
        font-size: .92rem;
        color: #184a96;
        background: #edf4ff;
        border: 1px solid #cfe0ff;
        border-radius: 8px;
        padding: .65rem .75rem;
        display: none;
    }

    .request-status.is-visible {
        display: block;
    }

</style>
</head>
<body>

<div class="container">

    <!-- PAINEL ESQUERDO -->
    <div class="left-panel">

        <!-- Logo SAE (SVG direto) -->
        <div class="logo">
            <svg href="<?= $baseUrl ?>?route=about" viewBox="0 0 300 360">
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
        <a href="<?= $baseUrl ?>?route=about" class="nav-link">Sobre</a>

    </div>

    <!-- PAINEL DIREITO -->
    <div class="right-panel">
        <div class="card">

            <h2>Login</h2>

            <?php if (!empty($_SESSION['success_register'])): ?>
                <div class="success"><?= htmlspecialchars($_SESSION['success_register']) ?></div>
                <?php unset($_SESSION['success_register']); ?>
            <?php endif; ?>

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
                <p style="text-align:center; margin-top:.5rem;">
                    <button type="button" class="link-button" id="openRemoteAccessModal">Pedir acesso remoto</button>
                </p>
            </footer>            
        </div>
    </div>

</div>

<div class="modal-overlay" id="remoteAccessModal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="remoteAccessTitle">
        <h3 id="remoteAccessTitle">Pedido de acesso remoto</h3>
        <p style="margin:.2rem 0 .8rem; color:#5f738f; font-size:.95rem;">
            Indique o nome completo do enfermeiro para enviar um pedido ao administrador.
        </p>

        <label for="remoteNurseName">Nome do enfermeiro</label>
        <input id="remoteNurseName" type="text" autocomplete="name" placeholder="Ex: Pedro Carlos Pinheiro Carlos Dias">

        <div id="remoteAccessStatus" class="request-status"></div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" id="closeRemoteAccessModal">Cancelar</button>
            <button type="button" id="submitRemoteAccess">Enviar pedido</button>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('remoteAccessModal');
    var openBtn = document.getElementById('openRemoteAccessModal');
    var closeBtn = document.getElementById('closeRemoteAccessModal');
    var submitBtn = document.getElementById('submitRemoteAccess');
    var nurseInput = document.getElementById('remoteNurseName');
    var statusBox = document.getElementById('remoteAccessStatus');
    var pollTimer = null;

    function setStatus(message, isError) {
        statusBox.textContent = message;
        statusBox.classList.add('is-visible');
        if (isError) {
            statusBox.style.background = '#ffecec';
            statusBox.style.borderColor = '#ffc8c8';
            statusBox.style.color = '#9a1f1f';
            return;
        }

        statusBox.style.background = '#edf4ff';
        statusBox.style.borderColor = '#cfe0ff';
        statusBox.style.color = '#184a96';
    }

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        nurseInput.focus();
    }

    function closeModal() {
        stopPolling();
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    }

    function pollStatus(code) {
        stopPolling();
        pollTimer = setInterval(function () {
            fetch('/enfermaria/public/index.php?route=remote_access_request_status&code=' + encodeURIComponent(code) + '&t=' + Date.now(), {
                method: 'GET',
                cache: 'no-store',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data || data.ok !== true) {
                    if (data && data.message) {
                        setStatus(data.message, true);
                    }
                    return;
                }

                if (data.status === 'approved' && data.redirect_url) {
                    stopPolling();
                    setStatus('Pedido aprovado. A abrir sessão...', false);
                    window.location.href = data.redirect_url;
                    return;
                }

                if (data.status === 'rejected') {
                    stopPolling();
                    setStatus('Pedido rejeitado pelo administrador.', true);
                    return;
                }

                if (data.status === 'expired') {
                    stopPolling();
                    setStatus('Pedido expirou. Envie novamente.', true);
                }
            })
            .catch(function () {
                // ignora falhas transitórias e continua polling
            });
        }, 5000);
    }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    submitBtn.addEventListener('click', function () {
        var nurseName = (nurseInput.value || '').trim();
        if (!nurseName) {
            setStatus('Indique o nome completo do enfermeiro.', true);
            return;
        }

        submitBtn.disabled = true;
        setStatus('A enviar pedido ao administrador...', false);

        fetch('/enfermaria/public/index.php?route=remote_access_request_create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'nurse_name=' + encodeURIComponent(nurseName)
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            submitBtn.disabled = false;
            if (!data || data.ok !== true || !data.request_code) {
                setStatus((data && data.message) ? data.message : 'Falha ao criar pedido.', true);
                return;
            }

            setStatus('Pedido enviado. Aguarde aprovacao do administrador.', false);
            pollStatus(data.request_code);
        })
        .catch(function () {
            submitBtn.disabled = false;
            setStatus('Erro de comunicacao ao enviar pedido.', true);
        });
    });
})();
</script>

</body>
</html>
