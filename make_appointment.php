<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';

// Проверяем, авторизован ли пользователь
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$error_message = '';
$success_message = '';

// Получаем специальность из параметров
$selected_specialty = $_GET['specialty'] ?? '';

// Получаем список врачей по специальности или все врачи
if (!empty($selected_specialty)) {
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE specialty = ? ORDER BY full_name");
    $stmt->execute([$selected_specialty]);
} else {
    $stmt = $pdo->query("SELECT * FROM doctors ORDER BY specialty, full_name");
}
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка формы записи
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doctor_id = $_POST['doctor_id'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    
    // Проверяем, что дата не раньше сегодняшней
    $current_date = date('Y-m-d');
    if ($appointment_date < $current_date) {
        $error_message = 'Нельзя записаться на прошедшую дату';
    } else {
        // Проверяем, не превышено ли количество записей на этот день для данного врача
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM appointments 
            WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ?
        ");
        $stmt->execute([$doctor_id, $appointment_date, $appointment_time]);
        $count = $stmt->fetchColumn();
        
        if ($count >= 1) { // Предполагаем, что на одно время может быть только одна запись
            $error_message = 'На выбранное время уже есть запись';
        } else {
            try {
                // Создаем запись
                $stmt = $pdo->prepare("
                    INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$_SESSION['user_id'], $doctor_id, $appointment_date, $appointment_time]);
                
                $success_message = 'Запись успешно создана!';
            } catch (PDOException $e) {
                $error_message = 'Ошибка при создании записи: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запись к врачу - Здоровый образ жизни!</title>
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
        <section class="appointment-form">
            <h2>Запись к врачу</h2>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="doctor_id">Выберите врача:</label>
                    <select id="doctor_id" name="doctor_id" required>
                        <option value="">-- Выберите врача --</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo $doctor['id']; ?>" 
                                <?php echo isset($_POST['doctor_id']) && $_POST['doctor_id'] == $doctor['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($doctor['full_name']); ?> (<?php echo htmlspecialchars($doctor['specialty']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="appointment_date">Дата приема:</label>
                    <input type="date" id="appointment_date" name="appointment_date" 
                           min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="appointment_time">Время приема:</label>
                    <input type="time" id="appointment_time" name="appointment_time" 
                           min="08:00" max="18:00" required>
                </div>
                
                <button type="submit" class="btn primary">Записаться</button>
            </form>
            
            <p><a href="dashboard.php">Вернуться в личный кабинет</a></p>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2023 Здоровый образ жизни! Все права защищены.</p>
    </footer>
</body>
</html>