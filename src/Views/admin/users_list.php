<?php
$baseUrl = '/enfermaria/public/index.php';
$nome = $_SESSION['user_name'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Enfermaria · Gestão de Utilizadores</title>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">

<style>
    body { 
        margin: 0; 
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; 
        background: #f5f7fb; 
        color: #333; 
    }
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
        max-width: 1200px; 
        margin: 0 auto; 
        padding: 2rem; 
        text-align: center; /* Centraliza para consistência */
    }
    h1 { 
        margin-top: 0; 
        font-size: 2rem;
        color: #1f6feb;
    }
    table { 
        width: 100%; 
        border-collapse: collapse; 
        background: #fff;
        border-radius: 12px; 
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        margin: 0 auto 1.5rem;
    }
    th, td { 
        padding: 0.8rem 1rem; 
        border-bottom: 1px solid #eee; 
        font-size: .95rem; 
        text-align: left; 
    }
    th { 
        background: #f0f4ff; 
        font-weight: 600;
        color: #555;
    }
    tr:last-child td { 
        border-bottom: none; 
    }
    tr:hover {
        background: #f8faff;
    }
    .badge { 
        display: inline-block; 
        padding: 0.3rem 0.7rem; 
        border-radius: 999px;
        font-size: .8rem; 
        background: #e5f2ff; 
        color: #1f6feb;
        font-weight: 500;
    }
    .actions { 
        display: flex; 
        gap: 0.5rem; 
    }
    .btn {
        border: none;
        border-radius: 6px;
        padding: 0.5rem 1rem;
        font-size: .9rem;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.1s ease;
    }
    .btn:hover {
        transform: translateY(-2px);
    }
    .btn-outline { 
        background: #f0f4ff; 
        color: #1f6feb; 
        border: 1px solid #1f6feb;
    }
    .btn-outline:hover { 
        background: #e5f2ff; 
    }
    .btn-danger { 
        background: #e74c3c; 
        color: #fff; 
    }
    .btn-danger:hover { 
        background: #d43f2f; 
    }
    .btn-approve { 
        background: #27ae60; 
        color: #fff; 
    }
    .btn-approve:hover { 
        background: #219e52; 
    }
    .no-data { 
        margin-top: 1rem; 
        color: #777; 
        font-size: 1rem;
    }
    .flash { 
        margin-bottom: 1.5rem; 
        padding: 1rem; 
        border-radius: 8px; 
        font-size: .95rem; 
    }
    .flash-error { 
        background: #ffe0e0; 
        color: #900; 
    }
    .flash-success { 
        background: #e6ffed; 
        color: #047857; 
    }
    .top-links { 
        margin-bottom: 1.5rem; 
        font-size: .95rem; 
        text-align: left;
    }
    .top-links a { 
        margin-right: 1rem; 
        text-decoration: none; 
        color: #1f6feb; 
        transition: text-decoration 0.2s ease;
    }
    .top-links a:hover { 
        text-decoration: underline; 
    }
    select {
        padding: 0.5rem 0.8rem;
        border-radius: 6px;
        border: 1px solid #ddd;
        background: #fff;
        transition: border-color 0.2s ease;
    }
    select:focus {
        border-color: #1f6feb;
        outline: none;
    }
    .separator {
        border: none;
        border-top: 1px solid #ddd;
        margin: 2rem 0;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        main {
            padding: 1rem;
        }
        table {
            font-size: 0.85rem;
        }
        .actions {
            flex-direction: column;
            gap: 0.5rem;
        }
    }
</style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main>
    <h1>Gestão de Utilizadores</h1>

    <hr class="separator"> <!-- Adicionado para consistência -->

    <div class="top-links">
        <a href="<?= $baseUrl ?>?route=admin_users">← Voltar</a>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="flash flash-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="flash flash-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr><th>Nome</th><th>Telefone</th><th>Email</th><th>Perfil</th><th>Aprovado</th><th>Ações</th></tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): $isDeleted = !empty($u['deleted_at']); ?>
                <tr>
                    <td><?= htmlspecialchars($u['full_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <form method="post" action="<?= $baseUrl ?>?route=admin_users_change_role" style="display:flex;gap:.5rem;align-items:center;">
                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                            <select name="role_id"  <?= ($u['id'] == $_SESSION['user_id']) ? 'disabled' : '' ?>>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= (int)$role['id'] ?>" <?= $role['id']==$u['role_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ((int)($_SESSION['user_id'] ?? 0) === (int)$u['id']): ?>
                                <button class="btn btn-approve" disabled>Guardar</button>   
                            <?php else: ?>      
                                <button class="btn btn-approve" type="submit">Guardar</button>
                            <?php endif; ?>
                        </form>
                    </td>
                    <td><?= $u['approved'] ? 'Sim' : 'Não' ?></td>
                    <td>
                        
                        <form method="post" action="<?= $baseUrl ?>?route=admin_user_delete" style="display:inline-block;margin-left:8px;">
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <?php if ($isDeleted): ?>
                                <input type="hidden" name="action" value="restore">
                                <button class="btn btn-outline" type="submit">Restaurar</button>
                            <?php else: ?>
                                <input type="hidden" name="action" value="delete">
                                <!-- impede apagar se este for tu próprio na view (apagar via controller também valida) -->
                                <?php if ((int)($_SESSION['user_id'] ?? 0) === (int)$u['id']): ?>
                                    <button class="btn btn-danger" disabled>Apagar</button>
                                <?php else: ?>
                                    <button class="btn btn-danger" type="submit" onclick="return confirm('Confirmar remoção do utilizador?')">Apagar</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>