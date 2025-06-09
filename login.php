<?php
session_start();
require_once 'includes/db.php';

// O bloco PHP de login permanece idêntico
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verificação de usuário e senha
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['username'] = $user['name']; // Adicionando o nome do usuário à sessão

        // Redireciona para o painel de admin ou para a página inicial
        header("Location: " . ($user['is_admin'] ? "admin/jobs.php" : "index.php"));
        exit;
    } else {
        $error = "E-mail ou senha inválidos. Tente novamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Empregos Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

    <div class="auth-panel-right">
        <div class="form-container">
            <h2>Acesse sua Conta</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger mb-3">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Entrar</button>
            </form>
            
            <div class="form-footer-link">
                <p>Ainda não é usuário? <a href="register.php">Criar</a></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>