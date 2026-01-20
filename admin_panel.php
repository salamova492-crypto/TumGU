<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';

// Проверяем, авторизован ли пользователь и является ли он администратором
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

$error_message = '';
$success_message = '';

// Обработка формы добавления врача
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_doctor'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $specialty = trim($_POST['specialty'] ?? '');
    
    if (!empty($full_name) && !empty($specialty)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO doctors (full_name, specialty) VALUES (?, ?)");
            $stmt->execute([$full_name, $specialty]);
            
            $success_message = 'Врач успешно добавлен!';
        } catch (PDOException $e) {
            $error_message = 'Ошибка при добавлении врача: ' . $e->getMessage();
        }
    } else {
        $error_message = 'Пожалуйста, заполните все поля';
    }
}

// Получаем список всех врачей
$stmt = $pdo->query("SELECT * FROM doctors ORDER BY specialty, full_name");
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - Здоровый образ жизни!</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Здоровый образ жизни!</h1>
        <nav>
            <ul>
                <li><a href="index.php">Главная</a></li>
                <li><a href="dashboard.php">Личный кабинет</a></li>
                <li><a href="logout.php">Выход</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <section class="admin-panel">
            <h2>Панель администратора</h2>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <div class="admin-actions">
                <h3>Добавить нового врача</h3>
                <form method="post" action="" class="form-inline">
                    <div class="form-group">
                        <label for="full_name">ФИО врача:</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="specialty">Специальность:</label>
                        <input type="text" id="specialty" name="specialty" required>
                    </div>
                    
                    <button type="submit" name="add_doctor" class="btn primary">Добавить врача</button>
                </form>
            </div>
            
            <div class="doctors-list">
                <h3>Все врачи</h3>
                
                <?php if (count($doctors) > 0): ?>
                    <table class="doctors-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ФИО</th>
                                <th>Специальность</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctors as $doctor): ?>
                                <tr>
                                    <td><?php echo $doctor['id']; ?></td>
                                    <td><?php echo htmlspecialchars($doctor['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($doctor['specialty']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Врачи еще не добавлены.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2023 Здоровый образ жизни! Все права защищены.</p>
    </footer>
</body>
</html>