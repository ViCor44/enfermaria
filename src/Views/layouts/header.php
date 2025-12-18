<?php
// Valores de fallback
$baseUrl = $baseUrl ?? '/enfermaria/public/index.php';
$nome    = $nome    ?? ($_SESSION['user_name'] ?? 'Utilizador');
$role    = $role    ?? ($_SESSION['role'] ?? '');
$roleLabel = $role;
$route = $_GET['route'] ?? 'dashboard';
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
                <div class="brand-text-sub">Gestão de Acidentes e Tratamentos</div>
            </div>
        </div>

        <!-- Navegação principal -->
        <nav class="main-nav">
            <a href="<?= $baseUrl ?>?route=dashboard"
               class="nav-link <?= $route === 'dashboard' ? 'active' : '' ?>">
                Dashboard
            </a>

            <?php if ($role === 'Administrador'): ?>
                <a href="<?= $baseUrl ?>?route=admin_incidents"
                   class="nav-link <?= $route === 'admin_incidents' ? 'active' : '' ?>">
                    Acidentes
                </a>
                <a href="<?= $baseUrl ?>?route=admin_treatments"
                   class="nav-link <?= $route === 'admin_treatments' ? 'active' : '' ?>">
                    Tratamentos
                </a>
                <a href="<?= $baseUrl ?>?route=admin_users"
                   class="nav-link <?= $route === 'admin_users' ? 'active' : '' ?>">
                    Utilizadores
                </a>
                <a href="/enfermaria/public/index.php?route=admin_stats"
                    class="nav-link <?= ($_GET['route'] ?? '') === 'admin_stats' ? 'active' : '' ?>">
                    Estatísticas
                </a>
            <?php endif; ?>

            <?php if ($role === 'Enfermeiro'): ?>
                <a href="<?= $baseUrl ?>?route=incidents_new"
                class="nav-link <?= $route === 'incidents_new' ? 'active' : '' ?>">
                    Novo Acidente
                </a>                
                <a href="<?= $baseUrl ?>?route=admin_incidents"
                class="nav-link <?= $route === 'admin_incidents' ? 'active' : '' ?>">
                    Acidentes
                </a>
                <a href="<?= $baseUrl ?>?route=admin_treatments"
                class="nav-link <?= $route === 'admin_treatments' ? 'active' : '' ?>">
                    Tratamentos
                </a>
            <?php endif; ?>

            <?php if ($role === 'Manager'): ?>
                <a href="<?= $baseUrl ?>?route=admin_incidents"
                   class="nav-link <?= $route === 'admin_incidents' ? 'active' : '' ?>">
                    Acidentes
                </a>
                <a href="/enfermaria/public/index.php?route=admin_stats"
                    class="nav-link <?= ($_GET['route'] ?? '') === 'admin_stats' ? 'active' : '' ?>">
                    Estatísticas
                </a>
            <?php endif; ?>
        </nav>

        <!-- Área do utilizador -->
        <div class="user-area">
            <div class="user-pill">
                <?= htmlspecialchars($nome) ?><br>
                <span class="user-role"><?= htmlspecialchars($roleLabel) ?></span>
            </div>
            <a href="<?= $baseUrl ?>?route=logout" class="btn-logout">Sair</a>
        </div>
    </div>
</header>