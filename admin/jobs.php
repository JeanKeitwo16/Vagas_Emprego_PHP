<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $contact = $_POST['contact'];
        $category_id = $_POST['category_id'];
        $image = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../uploads/";
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = 'uploads/' . uniqid('job_', true) . '.' . $file_extension;
            move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image);
        }

        $stmt = $pdo->prepare("INSERT INTO jobs (title, description, contact, image, category_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $contact, $image, $category_id]);

    } elseif (isset($_POST['toggle'])) {
        $id = $_POST['id'];
        $is_active = $_POST['is_active'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE jobs SET is_active = ? WHERE id = ?");
        $stmt->execute([$is_active, $id]);
    }
    header("Location: jobs.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$jobs = $pdo->query("SELECT j.*, c.name as category_name FROM jobs j JOIN categories c ON j.category_id = c.id ORDER BY j.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin - Gerenciar Vagas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/admin.css" />
    <style>
        /* Layout flex para main content */
        main.main-content {
            display: flex;
            gap: 2rem;
            padding-top: 2rem;
            flex-wrap: wrap;
        }
        .admin-panel.form-panel {
            flex: 1 1 40%;
            background: var(--bs-body-bg);
            padding: 1.5rem 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgb(0 0 0 / 0.2);
            min-width: 320px;
        }
        .admin-panel.table-panel {
            flex: 1 1 55%;
            min-width: 350px;
        }
        /* Form headers */
        .form-panel h4 {
            font-weight: 700;
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--bs-primary);
            padding-bottom: 0.3rem;
        }
        /* Botões maiores e com espaçamento */
        button.btn-primary {
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
        }
        /* Tabela com linhas alternadas e cursor pointer nos botões */
        table.table-hover tbody tr:hover {
            background-color: var(--bs-primary-bg-subtle);
        }
        /* Ajuste botões de ação */
        .table-panel .btn-sm {
            min-width: 100px;
            margin-right: 0.5rem;
        }
        /* Responsivo */
        @media (max-width: 900px) {
            main.main-content {
                flex-direction: column;
            }
            .admin-panel.form-panel, .admin-panel.table-panel {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg admin-navbar shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">Empregos Online</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link active" href="jobs.php">Ver vagas</a></li>
                <li class="nav-item"><a class="nav-link" href="categories.php">Categorias</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Admin</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../logout.php">Sair</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="main-content container">
    <section class="admin-panel form-panel">
        <h4>Adicionar nova vaga</h4>
        <form method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-12">
                <label for="title" class="form-label">Título da Vaga</label>
                <input type="text" id="title" name="title" class="form-control" required />
            </div>
            <div class="col-md-12">
                <label for="category_id" class="form-label">Categoria</label>
                <select id="category_id" name="category_id" class="form-select" required>
                    <option value="" disabled selected>Selecione uma categoria</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-12">
                <label for="description" class="form-label">Descrição Completa</label>
                <textarea id="description" name="description" class="form-control" rows="5" required></textarea>
            </div>
            <div class="col-md-12">
                <label for="contact" class="form-label">Contato</label>
                <input type="text" id="contact" name="contact" class="form-control" required />
            </div>
            <div class="col-md-12">
                <label for="image" class="form-label">Imagem(Opcional)</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*" />
            </div>
            <div class="col-md-12 d-flex justify-content-end mt-3">
                <button type="submit" name="add" class="btn btn-primary">Criar Vaga</button>
            </div>
        </form>
    </section>

    <section class="admin-panel table-panel">
        <h4>Vagas cadastradas</h4>
        <div class="table-responsive mt-3">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Categoria</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jobs)): ?>
                        <tr><td colspan="4" class="text-center text-muted">Nenhuma vaga cadastrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td><?= htmlspecialchars($job['title']); ?></td>
                                <td><?= htmlspecialchars($job['category_name']); ?></td>
                                <td>
                                    <span class="badge <?= $job['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?= $job['is_active'] ? 'Ativa' : 'Inativa'; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form method="POST" class="d-inline me-2">
                                        <input type="hidden" name="id" value="<?= $job['id']; ?>" />
                                        <input type="hidden" name="is_active" value="<?= $job['is_active']; ?>" />
                                        <button type="submit" name="toggle" class="btn btn-sm <?= $job['is_active'] ? 'btn-danger' : 'btn-success'; ?>">
                                            <?= $job['is_active'] ? 'Desativar' : 'Ativar'; ?>
                                        </button>
                                    </form>
                                    <a href="view_applications.php?job_id=<?= $job['id']; ?>" class="btn btn-sm btn-info">Ver Inscritos</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
