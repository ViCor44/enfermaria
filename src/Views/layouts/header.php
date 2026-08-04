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
</style>
<header class="topbar">
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

            <?php if (in_array($role, ['Administrador', 'Enfermeiro'], true)): ?>
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

                    <span class="nav-link nav-btn">
                        Novo
                    </span>

                    <div class="nav-menu">
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
                <a href="/enfermaria/public/index.php?route=admin_stats"
                    class="nav-link <?= ($_GET['route'] ?? '') === 'admin_stats' ? 'active' : '' ?>">
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
            <a href="<?= $baseUrl ?>?route=logout" class="btn-logout">Sair</a>
        </div>
    </div>
</header>
