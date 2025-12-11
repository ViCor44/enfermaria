<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Enfermaria | Login</title>
    <style>
        body {
            margin: 0;
            font-family: system-ui, sans-serif;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #fff;
            padding: 2rem;
            width: 100%;
            max-width: 380px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,.15);
        }
        h1 {
            margin: 0 0 1rem;
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin-top: 1rem;
            font-weight: 600;
            color: #555;
        }
        input {
            width: 100%;
            padding: .7rem;
            margin-top: .3rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }
        button {
            margin-top: 1.5rem;
            width: 100%;
            padding: .8rem;
            border: none;
            border-radius: 8px;
            background: #4facfe;
            color: white;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover {
            background: #3498db;
        }
        .error {
            background: #ffe0e0;
            color: #900;
            padding: .7rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            text-align: center;
        }
        footer {
            text-align: center;
            font-size: .85rem;
            margin-top: 1rem;
            color: #777;
        }
    </style>
</head>
<body>

<div class="card">
    <h1>Enfermaria</h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success_register'])): ?>
        <div class="success">
            <?= htmlspecialchars($_SESSION['success_register']); unset($_SESSION['success_register']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="success">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/enfermaria/public/index.php?route=login_submit">

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Entrar</button>
    </form>

    <footer>
        Sistema interno · Parque Aquático<br>
        <a href="/enfermaria/public/index.php?route=register" style="color:#1f6feb;text-decoration:none;font-size:.9rem;">
            Não tem conta? Registe-se
        </a>
    </footer>
</div>

</body>
</html>
