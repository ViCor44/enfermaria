<?php
// Valores de fallback
$baseUrl = $baseUrl ?? '/enfermaria/public/index.php';
$nome    = $nome    ?? ($_SESSION['user_name'] ?? 'Utilizador');
$role    = $role    ?? ($_SESSION['role'] ?? '');
$roleLabel = $role;
$route = $_GET['route'] ?? 'dashboard';
?>
<style>
    .brand-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
    }

    .logo-sae {
        height: 48px;      /* Ajustável conforme preferir */
        width: auto;
        display: block;
    }
</style>
<header class="topbar">
    <div class="topbar-inner">
        <!-- Marca -->
        <div class="brand">
            <div class="brand-logo">
                <img href="<?= $baseUrl ?>?route=about" src="/enfermaria/public/assets/img/logo-sae.png" alt="SAE" class="logo-sae">
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
