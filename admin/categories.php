<?php
session_start();
require_once '../includes/db.php';

// TODO: Verificação de login/admin aqui...

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $name = trim($_POST['name']);
        if ($name !== '') {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
            $stmt->execute(['name' => $name]);
        }
    }

    if (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        if ($id > 0 && $name !== '') {
            $stmt = $pdo->prepare("UPDATE categories SET name = :name WHERE id = :id");
            $stmt->execute(['name' => $name, 'id' => $id]);
        }
    }

    if (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        if ($id > 0) {
            // Verifica se existem vagas vinculadas à categoria
            $check = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE category_id = :id");
            $check->execute(['id' => $id]);
            $count = $check->fetchColumn();

            if ($count == 0) {
                // Sem dependências, pode deletar
                $del = $pdo->prepare("DELETE FROM categories WHERE id = :id");
                $del->execute(['id' => $id]);
            } else {
                // Tem dependências, não pode deletar
                $_SESSION['error_message'] = "Não é possível excluir a categoria pois existem vagas vinculadas a ela.";
            }
        }
    }

    header("Location: categories.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin - Categorias</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/admin.css" />
</head>
<body>

<nav class="navbar navbar-expand-lg admin-navbar">
  <div class="container">
    <a class="navbar-brand fw-bold" href="../index.php">Empregos Online</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="jobs.php">Ver vagas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="categories.php">Categorias</a>
        </li>
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

<main class="main-content container mt-4">
  <h2>Gerenciar Categorias</h2>

  <?php if (!empty($_SESSION['error_message'])): ?>
    <div class="alert alert-danger" role="alert">
      <?php 
        echo $_SESSION['error_message']; 
        unset($_SESSION['error_message']); 
      ?>
    </div>
  <?php endif; ?>

  <div class="admin-panel mb-4">
    <h4>Criar nova categoria</h4>
    <form method="POST" class="row g-3 align-items-end">
      <div class="col">
        <label for="name" class="form-label">Nome da categoria</label>
        <input type="text" class="form-control" id="name" name="name" required />
      </div>
      <div class="col-auto">
        <button type="submit" name="add" class="btn btn-primary">Criar</button>
      </div>
    </form>
  </div>

  <div class="admin-panel">
    <h4>Categorias cadastradas</h4>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Nome</th>
            <th class="text-end">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $category): ?>
            <tr>
              <td><?php echo htmlspecialchars($category['name']); ?></td>
              <td class="text-end">
                <form method="POST" class="d-inline-block me-2" id="edit-form-<?php echo $category['id']; ?>">
                  <input type="hidden" name="id" value="<?php echo $category['id']; ?>" />
                  <div class="input-group input-group-sm">
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required />
                    <button type="submit" name="edit" class="btn btn-warning">Editar</button>
                  </div>
                </form>
                <form method="POST" class="d-inline-block" onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?');">
                  <input type="hidden" name="id" value="<?php echo $category['id']; ?>" />
                  <button type="submit" name="delete" class="btn btn-danger btn-sm">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
