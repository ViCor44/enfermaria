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
    <h1>Editar Utilizador</h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="post" action="<?= $baseUrl ?>?route=admin_user_update">
        <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
        <div class="form-group">
            <label>Nome completo</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Telefone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Perfil</label>
            <select name="role_id" class="form-control" required>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= (int)$r['id'] ?>" <?= (isset($user['role_id']) && $user['role_id'] == $r['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Gravar</button>
            <a class="btn btn-link" href="<?= $baseUrl ?>?route=admin_users">Cancelar</a>
        </div>
    </form>
    </main>

</body>
</html>