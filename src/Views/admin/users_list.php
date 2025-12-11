<?php
$nome = $_SESSION['user_name'] ?? 'Administrador';
$baseUrl = '/enfermaria/public/index.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Enfermaria · Gestão de Utilizadores</title>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">

<style>
/* styling simples — podes adaptar ao teu CSS */
table { width:100%; border-collapse:collapse; }
th,td { padding: .6rem; border-bottom:1px solid #eee; text-align:left; }
select { padding:.35rem; border-radius:4px; }
.btn { padding:.4rem .6rem; border-radius:6px; border:none; cursor:pointer; }
.btn-save { background:#1f6feb; color:#fff; }
.flash { margin-bottom:1rem; padding:.6rem; border-radius:6px; }
.flash-success { background:#e6ffed; color:#047857; }
.flash-error { background:#ffe0e0; color:#900; }
</style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main style="padding:1.5rem;">
    <h1>Gestão de Utilizadores</h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="flash flash-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="flash flash-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr><th>Nome</th><th>Email</th><th>Perfil</th><th>Aprovado</th><th>Ações</th></tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['full_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <form method="post" action="<?= $baseUrl ?>?route=admin_users_change_role" style="display:flex;gap:.5rem;align-items:center;">
                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                            <select name="role_id">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= (int)$role['id'] ?>" <?= $role['id']==$u['role_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-save" type="submit">Guardar</button>
                        </form>
                    </td>
                    <td><?= $u['approved'] ? 'Sim' : 'Não' ?></td>
                    <td><!-- aqui podes adicionar outras ações (reset pwd, apagar...) --></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>
