<?php
// Valores de fallback
$baseUrl = $baseUrl ?? '/enfermaria/public/index.php';
$nome    = $nome    ?? ($_SESSION['user_name'] ?? 'Utilizador');
$role    = $role    ?? ($_SESSION['role'] ?? '');
$roleLabel = $role;
$route = $_GET['route'] ?? 'dashboard';
?>
<header class="topbar">
    <div class="topbar-inner">
        <!-- Marca -->
        <div class="brand">
            <div class="brand-logo">E</div>
            <div>
                <div class="brand-text-title">Enfermaria • Parque Aquático</div>
                <div class="brand-text-sub">Gestão de incidentes e tratamentos</div>
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
                    Incidentes
                </a>
                <a href="<?= $baseUrl ?>?route=admin_users"
                   class="nav-link <?= $route === 'admin_users' ? 'active' : '' ?>">
                    Utilizadores
                </a>
            <?php endif; ?>

            <?php if ($role === 'Enfermeiro'): ?>
                <a href="<?= $baseUrl ?>?route=incidents_new"
                class="nav-link <?= $route === 'incidents_new' ? 'active' : '' ?>">
                    Novo incidente
                </a>
                <a href="<?= $baseUrl ?>?route=incidents_my"
                class="nav-link <?= $route === 'incidents_my' ? 'active' : '' ?>">
                    Meus incidentes
                </a>
                <a href="<?= $baseUrl ?>?route=admin_incidents"
                class="nav-link <?= $route === 'admin_incidents' ? 'active' : '' ?>">
                    Todos os incidentes
                </a>
                <a href="<?= $baseUrl ?>?route=treatments_my"
                class="nav-link <?= $route === 'treatments_my' ? 'active' : '' ?>">
                    Meus tratamentos
                </a>
            <?php endif; ?>

            <?php if ($role === 'Manager'): ?>
                <a href="<?= $baseUrl ?>?route=admin_incidents"
                   class="nav-link <?= $route === 'admin_incidents' ? 'active' : '' ?>">
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
