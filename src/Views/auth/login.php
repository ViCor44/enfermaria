<?php
$baseUrl = '/enfermaria/public/index.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SAE | Login</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --page-bg: #edf4fb;
        --surface: #ffffff;
        --surface-soft: #f6f9fd;
        --text: #16324f;
        --muted: #64778d;
        --accent: #1f6feb;
        --accent-dark: #1859bd;
        --border: #d8e4f1;
        --focus: rgba(31, 111, 235, .18);
        --shadow: 0 24px 70px rgba(20, 57, 94, .16);
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        min-height: 100vh;
        min-height: 100dvh;
        font-family: 'Manrope', 'Segoe UI', sans-serif;
        background:
            linear-gradient(rgba(31, 111, 235, .045) 1px, transparent 1px),
            linear-gradient(90deg, rgba(31, 111, 235, .045) 1px, transparent 1px),
            var(--page-bg);
        background-size: 34px 34px;
        color: var(--text);
    }

    .container {
        width: min(1180px, calc(100% - 48px));
        min-height: min(720px, calc(100dvh - 48px));
        margin: 24px auto;
        display: grid;
        grid-template-columns: minmax(0, .92fr) minmax(420px, 1.08fr);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .8);
        border-radius: 20px;
        background: var(--surface);
        box-shadow: var(--shadow);
    }

    .left-panel {
        position: relative;
        padding: clamp(36px, 5vw, 68px);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
        color: #fff;
        background: linear-gradient(145deg, #164c91 0%, #1f6feb 58%, #4aa7e8 100%);
    }

    .left-panel::after {
        content: '';
        position: absolute;
        right: -90px;
        bottom: -105px;
        width: 310px;
        height: 310px;
        border: 54px solid rgba(255, 255, 255, .09);
        border-radius: 50%;
        pointer-events: none;
    }

    .brand-mark {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        gap: 14px;
        color: #fff;
        text-decoration: none;
        width: fit-content;
    }

    .brand-mark img {
        width: 58px;
        height: 58px;
        padding: 5px;
        object-fit: contain;
        border-radius: 12px;
        background: #fff;
    }

    .brand-name {
        display: block;
        font-size: 1.12rem;
        font-weight: 800;
    }

    .brand-subtitle {
        display: block;
        margin-top: 2px;
        font-size: .76rem;
        font-weight: 600;
        opacity: .82;
    }

    .welcome-copy {
        position: relative;
        z-index: 1;
        margin: 70px 0;
    }

    .eyebrow {
        margin: 0 0 14px;
        font-size: .76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: #cce7ff;
    }

    .left-panel h1 {
        max-width: 520px;
        margin: 0;
        font-size: clamp(2rem, 4vw, 3.25rem);
        line-height: 1.08;
        letter-spacing: 0;
    }

    .left-panel p:not(.eyebrow) {
        max-width: 460px;
        margin: 20px 0 0;
        font-size: 1rem;
        line-height: 1.7;
        color: #e3f1ff;
    }

    .about-link {
        position: relative;
        z-index: 1;
        width: fit-content;
        color: #fff;
        font-size: .88rem;
        font-weight: 700;
        text-underline-offset: 4px;
    }

    .right-panel {
        display: grid;
        place-items: center;
        padding: clamp(34px, 6vw, 76px);
        background: var(--surface);
    }

    .card {
        width: 100%;
        max-width: 410px;
    }

    .card h2 {
        margin: 0;
        font-size: clamp(1.8rem, 3vw, 2.25rem);
        line-height: 1.2;
        color: var(--text);
        letter-spacing: 0;
    }

    .card-intro {
        margin: 10px 0 26px;
        color: var(--muted);
        font-size: .95rem;
        line-height: 1.55;
    }

    label {
        display: block;
        margin: 18px 0 7px;
        font-size: .84rem;
        font-weight: 800;
        color: #304b68;
    }

    input {
        width: 100%;
        min-height: 50px;
        padding: 0 14px;
        border: 1px solid #bfd0e1;
        border-radius: 8px;
        background: #fbfdff;
        color: var(--text);
        font: inherit;
        font-size: .95rem;
        transition: border-color .2s, box-shadow .2s, background .2s;
    }

    input:hover {
        border-color: #8eacd0;
    }

    input:focus {
        outline: none;
        border-color: var(--accent);
        background: #fff;
        box-shadow: 0 0 0 4px var(--focus);
    }

    .primary-button,
    #submitRemoteAccess {
        width: 100%;
        min-height: 50px;
        padding: 0 18px;
        border: none;
        border-radius: 8px;
        background: var(--accent);
        color: #fff;
        font: inherit;
        font-weight: 800;
        cursor: pointer;
        transition: background .2s, box-shadow .2s, transform .15s;
    }

    .primary-button {
        margin-top: 24px;
    }

    .primary-button:hover,
    #submitRemoteAccess:hover {
        background: var(--accent-dark);
        box-shadow: 0 10px 24px rgba(31, 111, 235, .2);
        transform: translateY(-1px);
    }

    button:focus-visible,
    a:focus-visible {
        outline: 3px solid rgba(31, 111, 235, .28);
        outline-offset: 3px;
    }

    .error {
        background: #fff1f1;
        border: 1px solid #f4caca;
        color: #9a2525;
        padding: 12px 14px;
        border-radius: 8px;
        margin: 18px 0;
        font-size: .9rem;
    }

    .success {
        background: #edf9f2;
        border: 1px solid #a9ddbd;
        color: #236b3d;
        padding: 12px 14px;
        border-radius: 8px;
        margin: 18px 0;
        font-size: .9rem;
    }

    footer {
        margin-top: 22px;
        font-size: .88rem;
        color: var(--muted);
    }

    footer a {
        color: var(--accent);
        text-decoration: none;
        font-weight: 800;
    }

    footer a:hover {
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .account-links {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .remote-access-callout {
        margin-top: 24px;
        padding: 16px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--surface-soft);
    }

    .remote-access-callout strong {
        display: block;
        margin-bottom: 5px;
        color: var(--text);
        font-size: .9rem;
    }

    .remote-access-callout p {
        margin: 0 0 12px;
        color: var(--muted);
        font-size: .82rem;
        line-height: 1.5;
    }

    .remote-access-callout .remote-access-btn {
        padding: 0;
        border: 0;
        background: transparent;
        color: var(--accent);
        font: inherit;
        font-size: .84rem;
        font-weight: 800;
        cursor: pointer;
    }

    .remote-access-callout .remote-access-btn:hover {
        color: var(--accent-dark);
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        padding: 20px;
        background: rgba(15, 35, 58, .58);
        backdrop-filter: blur(5px);
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
        padding: 24px;
        box-shadow: 0 18px 44px rgba(17, 35, 72, .28);
        color: var(--text);
        border: 1px solid var(--border);
    }

    .modal-card h3 {
        margin: 0 0 6px;
        font-size: 1.3rem;
        color: var(--text);
    }

    .modal-hint {
        margin: 0 0 16px;
        color: var(--muted);
        font-size: .9rem;
        line-height: 1.45;
    }

    .modal-content {
        display: grid;
        gap: .55rem;
    }

    .modal-card input {
        border-radius: 8px;
    }

    .modal-actions {
        display: flex;
        gap: .6rem;
        justify-content: flex-end;
        margin-top: 16px;
    }

    .btn-secondary {
        min-height: 46px;
        border: 1px solid var(--border);
        background: #fff;
        color: #36516f;
        border-radius: 8px;
        padding: 0 18px;
        font: inherit;
        font-weight: 700;
        cursor: pointer;
    }

    .btn-secondary:hover {
        background: var(--surface-soft);
    }

    #submitRemoteAccess {
        width: auto;
        min-height: 46px;
    }

    .request-status {
        margin-top: .8rem;
        font-size: .93rem;
        color: #184a96;
        background: linear-gradient(180deg, #f4f8ff 0%, #edf4ff 100%);
        border: 1px solid #cfe0ff;
        border-radius: 10px;
        padding: .7rem .8rem;
        display: none;
        line-height: 1.35;
    }

    .request-status.is-visible {
        display: block;
    }

    .modal-card.is-pending .modal-content {
        display: none;
    }

    .modal-card.is-pending .modal-actions {
        margin-top: .75rem;
    }

    .modal-card.is-pending .btn-secondary {
        width: 100%;
    }

    @media (max-width: 820px) {
        .container {
            width: min(100% - 28px, 560px);
            min-height: auto;
            margin: 14px auto;
            grid-template-columns: 1fr;
        }

        .left-panel {
            min-height: 245px;
            padding: 28px;
        }

        .welcome-copy {
            margin: 42px 0 18px;
        }

        .left-panel h1 {
            max-width: 440px;
            font-size: 2rem;
        }

        .left-panel p:not(.eyebrow) {
            margin-top: 12px;
            font-size: .9rem;
        }

        .about-link {
            display: none;
        }

        .right-panel {
            padding: 34px 28px 38px;
        }
    }

    @media (max-width: 440px) {
        .container {
            width: 100%;
            margin: 0;
            border: 0;
            border-radius: 0;
        }

        .left-panel,
        .right-panel {
            padding-left: 22px;
            padding-right: 22px;
        }

        .brand-mark img {
            width: 48px;
            height: 48px;
        }

        .account-links {
            align-items: flex-start;
            flex-direction: column;
            gap: 10px;
        }

        .modal-actions {
            flex-direction: column-reverse;
        }

        .modal-actions button,
        #submitRemoteAccess {
            width: 100%;
        }
    }

</style>
</head>
<body>

<div class="container">

    <div class="left-panel">
        <a href="<?= $baseUrl ?>?route=about" class="brand-mark" aria-label="SAE - Sobre o sistema">
            <img src="/enfermaria/public/assets/img/logo-sae.png" alt="">
            <span>
                <span class="brand-name">SAE</span>
                <span class="brand-subtitle">Sistema de Apoio à Enfermaria</span>
            </span>
        </a>

        <div class="welcome-copy">
            <p class="eyebrow">Área reservada</p>
            <h1>A enfermaria, organizada num só lugar.</h1>
            <p>Acompanhe ocorrências, tratamentos e equipas com acesso rápido e seguro.</p>
        </div>

        <a href="<?= $baseUrl ?>?route=about" class="about-link">Conhecer o SAE</a>
    </div>

    <div class="right-panel">
        <div class="card">
            <h2>Iniciar sessão</h2>
            <p class="card-intro">Introduza os seus dados para aceder ao painel.</p>

            <?php if (!empty($_SESSION['success_register'])): ?>
                <div class="success"><?= htmlspecialchars($_SESSION['success_register']) ?></div>
                <?php unset($_SESSION['success_register']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="error"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="post" action="/enfermaria/public/index.php?route=login_submit">

                <label for="loginEmail">Email</label>
                <input id="loginEmail" type="email" name="email" autocomplete="username" placeholder="nome@exemplo.pt" required autofocus>

                <label for="loginPassword">Password</label>
                <input id="loginPassword" type="password" name="password" autocomplete="current-password" placeholder="Introduza a sua password" required>

                <button type="submit" class="primary-button">Entrar</button>
            </form>

            <footer>
                <div class="account-links">
                    <span>Não tem conta? <a href="/enfermaria/public/index.php?route=register">Registe-se</a></span>
                    <a href="<?= $baseUrl ?>?route=forgot_password">Recuperar password</a>
                </div>

                <div class="remote-access-callout">
                    <strong>Esqueceu-se do cartão?</strong>
                    <p>Peça ao administrador uma autorização temporária para entrar no sistema.</p>
                    <button type="button" class="remote-access-btn" id="openRemoteAccessModal">Pedir acesso remoto</button>
                </div>
            </footer>            
        </div>
    </div>

</div>

<div class="modal-overlay" id="remoteAccessModal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="remoteAccessTitle">
        <h3 id="remoteAccessTitle">Pedido de acesso remoto</h3>
        <p class="modal-hint" id="remoteAccessHint">
            Indique o nome completo do enfermeiro para enviar um pedido ao administrador.
        </p>

        <div class="modal-content" id="remoteAccessContent">
            <label for="remoteNurseName">Nome do enfermeiro</label>
            <input id="remoteNurseName" type="text" autocomplete="name" placeholder="Ex: Pedro Carlos Pinheiro Carlos Dias">
        </div>

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
    var modalCard = modal.querySelector('.modal-card');
    var hintText = document.getElementById('remoteAccessHint');
    var storageKey = 'remoteAccessPendingRequest';
    var pollTimer = null;
    var pendingRequestCode = '';

    function setPendingMode(isPending) {
        if (isPending) {
            modalCard.classList.add('is-pending');
            hintText.textContent = 'Pedido enviado com sucesso.';
            closeBtn.textContent = 'Fechar';
            submitBtn.style.display = 'none';
            return;
        }

        modalCard.classList.remove('is-pending');
        hintText.textContent = 'Indique o nome completo do enfermeiro para enviar um pedido ao administrador.';
        closeBtn.textContent = 'Cancelar';
        submitBtn.style.display = '';
    }

    function savePendingRequest(code) {
        pendingRequestCode = code;
        try {
            window.localStorage.setItem(storageKey, code);
        } catch (error) {
            // ignora falhas de armazenamento no browser
        }
    }

    function clearPendingRequest() {
        pendingRequestCode = '';
        try {
            window.localStorage.removeItem(storageKey);
        } catch (error) {
            // ignora falhas de armazenamento no browser
        }
    }

    function loadPendingRequest() {
        try {
            var savedCode = (window.localStorage.getItem(storageKey) || '').trim();
            return savedCode;
        } catch (error) {
            return '';
        }
    }

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
        if (!pendingRequestCode) {
            nurseInput.focus();
        }
    }

    function closeModal() {
        if (!pendingRequestCode) {
            stopPolling();
        }
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
                    clearPendingRequest();
                    setPendingMode(false);
                    setStatus('Pedido aprovado. A abrir sessão...', false);
                    window.location.href = data.redirect_url;
                    return;
                }

                if (data.status === 'rejected') {
                    stopPolling();
                    clearPendingRequest();
                    setPendingMode(false);
                    setStatus('Pedido rejeitado pelo administrador.', true);
                    return;
                }

                if (data.status === 'expired') {
                    stopPolling();
                    clearPendingRequest();
                    setPendingMode(false);
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
        if (pendingRequestCode) {
            return;
        }

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
        .then(function (response) {
            return response.text().then(function (raw) {
                var data;
                try {
                    data = JSON.parse(raw);
                } catch (parseError) {
                    var snippet = (raw || '').replace(/\s+/g, ' ').trim().slice(0, 120);
                    throw new Error('HTTP ' + response.status + ' — resposta inválida: ' + (snippet || 'vazia'));
                }
                return { response: response, data: data };
            });
        })
        .then(function (result) {
            submitBtn.disabled = false;
            var data = result.data;
            if (!data || data.ok !== true || !data.request_code) {
                setStatus((data && data.message) ? data.message : ('Falha ao criar pedido (HTTP ' + result.response.status + ').'), true);
                return;
            }

            savePendingRequest(data.request_code);
            setPendingMode(true);
            setStatus('Pedido enviado. Aguarde aprovacao do administrador.', false);
            pollStatus(data.request_code);
        })
        .catch(function (error) {
            submitBtn.disabled = false;
            var detail = (error && error.message) ? error.message : 'sem detalhe';
            setStatus('Erro de comunicação ao enviar pedido (' + detail + ').', true);
        });
    });

    var existingRequestCode = loadPendingRequest();
    if (existingRequestCode) {
        savePendingRequest(existingRequestCode);
        setPendingMode(true);
        setStatus('Pedido enviado. Aguarde aprovacao do administrador.', false);
        pollStatus(existingRequestCode);
    } else {
        setPendingMode(false);
    }
})();
</script>

</body>
</html>
