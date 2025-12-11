<?php
    $nome = $nome ?? ($_SESSION['user_name'] ?? 'Utilizador');
    $role = $role ?? ($_SESSION['role'] ?? '');
    $lastLogin = $lastLogin ?? ($_SESSION['last_login'] ?? null);
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
    </style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>

<main>
    <h1>Dashboard</h1>
    <p class="subtitle">
        Bem-vindo, <?= htmlspecialchars($nome) ?>.
        Aqui vais ter um resumo rápido dos incidentes e tratamentos.
    </p>

    <div class="cards">
        <div class="card">
            <h2>Incidentes de hoje</h2>
            <div class="value"><?= isset($incidentsToday) ? (int)$incidentsToday : 0 ?></div>
        </div>
        <div class="card">
            <h2>Tratamentos em curso</h2>
            <div class="value"><?= isset($treatmentsInProgress) ? (int)$treatmentsInProgress : 0 ?></div>

        </div>
        <div class="card">
            <h2>Último acesso</h2>
            <div class="value">
                <?= $lastLogin ? htmlspecialchars($lastLogin) : '—' ?>
            </div>
        </div>
    </div>
</main>

</body>
</html>
