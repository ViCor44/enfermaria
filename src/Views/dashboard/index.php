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
        }

        /* layout principal do dashboard pode ficar como já tinhas */

        header {
            background: #1f6feb;
            color: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-weight: 700;
            letter-spacing: .03em;
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
            max-width: 1100px;
            margin: 0 auto;
        }
        h1 {
            margin-top: 0;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 1.2rem;
            box-shadow: 0 8px 20px rgba(0,0,0,.06);
        }
        .card h2 {
            font-size: 1rem;
            margin: 0 0 .5rem;
            color: #555;
        }
        .card .value {
            font-size: 1.8rem;
            font-weight: 700;
        }
        .subtitle {
            color: #777;
            font-size: .95rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 24px;
        }

        .dashboard-card {
            background: #fff;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.06);
        }

        .link-card {
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform 0.12s ease, box-shadow 0.12s ease;
        }

        .link-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 40px rgba(0,0,0,0.09);
        }

        .big-number {
            font-size: 28px;
            font-weight: 700;
            margin-top: 12px;
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
        <div class="big-number"><?= htmlspecialchars($lastLogin) ?></div>
    </div>

</div>
</main>

</body>
</html>
