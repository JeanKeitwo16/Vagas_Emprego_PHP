<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($pageTitle ?? 'Empregos Online'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
</head>
<body>

<nav class="admin-navbar navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Empregos Online</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item me-3">
                        <span class="text-white">
                            Olá, <?= htmlspecialchars($_SESSION['username'] ?? 'Usuário'); ?>
                        </span>
                    </li>
                    <?php if (!empty($_SESSION['is_admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/jobs.php">Gerenciar Vagas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/categories.php">Gerenciar Categorias</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item ms-3">
                        <a class="btn btn-primary" href="logout.php">Sair</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item me-3">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary" href="register.php">Cadastro</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
