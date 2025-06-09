<?php
session_start();
require_once 'includes/db.php';

$category_id = $_GET['category_id'] ?? '';
$where = $category_id ? "WHERE j.category_id = :category_id AND j.is_active = 1" : "WHERE j.is_active = 1";
$stmt = $pdo->prepare("SELECT j.*, c.name as category_name FROM jobs j JOIN categories c ON j.category_id = c.id $where ORDER BY j.created_at DESC");
if ($category_id) {
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
}
$stmt->execute();
$jobs = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$user_applications = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT job_id FROM applications WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_applications = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply']) && isset($_SESSION['user_id'])) {
    $job_id = $_POST['job_id'];
    $user_id = $_SESSION['user_id'];
    
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ? AND job_id = ?");
    $checkStmt->execute([$user_id, $job_id]);
    if ($checkStmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO applications (user_id, job_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $job_id]);
    }
    
    header("Location: index.php?applied=true");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Empregos Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css" />
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
                        <a class="btn btn-primary" href="register.php" style="background-color:#e53935; border-color:#fc5a03; color:#fff;">Cadastre-se</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<header class="hero-section">
    <div class="container">
        <form method="GET" class="filter-form">
            <select class="form-select" id="category_id" name="category_id" onchange="this.form.submit()">
                <option value="">Ver categorias</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id']; ?>" <?= ($category_id == $category['id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</header>

<main class="container mt-5">
    <?php if (empty($jobs)): ?>
        <div class="text-center">
            <p class="lead">Não há vagas disponíveis.</p>
        </div>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($jobs as $job): ?>
                <li class="list-group-item bg-dark text-white mb-3 rounded shadow-sm p-4">
                    <div class="d-flex align-items-start gap-4">
                        <?php if (!empty($job['image'])): ?>
                            <img src="<?= htmlspecialchars($job['image']); ?>" alt="Imagem da vaga" style="width:64px; height:64px; object-fit:cover; border-radius:50%;">
                        <?php else: ?>
                            <i class="bi bi-briefcase-fill" style="font-size: 2rem;"></i>
                        <?php endif; ?>

                        <div class="flex-grow-1">
                            <h5 class="mb-1"><?= htmlspecialchars($job['title']); ?></h5>
                            <span class="badge bg-secondary mb-2"><?= htmlspecialchars($job['category_name']); ?></span>
                            <p class="mb-2"><?= htmlspecialchars($job['description']); ?></p>
                            <div class="text-muted mb-2"><strong>Contato:</strong> <?= htmlspecialchars($job['contact']); ?></div>

                            <?php if (isset($_SESSION['user_id']) && (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin'])): ?>
                                <?php if (in_array($job['id'], $user_applications)): ?>
                                    <button class="btn btn-outline-secondary btn-sm" disabled>Inscrito!</button>
                                <?php else: ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="job_id" value="<?= $job['id']; ?>" />
                                        <button type="submit" name="apply" class="btn btn-primary btn-sm">Candidatar</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
