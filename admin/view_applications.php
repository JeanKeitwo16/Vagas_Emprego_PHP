<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['job_id'])) {
    header("Location: jobs.php");
    exit;
}
$job_id = $_GET['job_id'];

$stmt_job = $pdo->prepare("SELECT title FROM jobs WHERE id = ?");
$stmt_job->execute([$job_id]);
$job = $stmt_job->fetch();

if (!$job) {
    header("Location: jobs.php");
    exit;
}

$stmt_apps = $pdo->prepare("SELECT u.name, u.email, u.photo, u.linkedin FROM applications a JOIN users u ON a.user_id = u.id WHERE a.job_id = ?");
$stmt_apps->execute([$job_id]);
$applications = $stmt_apps->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin - Inscritos na Vaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/admin.css" />
    <style>
        main.main-content {
            margin-top: 2rem;
        }
        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .header-flex h2 {
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0;
        }
        .applicants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        .applicant-card {
            background-color: var(--bs-body-bg);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgb(0 0 0 / 0.15);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: box-shadow 0.3s ease;
        }
        .applicant-card:hover {
            box-shadow: 0 4px 16px rgb(0 0 0 / 0.25);
        }
        .applicant-photo {
            flex-shrink: 0;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--bs-primary);
        }
        .applicant-info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }
        .applicant-name {
            font-weight: 600;
            font-size: 1.1rem;
        }
        .applicant-email {
            font-size: 0.9rem;
            color: var(--bs-secondary-text);
            word-break: break-word;
        }
        .applicant-linkedin {
            margin-top: 0.5rem;
        }
        .btn-back {
            display: flex;
            justify-content: flex-end;
            margin-top: 2rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg admin-navbar">
    <div class="container">
        <a class="navbar-brand" href="../index.php">Empregos Online</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link active" href="jobs.php">Gerenciar Vagas</a></li>
                <li class="nav-item"><a class="nav-link" href="categories.php">Categorias</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Admin</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../login.php">Sair</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="main-content container">
    <div class="header-flex">
        <h2>Inscritos na Vaga: "<?= htmlspecialchars($job['title']); ?>"</h2>
        <a href="jobs.php" class="btn btn-secondary btn-back">Voltar para Vagas</a>
    </div>

    <?php if (empty($applications)): ?>
        <p class="text-center text-muted fs-5 mt-5">Nenhum candidato inscrito nesta vaga ainda.</p>
    <?php else: ?>
        <div class="applicants-grid">
            <?php foreach ($applications as $app): ?>
                <div class="applicant-card">
                    <img
                        src="../<?= htmlspecialchars($app['photo'] ?: 'uploads/default_user.jpg'); ?>"
                        alt="Foto de <?= htmlspecialchars($app['name']); ?>"
                        class="applicant-photo"
                    />
                    <div class="applicant-info">
                        <div class="applicant-name"><?= htmlspecialchars($app['name']); ?></div>
                        <div class="applicant-email"><?= htmlspecialchars($app['email']); ?></div>
                        <div class="applicant-linkedin">
                            <?php if (!empty($app['linkedin'])): ?>
                                <a href="<?= htmlspecialchars($app['linkedin']); ?>" target="_blank" class="btn btn-sm btn-info">
                                    Ver Perfil LinkedIn
                                </a>
                            <?php else: ?>
                                <span class="text-secondary fst-italic">LinkedIn não informado</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
