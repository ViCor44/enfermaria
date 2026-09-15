<?php
// Valores de fallback
$baseUrl = $baseUrl ?? '/enfermaria/public/index.php';
$nome    = $nome    ?? ($_SESSION['user_name'] ?? 'Utilizador');
$role    = $role    ?? ($_SESSION['role'] ?? '');
$roleLabel = $role;
$route = $_GET['route'] ?? 'dashboard';
if (!isset($pendingApprovalsCount)) {
    $pendingApprovalsCount = ($role === 'Administrador')
        ? count(\App\Models\User::getPendingApprovals())
        : 0;
}
?>
<style>
    .topbar {
        background: #1f6feb;
        color: #fff;
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
    }
    .topbar-inner {
        max-width: 1400px;
        margin: 0 auto;
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .brand {
        display: flex;
        align-items: center;
    }
    .brand-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }
    .logo-sae {
        height: 48px;
        width: auto;
        display: block;
        border-radius: 4px; /* Suaviza as bordas da imagem */
    }
    .brand-text-title {
        font-weight: 700;
        font-size: 1.2rem;
        letter-spacing: 0.03em;
    }
    .brand-text-sub {
        font-size: 0.85rem;
        opacity: 0.9;
    }
    .main-nav {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    .nav-link {
        color: #fff;
        text-decoration: none;
        font-size: 0.95rem;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        transition: background 0.2s ease, transform 0.1s ease;
    }
    .nav-link:hover {
        background: rgba(255,255,255,0.15);
        transform: translateY(-2px);
    }
    .nav-link.active {
        background: rgba(255,255,255,0.2);
        font-weight: 600;
    }
    .nav-link-with-badge {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
    }
    .nav-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 .35rem;
        border-radius: 999px;
        background: #ff4d4f;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 700;
        line-height: 1;
    }
    .user-area {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .user-pill {
        background: rgba(255,255,255,0.15);
        padding: 0.5rem 1rem;
        border-radius: 999px;
        text-align: center;
        font-size: 0.9rem;
    }
    .user-role {
        font-size: 0.75rem;
        opacity: 0.8;
    }
    .btn-logout {
        background: #fff;
        color: #1f6feb;
        padding: 0.5rem 1.2rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: background 0.2s ease, color 0.2s ease, transform 0.1s ease;
    }
    .btn-logout:hover {
        background: #f0f4ff;
        color: #0f5bdb;
        transform: translateY(-2px);
    }
    .btn-restore-session {
        background: #fff6d6;
        color: #7b5c00;
        border: 1px solid #ecd27b;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.82rem;
        white-space: nowrap;
    }
    .btn-restore-session:hover {
        background: #fff1bf;
    }

    .nav-dropdown {
        position: relative;
    }

    .nav-btn {
        cursor: pointer;
    }

    .nav-menu {
        display: none;
        position: absolute;
        top: 105%;
        left: 0;
        background: white;
        min-width: 190px;
        border-radius: 8px;
        box-shadow: 0 8px 18px rgba(0,0,0,0.15);
        z-index: 999;
    }

    .nav-menu a {
        display: block;
        padding: .65rem 1rem;
        text-decoration: none;
        color: #333;
    }

    .nav-menu a:hover {
        background: #f1f5ff;
    }

    .nav-dropdown:hover .nav-menu {
        display: block;
    }

    /* Responsividade */
    @media (max-width: 1024px) {
        .topbar-inner {
            flex-direction: column;
            gap: 1rem;
        }
        .main-nav {
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
        }
    }
    @media (max-width: 768px) {
        .topbar {
            padding: 1rem;
        }
        .brand-text-title {
            font-size: 1rem;
        }
        .brand-text-sub {
            font-size: 0.75rem;
        }
        .nav-link {
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
        }
        .user-pill {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }
        .btn-logout {
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
        }

        .nav-item {
            position: relative;
        }

        .dropdown-toggle {
            cursor: pointer;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: #fff;
            min-width: 190px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,.12);
            padding: .4rem 0;
            display: none;
            z-index: 200;
        }

        .dropdown-menu a {
            display: block;
            padding: .6rem 1rem;
            color: #333;
            text-decoration: none;
            font-size: .9rem;
        }

        .dropdown-menu a:hover {
            background: #f0f4ff;
        }

        /* hover abre */
        .nav-item.dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu a {
            color: #1f6feb;
            text-decoration: none;
            font-weight: 500;
        }

        .dropdown-menu a:hover {
            text-decoration: underline;
            background: #f0f4ff;
        }

    }

    :root {
        --app-sidebar-width: 272px;
    }

    body {
        padding-left: var(--app-sidebar-width);
        transition: padding-left 0.2s ease;
    }

    .topbar {
        position: fixed;
        inset: 0 auto 0 0;
        z-index: 1200;
        width: var(--app-sidebar-width);
        height: 100vh;
        padding: 0;
        overflow: hidden;
        background: #123f72;
        box-shadow: 8px 0 28px rgba(18, 45, 77, 0.14);
    }

    .topbar-inner {
        width: 100%;
        height: 100%;
        max-width: none;
        margin: 0;
        padding: 22px 16px 16px;
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        gap: 22px;
    }

    .brand {
        min-height: 58px;
        padding: 0 6px 18px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.13);
    }

    .brand-logo { margin-right: 11px; }
    .logo-sae { width: 42px; height: 42px; object-fit: contain; }
    .brand-text-title { font-size: 0.98rem; line-height: 1.25; letter-spacing: 0; }
    .brand-text-sub { margin-top: 3px; font-size: 0.7rem; line-height: 1.3; }

    .main-nav {
        display: flex;
        min-height: 0;
        flex: 1;
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        gap: 5px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,.25) transparent;
    }

    .nav-link,
    .nav-btn {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        min-height: 43px;
        padding: 0 13px;
        border: 1px solid transparent;
        border-radius: 7px;
        color: #e8f1fb;
        font-size: 0.9rem;
        white-space: nowrap;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .nav-link:hover,
    .nav-btn:hover {
        border-color: rgba(255,255,255,.12);
        background: rgba(255,255,255,.09);
        transform: none;
    }

    .nav-link.active,
    .nav-dropdown.active > .nav-btn {
        border-color: rgba(255,255,255,.2);
        background: #fff;
        color: #174f8c;
        font-weight: 700;
    }

    .nav-dropdown { position: static; }

    .nav-btn {
        width: 100%;
        appearance: none;
        background: transparent;
        font-family: inherit;
        text-align: left;
        cursor: pointer;
    }

    .nav-menu {
        position: static;
        display: none;
        min-width: 0;
        margin: 4px 0 5px 13px;
        padding-left: 12px;
        border-left: 1px solid rgba(255,255,255,.22);
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .nav-dropdown:hover .nav-menu,
    .nav-dropdown:focus-within .nav-menu,
    .nav-dropdown.submenu-open .nav-menu {
        display: grid;
    }

    .nav-menu a {
        padding: 9px 11px;
        border-radius: 6px;
        color: #d4e5f6;
        font-size: 0.84rem;
    }

    .nav-menu a:hover,
    .nav-menu a.active {
        background: rgba(255,255,255,.1);
        color: #fff;
    }

    .user-area {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        padding-top: 15px;
        border-top: 1px solid rgba(255,255,255,.13);
    }

    .user-pill {
        grid-column: 1 / -1;
        padding: 10px 12px;
        border-radius: 7px;
        background: rgba(255,255,255,.09);
        font-size: 0.84rem;
        text-align: left;
    }

    .btn-logout,
    .btn-restore-session {
        display: inline-flex;
        min-height: 38px;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        border: 1px solid rgba(255,255,255,.24);
        border-radius: 7px;
        background: transparent;
        color: #fff;
        font-size: 0.8rem;
    }

    .btn-restore-session { grid-column: 1 / -1; }

    .sidebar-toggle {
        position: fixed;
        top: 16px;
        left: calc(var(--app-sidebar-width) - 17px);
        z-index: 1300;
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        padding: 0;
        border: 1px solid #d8e3ef;
        border-radius: 50%;
        background: #fff;
        color: #234a75;
        box-shadow: 0 5px 14px rgba(27, 56, 88, 0.18);
        cursor: pointer;
        transition: left 0.2s ease, transform 0.2s ease;
    }

    .sidebar-toggle svg { width: 18px; height: 18px; }

    .sidebar-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1100;
        display: none;
        background: rgba(12, 27, 45, 0.48);
    }

    body.sidebar-collapsed { padding-left: 0; }
    body.sidebar-collapsed .topbar { transform: translateX(-100%); }
    body.sidebar-collapsed .sidebar-toggle { left: 16px; transform: rotate(180deg); }
    .topbar { transition: transform 0.2s ease; }

    @media (max-width: 900px) {
        body { padding-left: 0; }
        .topbar { transform: translateX(-100%); }
        .sidebar-toggle { left: 16px; transform: rotate(180deg); }
        body.sidebar-open { overflow: hidden; }
        body.sidebar-open .topbar { transform: translateX(0); }
        body.sidebar-open .sidebar-toggle { left: calc(var(--app-sidebar-width) - 17px); transform: none; }
        body.sidebar-open .sidebar-backdrop { display: block; }
    }
</style>
<button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Recolher menu" aria-controls="appSidebar" aria-expanded="true">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
        <path d="M15 18l-6-6 6-6"/>
    </svg>
</button>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<header class="topbar" id="appSidebar">
    <div class="topbar-inner">
        <!-- Marca -->
        <div class="brand">
            <div class="brand-logo">
                <a href="<?= $baseUrl ?>?route=about">
                    <img src="/enfermaria/public/assets/img/logo-sae.png" alt="SAE" class="logo-sae">
                </a>
            </div>
            <div>
                <div class="brand-text-title">Sistema de Apoio à Enfermaria</div>
                <div class="brand-text-sub">Gestão de Ocorrências e Tratamentos</div>
            </div>
        </div>

        <!-- Navegação principal -->
        <nav class="main-nav">
            <a href="<?= $baseUrl ?>?route=dashboard"
               class="nav-link <?= $route === 'dashboard' ? 'active' : '' ?>">
                Dashboard
            </a>

            <?php if (in_array($role, ['Administrador', 'Enfermeiro', 'Manager'], true)): ?>
                <a href="<?= $baseUrl ?>?route=park_schedule"
                   class="nav-link <?= in_array($route, ['park_schedule', 'park_schedule_save'], true) ? 'active' : '' ?>">
                    Escala
                </a>
            <?php endif; ?>

            <?php if ($role === 'Administrador'): ?>
                
                <a href="<?= $baseUrl ?>?route=admin_incidents"
                class="nav-link <?= $route === 'admin_incidents' ? 'active' : '' ?>">
                    Ocorrências
                </a>

                <a href="<?= $baseUrl ?>?route=admin_internal_records"
                class="nav-link <?= $route === 'admin_internal_records' ? 'active' : '' ?>">
                    Registos Internos
                </a>

                <a href="<?= $baseUrl ?>?route=admin_treatments"
                class="nav-link <?= $route === 'admin_treatments' ? 'active' : '' ?>">
                    Tratamentos
                </a>

                <a href="<?= $baseUrl ?>?route=admin_users"
                class="nav-link nav-link-with-badge <?= $route === 'admin_users' ? 'active' : '' ?>">
                    Utilizadores
                    <?php if ($pendingApprovalsCount > 0): ?>
                        <span class="nav-badge" title="Utilizadores por aprovar">
                            <?= (int)$pendingApprovalsCount ?>
                        </span>
                    <?php endif; ?>
                </a>

                <a href="<?= $baseUrl ?>?route=admin_stats"
                class="nav-link <?= $route === 'admin_stats' ? 'active' : '' ?>">
                    Estatísticas
                </a>
            <?php endif; ?>


            <?php if ($role === 'Enfermeiro'): ?>
                <div class="nav-dropdown <?= in_array($route, ['incidents_new','internal_new']) ? 'active' : '' ?>">

                    <button class="nav-link nav-btn" type="button" aria-expanded="false" aria-controls="newRecordMenu">
                        Novo
                    </button>

                    <div class="nav-menu" id="newRecordMenu">
                        <a href="<?= $baseUrl ?>?route=internal_new"
                        class="<?= $route === 'internal_new' ? 'active' : '' ?>">
                            Situação Menor
                        </a>

                        <a href="<?= $baseUrl ?>?route=incidents_new"
                        class="<?= $route === 'incidents_new' ? 'active' : '' ?>">
                            Ocorrência
                        </a>
                    </div>
                </div>
                
                <a href="<?= $baseUrl ?>?route=admin_incidents"
                class="nav-link <?= $route === 'admin_incidents' ? 'active' : '' ?>">
                    Ocorrências
                </a>
                <a href="<?= $baseUrl ?>?route=admin_treatments"
                class="nav-link <?= $route === 'admin_treatments' ? 'active' : '' ?>">
                    Tratamentos
                </a>
            <?php endif; ?>

            <?php if ($role === 'Manager'): ?>

                <a href="<?= $baseUrl ?>?route=admin_internal_records"
                class="nav-link <?= $route === 'admin_internal_records' ? 'active' : '' ?>">
                    Registos Internos
                </a>

                <a href="<?= $baseUrl ?>?route=admin_incidents"
                   class="nav-link <?= $route === 'admin_incidents' ? 'active' : '' ?>">
                    Ocorrências
                </a>
                <a href="<?= $baseUrl ?>?route=admin_stats"
                    class="nav-link <?= $route === 'admin_stats' ? 'active' : '' ?>">
                    Estatísticas
                </a>
            <?php endif; ?>
        </nav>

        <!-- Área do utilizador -->
        <div class="user-area">
            <?php if (!empty($_SESSION['delegated_by_admin'])): ?>
                <a href="<?= $baseUrl ?>?route=admin_restore_session" class="btn-restore-session">
                    Voltar para sessão admin
                </a>
            <?php endif; ?>
            <div class="user-pill">
                <?= htmlspecialchars($nome) ?><br>
                <span class="user-role"><?= htmlspecialchars($roleLabel) ?></span>
            </div>
            <a href="<?= $baseUrl ?>?route=user_settings" class="btn-logout" title="Definições da conta">Definições</a>
            <a href="<?= $baseUrl ?>?route=logout" class="btn-logout">Sair</a>
        </div>
    </div>
</header>
<script>
(() => {
    const toggle = document.getElementById('sidebarToggle');
    const backdrop = document.getElementById('sidebarBackdrop');
    const sidebar = document.getElementById('appSidebar');
    const newRecordDropdown = sidebar.querySelector('.nav-dropdown');
    const newRecordButton = newRecordDropdown?.querySelector('.nav-btn');
    const mobileQuery = window.matchMedia('(max-width: 900px)');

    function isOpen() {
        return mobileQuery.matches
            ? document.body.classList.contains('sidebar-open')
            : !document.body.classList.contains('sidebar-collapsed');
    }

    function syncState() {
        const open = isOpen();
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Recolher menu' : 'Abrir menu');
    }

    function toggleSidebar() {
        if (mobileQuery.matches) {
            document.body.classList.toggle('sidebar-open');
        } else {
            document.body.classList.toggle('sidebar-collapsed');
        }
        syncState();
    }

    toggle.addEventListener('click', toggleSidebar);
    newRecordButton?.addEventListener('click', () => {
        if (!mobileQuery.matches) return;
        const open = newRecordDropdown.classList.toggle('submenu-open');
        newRecordButton.setAttribute('aria-expanded', String(open));
    });
    backdrop.addEventListener('click', () => {
        document.body.classList.remove('sidebar-open');
        syncState();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
            document.body.classList.remove('sidebar-open');
            toggle.focus();
            syncState();
        }
    });
    sidebar.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            if (mobileQuery.matches) document.body.classList.remove('sidebar-open');
        });
    });
    mobileQuery.addEventListener('change', () => {
        document.body.classList.remove('sidebar-open', 'sidebar-collapsed');
        newRecordDropdown?.classList.remove('submenu-open');
        newRecordButton?.setAttribute('aria-expanded', 'false');
        syncState();
    });
    syncState();
})();
</script>
