<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';

// Проверяем, авторизован ли пользователь
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();

// Получаем записи пользователя
$stmt = $pdo->prepare("
    SELECT a.*, d.full_name as doctor_name, d.specialty 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.user_id = ?
    ORDER BY a.appointment_date ASC
");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои записи - Здоровый образ жизни!</title>
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
        <section class="my-appointments">
            <h2>Мои записи к врачам</h2>
            
            <?php if (count($appointments) > 0): ?>
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Врач</th>
                            <th>Специальность</th>
                            <th>Дата</th>
                            <th>Время</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <?php 
                            $appointment_date = strtotime($appointment['appointment_date']);
                            $today = strtotime(date('Y-m-d'));
                            $can_cancel = $appointment_date > $today; // Можно отменить до начала дня записи
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['specialty']); ?></td>
                                <td><?php echo date('d.m.Y', $appointment_date); ?></td>
                                <td><?php echo date('H:i', strtotime($appointment['appointment_time'])); ?></td>
                                <td>
                                    <?php 
                                    if ($appointment_date < $today) {
                                        echo '<span class="status past">Прошло</span>';
                                    } elseif ($appointment_date == $today) {
                                        echo '<span class="status today">Сегодня</span>';
                                    } else {
                                        echo '<span class="status future">Предстоящее</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($can_cancel): ?>
                                        <a href="cancel_appointment.php?id=<?php echo $appointment['id']; ?>" 
                                           class="btn danger" 
                                           onclick="return confirm('Вы уверены, что хотите отменить запись?')">Отменить</a>
                                    <?php else: ?>
                                        <span class="disabled">Отмена невозможна</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>У вас пока нет записей к врачам.</p>
            <?php endif; ?>
            
            <p><a href="dashboard.php">Вернуться в личный кабинет</a></p>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2023 Здоровый образ жизни! Все права защищены.</p>
    </footer>
</body>
</html>