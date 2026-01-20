<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        // Хешируем введенный пароль для сравнения
        $hashed_password = md5($password);
        
        // Проверяем данные пользователя
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
        $stmt->execute([$email, $hashed_password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Успешная аутентификация
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            // Перенаправляем в зависимости от роли
            if ($user['role'] === 'admin') {
                header('Location: admin_panel.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $error_message = 'Неверные данные для входа';
        }
    } else {
        $error_message = 'Пожалуйста, заполните все поля';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Здоровый образ жизни!</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Здоровый образ жизни!</h1>
        <nav>
            <ul>
                <li><a href="index.php">Главная</a></li>
                <li><a href="login.php">Вход</a></li>
                <li><a href="register.php">Регистрация</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <section class="form-section">
            <h2>Вход в аккаунт</h2>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="email">Email (Логин):</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn primary">Войти</button>
            </form>
            
            <p><a href="register.php">Нет аккаунта? Зарегистрироваться</a></p>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2023 Здоровый образ жизни! Все права защищены.</p>
    </footer>
</body>
</html>