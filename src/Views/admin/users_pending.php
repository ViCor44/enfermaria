<?php
$nome = $_SESSION['user_name'] ?? 'Administrador';
$baseUrl = '/enfermaria/public/index.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Enfermaria | Aprovação de Utilizadores</title>
    <link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">

    <style>
        body { margin:0; font-family: system-ui, sans-serif; background:#f3f6fb; }
        header {
            background:#1f6feb;
            color:#fff;
            padding:1rem 2rem;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }
        .logo { font-weight:700; }
        .user-info { font-size:.9rem; text-align:right; }
        .user-info a { color:#fff; text-decoration:underline; margin-left:.5rem; }
        main { padding:2rem; max-width:1000px; margin:0 auto; }
        h1 { margin-top:0; }
        table { width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 8px 20px rgba(0,0,0,.06); }
        th, td { padding:.8rem; text-align:left; border-bottom:1px solid #eee; font-size:.9rem; }
        th { background:#f0f4ff; font-weight:600; }
        tr:last-child td { border-bottom:none; }
        .badge { padding:.2rem .5rem; border-radius:999px; font-size:.75rem; background:#e5f2ff; color:#1f6feb; }
        .actions { display:flex; gap:.5rem; }
        .btn {
            border:none;
            border-radius:6px;
            padding:.4rem .7rem;
            font-size:.85rem;
            cursor:pointer;
        }
        .btn-approve { background:#27ae60; color:#fff; }
        .btn-reject { background:#e74c3c; color:#fff; }
        .no-data { margin-top:1rem; color:#777; }
        .flash { margin-bottom:1rem; padding:.7rem; border-radius:6px; font-size:.9rem; }
        .flash-error { background:#ffe0e0; color:#900; }
        .flash-success { background:#e6ffed; color:#047857; }
        .top-links { margin-bottom:1rem; font-size:.9rem; }
        .top-links a { margin-right:1rem; text-decoration:none; color:#1f6feb; }
    </style>
</head>
<body>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<main>
    <h1>Aprovação de Utilizadores</h1>

    <div class="top-links">
        <a href="<?= $baseUrl ?>?route=dashboard">← Voltar ao Dashboard</a>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="flash flash-error">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="flash flash-success">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($pendingUsers)): ?>
        <p class="no-data">Não existem utilizadores pendentes de aprovação.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Perfil</th>
                    <th>Data de pedido</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pendingUsers as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['full_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($u['role_name']) ?></span></td>
                    <td><?= htmlspecialchars($u['created_at']) ?></td>
                    <td>
                        <div class="actions">
                            <!-- Formulário de APROVAR com escolha de perfil -->
                            <form method="post" action="<?= $baseUrl ?>?route=admin_users_action" style="margin:0; display:flex; gap:.5rem; align-items:center;">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="action" value="approve">

                                <select name="role_id">
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= (int)$role['id'] ?>" <?= $role['id'] == $u['role_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($role['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button class="btn btn-approve" type="submit">Aprovar</button>
                            </form>

                            <!-- Formulário de REJEITAR (sem alterar role) -->
                            <form method="post" action="<?= $baseUrl ?>?route=admin_users_action" style="margin:0;">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="reason" value="Rejeitado pelo administrador.">
                                <button class="btn btn-reject" type="submit">Rejeitar</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

</body>
</html>
