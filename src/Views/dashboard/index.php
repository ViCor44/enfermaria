<?php
    $nome = $nome ?? ($_SESSION['user_name'] ?? 'Utilizador');
    $role = $role ?? ($_SESSION['role'] ?? '');
    $lastLogin = $lastLogin ?? ($_SESSION['last_login'] ?? null);    
    $baseUrl = $baseUrl ?? '/enfermaria/public/index.php';

    // valores que já calculas no controller
    $today = date('Y-m-d');
    $AcidentesHoje = $AcidentesHoje ?? 0;
    $tratamentosEmCurso = $tratamentosEmCurso ?? 0;    
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Enfermaria | Dashboard</title>
    <link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">

    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f5f7fb;
            color: #333;
        }

        /* Estilos consistentes com a página de login: minimalista, centralizado, com tons de azul */
        header {
            background: #1f6feb;
            color: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .logo {
            font-weight: 700;
            letter-spacing: .03em;
            font-size: 1.2rem;
        }
        .user-info {
            font-size: .9rem;
            text-align: right;
        }
        .user-info a {
            color: #fff;
            text-decoration: underline;
            margin-left: .5rem;
        }
        main {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            text-align: center; /* Centraliza o conteúdo como na login */
        }
        h1 {
            margin-top: 0;
            font-size: 2rem;
            color: #1f6feb;
        }
        .subtitle {
            color: #777;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .dashboard-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-align: left;
        }

        .link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.1);
        }

        .dashboard-card h3 {
            font-size: 1.1rem;
            margin: 0 0 0.8rem;
            color: #555;
        }

        .big-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1f6feb;
        }

        /* Adiciona separador horizontal como na login, para consistência */
        .separator {
            border: none;
            border-top: 1px solid #ddd;
            margin: 2rem 0;
        }

        /* Responsividade melhorada */
        @media (max-width: 768px) {
            main {
                padding: 1rem;
            }
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>

<main>
    <h1>Dashboard</h1>
    <p class="subtitle">
        Bem-vindo, <?= htmlspecialchars($nome) ?>.
        Aqui vais ter um resumo rápido dos Acidentes e tratamentos.
    </p>

    <hr class="separator"> <!-- Adicionado para consistência com a login -->

    <div class="dashboard-grid">

        <!-- Acidentes de hoje -->
        <?php
            $AcidentesHref = $baseUrl . '?route=admin_incidents&from=' . $today . '&to=' . $today;
        ?>
        <a class="dashboard-card link-card" href="<?= htmlspecialchars($AcidentesHref) ?>">
            <h3>Acidentes de hoje</h3>
            <div class="big-number"><?= (int)$AcidentesHoje ?></div>
        </a>

        <!-- Tratamentos em curso -->
        <?php
            $tratamentosHref = $baseUrl . '?route=admin_treatments&status=em_curso';
        ?>
        <a class="dashboard-card link-card" href="<?= htmlspecialchars($tratamentosHref) ?>">
            <h3>Tratamentos em curso</h3>
            <div class="big-number"><?= (int)$tratamentosEmCurso ?></div>
        </a>

        <!-- Último acesso (sem link) -->
        <div class="dashboard-card">
            <h3>Último acesso</h3>
            <div class="big-number"><?= htmlspecialchars($lastLogin ?? 'N/A') ?></div>
        </div>

    </div>
</main>

</body>
</html>