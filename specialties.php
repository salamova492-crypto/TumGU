<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';

// Получаем список всех специальностей
$stmt = $pdo->query("SELECT DISTINCT specialty FROM doctors ORDER BY specialty");
$specialties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Специальности врачей - Здоровый образ жизни!</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Здоровый образ жизни!</h1>
        <nav>
            <ul>
                <li><a href="index.php">Главная</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="dashboard.php">Личный кабинет</a></li>
                    <li><a href="logout.php">Выход</a></li>
                <?php else: ?>
                    <li><a href="login.php">Вход</a></li>
                    <li><a href="register.php">Регистрация</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    
    <main>
        <section class="specialties">
            <h2>Специальности врачей</h2>
            
            <?php if (count($specialties) > 0): ?>
                <div class="specialties-grid">
                    <?php foreach ($specialties as $specialty): ?>
                        <div class="specialty-card">
                            <h3><?php echo htmlspecialchars($specialty['specialty']); ?></h3>
                            <p>Квалифицированные врачи в этой области</p>
                            
                            <?php if (isLoggedIn()): ?>
                                <a href="make_appointment.php?specialty=<?php echo urlencode($specialty['specialty']); ?>" 
                                   class="btn primary">Записаться</a>
                            <?php else: ?>
                                <p><a href="login.php">Войдите</a>, чтобы записаться к врачу</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>В настоящее время нет доступных специальностей врачей.</p>
            <?php endif; ?>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2023 Здоровый образ жизни! Все права защищены.</p>
    </footer>
</body>
</html>