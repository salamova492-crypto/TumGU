<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';

// Проверяем, авторизован ли пользователь
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$appointment_id = $_GET['id'] ?? 0;
$error_message = '';
$success_message = '';

if ($appointment_id > 0) {
    // Получаем информацию о записи
    $stmt = $pdo->prepare("
        SELECT a.*, d.full_name as doctor_name 
        FROM appointments a 
        JOIN doctors d ON a.doctor_id = d.id 
        WHERE a.id = ? AND a.user_id = ?
    ");
    $stmt->execute([$appointment_id, $_SESSION['user_id']]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        $error_message = 'Запись не найдена или недоступна для отмены';
    } else {
        // Проверяем, можно ли отменить запись (не сегодня и не в прошлом)
        $appointment_date = strtotime($appointment['appointment_date']);
        $today = strtotime(date('Y-m-d'));
        
        if ($appointment_date <= $today) {
            $error_message = 'Нельзя отменить запись на сегодня или прошедшую дату';
        } else {
            // Отменяем запись
            try {
                $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ? AND user_id = ?");
                $result = $stmt->execute([$appointment_id, $_SESSION['user_id']]);
                
                if ($result) {
                    $success_message = 'Запись успешно отменена';
                } else {
                    $error_message = 'Не удалось отменить запись';
                }
            } catch (PDOException $e) {
                $error_message = 'Ошибка при отмене записи: ' . $e->getMessage();
            }
        }
    }
} else {
    $error_message = 'Неверный идентификатор записи';
}

// Перенаправляем обратно через несколько секунд
if (!empty($success_message)) {
    header('refresh:3;url=my_appointments.php');
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отмена записи - Здоровый образ жизни!</title>
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
        <section class="cancel-appointment">
            <h2>Отмена записи</h2>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <p><a href="my_appointments.php" class="btn secondary">Вернуться к записям</a></p>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
                <p>Через 3 секунды вы будете перенаправлены на страницу записей...</p>
                <p><a href="my_appointments.php" class="btn primary">Перейти сейчас</a></p>
            <?php endif; ?>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2023 Здоровый образ жизни! Все права защищены.</p>
    </footer>
</body>
</html>