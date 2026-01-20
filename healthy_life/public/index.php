<?php
session_start();

require_once '../includes/config.php';

// Проверка авторизации
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

// Обработка выхода
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Обработка регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Валидация на стороне сервера
    $errors = [];
    
    // Проверка ФИО (только кириллические буквы и пробелы)
    if (!preg_match("/^[А-ЯЁа-яё\s]+$/u", $fullname)) {
        $errors[] = "ФИО должно содержать только кириллические буквы и пробелы";
    }
    
    // Проверка email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Неверный формат email";
    }
    
    // Проверка уникальности email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Пользователь с таким email уже существует";
    }
    
    // Проверка пароля
    if (strlen($password) < 6) {
        $errors[] = "Пароль должен быть не менее 6 символов";
    }
    
    // Проверка английской раскладки
    if (!preg_match("/^[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]+$/", $password)) {
        $errors[] = "Пароль может содержать только символы английской клавиатуры";
    }
    
    // Проверка совпадения паролей
    if ($password !== $confirmPassword) {
        $errors[] = "Пароли не совпадают";
    }
    
    if (empty($errors)) {
        // Хеширование пароля
        $hashedPassword = md5($password);
        
        // Сохранение в базу данных
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$fullname, $email, $hashedPassword])) {
            $success_message = "Регистрация прошла успешно! Теперь вы можете войти.";
        } else {
            $errors[] = "Ошибка при регистрации";
        }
    }
}

// Обработка входа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['login_email']);
    $password = $_POST['login_password'];
    
    // Хеширование введенного пароля
    $hashedPassword = md5($password);
    
    // Проверка учетных данных
    $stmt = $pdo->prepare("SELECT id, fullname, email, password, role FROM users WHERE email = ? AND password = ?");
    $stmt->execute([$email, $hashedPassword]);
    
    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        
        // Перенаправление в личный кабинет
        header('Location: index.php?page=dashboard');
        exit;
    } else {
        $login_error = "Неверный логин или пароль";
    }
}

// Обработка добавления врача (только для администратора)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_doctor']) && $userRole === 'admin') {
    $doctor_fullname = trim($_POST['doctor_fullname']);
    $specialty = trim($_POST['specialty']);
    
    if (!empty($doctor_fullname) && !empty($specialty)) {
        $stmt = $pdo->prepare("INSERT INTO doctors (fullname, specialty) VALUES (?, ?)");
        if ($stmt->execute([$doctor_fullname, $specialty])) {
            $doctor_success = "Врач успешно добавлен";
        } else {
            $doctor_error = "Ошибка при добавлении врача";
        }
    } else {
        $doctor_error = "Заполните все поля";
    }
}

// Обработка записи к врачу
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment']) && $isLoggedIn) {
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    
    // Проверяем, что дата не в прошлом
    if (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
        $appointment_error = "Нельзя записаться на прошедшую дату";
    } else {
        // Проверяем, есть ли уже 5 записей на этот день у этого врача
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = ?");
        $stmt->execute([$doctor_id, $appointment_date]);
        $count = $stmt->fetchColumn();
        
        if ($count >= 5) {
            $appointment_error = "У этого врача нет свободных мест на выбранную дату";
        } else {
            // Создаем запись
            $stmt = $pdo->prepare("INSERT INTO appointments (user_id, doctor_id, appointment_date) VALUES (?, ?, ?)");
            if ($stmt->execute([$userId, $doctor_id, $appointment_date])) {
                $appointment_success = "Вы успешно записались к врачу";
            } else {
                $appointment_error = "Ошибка при записи к врачу";
            }
        }
    }
}

// Обработка отмены записи
if (isset($_GET['cancel_appointment']) && $isLoggedIn) {
    $appointment_id = $_GET['cancel_appointment'];
    
    // Проверяем, принадлежит ли запись пользователю и можно ли её отменить (не раньше, чем за день до визита)
    $stmt = $pdo->prepare("SELECT appointment_date FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->execute([$appointment_id, $userId]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($appointment) {
        $appointmentDate = new DateTime($appointment['appointment_date']);
        $today = new DateTime();
        $interval = $today->diff($appointmentDate);
        
        // Можно отменить только если до визита больше 1 дня
        if ($interval->days > 0 || $appointmentDate->format('Y-m-d') !== $today->format('Y-m-d')) {
            $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$appointment_id, $userId])) {
                $appointment_success = "Запись успешно отменена";
            } else {
                $appointment_error = "Ошибка при отмене записи";
            }
        } else {
            $appointment_error = "Нельзя отменить запись на сегодня или на прошедшую дату";
        }
    } else {
        $appointment_error = "Запись не найдена или недоступна для отмены";
    }
}

// Получение специальностей врачей
$stmt = $pdo->query("SELECT DISTINCT specialty FROM doctors ORDER BY specialty");
$specialties = $stmt->fetchAll(PDO::FETCH_COLUMN);

$page = $_GET['page'] ?? 'home';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Здоровый образ жизни!</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Здоровый образ жизни!</h1>
            <?php if ($isLoggedIn): ?>
                <nav>
                    <a href="index.php">Главная</a>
                    <a href="index.php?page=dashboard">Личный кабинет</a>
                    <?php if ($userRole === 'admin'): ?>
                        <a href="index.php?page=admin">Панель управления</a>
                    <?php endif; ?>
                    <a href="index.php?logout=true">Выйти</a>
                </nav>
            <?php else: ?>
                <nav>
                    <a href="index.php">Главная</a>
                    <a href="index.php?page=login">Вход</a>
                    <a href="index.php?page=register">Регистрация</a>
                </nav>
            <?php endif; ?>
        </div>
    </header>

    <main class="container">
        <?php
        switch ($page) {
            case 'register':
                include 'register.php';
                break;
            case 'login':
                include 'login.php';
                break;
            case 'dashboard':
                include 'dashboard.php';
                break;
            case 'admin':
                include 'admin.php';
                break;
            case 'doctors':
                include 'doctors.php';
                break;
            default:
                include 'home.php';
                break;
        }
        ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 Здоровый образ жизни! Все права защищены.</p>
        </div>
    </footer>
</body>
</html>