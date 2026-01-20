<?php
require_once 'includes/session.php';

// Если пользователь уже авторизован, перенаправляем его
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Здоровый образ жизни!</title>
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
        <section class="welcome">
            <h2>Добро пожаловать в систему записи к врачам</h2>
            <p>Здесь вы можете записаться на прием к врачу, просмотреть список специальностей и управлять своими записями.</p>
            
            <div class="actions">
                <a href="specialties.php" class="btn">Просмотреть специальности врачей</a>
                <?php if (!isLoggedIn()): ?>
                    <a href="login.php" class="btn primary">Войти в аккаунт</a>
                    <a href="register.php" class="btn secondary">Зарегистрироваться</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn primary">Личный кабинет</a>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2023 Здоровый образ жизни! Все права защищены.</p>
    </footer>
</body>
</html>