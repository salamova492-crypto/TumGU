<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';

// Проверяем, авторизован ли пользователь
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();

// Получаем список специальностей
$stmt = $pdo->query("SELECT DISTINCT specialty FROM doctors ORDER BY specialty");
$specialties = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Получаем записи пользователя
$stmt = $pdo->prepare("
    SELECT a.*, d.full_name as doctor_name, d.specialty 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.user_id = ?
    ORDER BY a.appointment_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - Здоровый образ жизни!</title>
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
        <section class="dashboard">
            <h2>Добро пожаловать, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
            
            <div class="user-info">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Роль:</strong> <?php echo $user['role'] === 'admin' ? 'Администратор' : 'Пользователь'; ?></p>
            </div>
            
            <div class="dashboard-actions">
                <h3>Доступные действия</h3>
                
                <div class="action-buttons">
                    <a href="make_appointment.php" class="btn primary">Записаться к врачу</a>
                    <a href="my_appointments.php" class="btn secondary">Мои записи</a>
                    
                    <?php if ($user['role'] === 'admin'): ?>
                        <a href="admin_panel.php" class="btn admin">Панель администратора</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="appointment-list">
                <h3>Последние записи</h3>
                
                <?php if (count($appointments) > 0): ?>
                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th>Врач</th>
                                <th>Специальность</th>
                                <th>Дата</th>
                                <th>Время</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['specialty']); ?></td>
                                    <td><?php echo date('d.m.Y', strtotime($appointment['appointment_date'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($appointment['appointment_time'])); ?></td>
                                    <td>
                                        <a href="cancel_appointment.php?id=<?php echo $appointment['id']; ?>" 
                                           class="btn danger" 
                                           onclick="return confirm('Вы уверены, что хотите отменить запись?')">Отменить</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>У вас пока нет записей к врачам.</p>
                <?php endif; ?>
            </div>
            
            <div class="specialties-list">
                <h3>Специальности врачей</h3>
                
                <?php if (count($specialties) > 0): ?>
                    <ul>
                        <?php foreach ($specialties as $specialty): ?>
                            <li><?php echo htmlspecialchars($specialty); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Специальности врачей временно недоступны.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2023 Здоровый образ жизни! Все права защищены.</p>
    </footer>
</body>
</html>