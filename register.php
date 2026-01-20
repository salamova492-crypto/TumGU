<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Проверки на стороне сервера
    $valid = true;
    
    if (!isCyrillic($full_name)) {
        $error_message = 'ФИО должно содержать только кириллические буквы и пробелы';
        $valid = false;
    }
    
    if (!isValidEmail($email)) {
        $error_message = 'Неверный формат email адреса';
        $valid = false;
    } else {
        // Проверка уникальности email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error_message = 'Пользователь с таким email уже существует';
            $valid = false;
        }
    }
    
    if (!isValidPassword($password)) {
        $error_message = 'Пароль должен содержать не менее 6 символов и состоять из латинских букв';
        $valid = false;
    }
    
    if (!passwordsMatch($password, $confirm_password)) {
        $error_message = 'Пароли не совпадают';
        $valid = false;
    }
    
    if ($valid) {
        try {
            // Хешируем пароль
            $hashed_password = md5($password);
            
            // Сохраняем пользователя в базе данных
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$full_name, $email, $hashed_password]);
            
            $success_message = 'Регистрация прошла успешно! Теперь вы можете войти в систему.';
        } catch (PDOException $e) {
            $error_message = 'Ошибка при регистрации: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Здоровый образ жизни!</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/validation.js"></script>
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
            <h2>Регистрация</h2>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <form id="registration-form" method="post" action="">
                <div class="form-group">
                    <label for="full_name">ФИО:</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                    <span class="error" id="full_name_error"></span>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    <span class="error" id="email_error"></span>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль:</label>
                    <input type="password" id="password" name="password" required>
                    <span class="error" id="password_error"></span>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Подтверждение пароля:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <span class="error" id="confirm_password_error"></span>
                </div>
                
                <button type="submit" class="btn primary">Зарегистрироваться</button>
            </form>
            
            <p><a href="login.php">Уже есть аккаунт? Войти</a></p>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2023 Здоровый образ жизни! Все права защищены.</p>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registration-form');
            const fullNameInput = document.getElementById('full_name');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            
            const fullNameError = document.getElementById('full_name_error');
            const emailError = document.getElementById('email_error');
            const passwordError = document.getElementById('password_error');
            const confirmPasswordError = document.getElementById('confirm_password_error');
            
            // Функция проверки ФИО (только кириллица и пробелы)
            function validateFullName() {
                const fullName = fullNameInput.value.trim();
                if (fullName === '') {
                    fullNameError.textContent = 'Поле обязательно для заполнения';
                    fullNameInput.style.borderColor = 'red';
                    return false;
                }
                
                // Проверка на кириллицу и пробелы
                const cyrillicRegex = /^[\u0400-\u04FF\s]+$/;
                if (!cyrillicRegex.test(fullName)) {
                    fullNameError.textContent = 'ФИО должно содержать только кириллические буквы и пробелы';
                    fullNameInput.style.borderColor = 'red';
                    return false;
                }
                
                fullNameError.textContent = '';
                fullNameInput.style.borderColor = '';
                return true;
            }
            
            // Функция проверки email
            function validateEmail() {
                const email = emailInput.value.trim();
                if (email === '') {
                    emailError.textContent = 'Поле обязательно для заполнения';
                    emailInput.style.borderColor = 'red';
                    return false;
                }
                
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    emailError.textContent = 'Неверный формат email адреса';
                    emailInput.style.borderColor = 'red';
                    return false;
                }
                
                emailError.textContent = '';
                emailInput.style.borderColor = '';
                return true;
            }
            
            // Функция проверки пароля
            function validatePassword() {
                const password = passwordInput.value;
                if (password === '') {
                    passwordError.textContent = 'Поле обязательно для заполнения';
                    passwordInput.style.borderColor = 'red';
                    return false;
                }
                
                if (password.length < 6) {
                    passwordError.textContent = 'Пароль должен содержать не менее 6 символов';
                    passwordInput.style.borderColor = 'red';
                    return false;
                }
                
                // Проверка на латинские символы
                const latinRegex = /^[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]*$/;
                if (!latinRegex.test(password)) {
                    passwordError.textContent = 'Пароль может содержать только латинские символы';
                    passwordInput.style.borderColor = 'red';
                    return false;
                }
                
                passwordError.textContent = '';
                passwordInput.style.borderColor = '';
                return true;
            }
            
            // Функция проверки подтверждения пароля
            function validateConfirmPassword() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (confirmPassword === '') {
                    confirmPasswordError.textContent = 'Поле обязательно для заполнения';
                    confirmPasswordInput.style.borderColor = 'red';
                    return false;
                }
                
                if (password !== confirmPassword) {
                    confirmPasswordError.textContent = 'Пароли не совпадают';
                    confirmPasswordInput.style.borderColor = 'red';
                    return false;
                }
                
                confirmPasswordError.textContent = '';
                confirmPasswordInput.style.borderColor = '';
                return true;
            }
            
            // Валидация при потере фокуса
            fullNameInput.addEventListener('blur', validateFullName);
            emailInput.addEventListener('blur', validateEmail);
            passwordInput.addEventListener('blur', validatePassword);
            confirmPasswordInput.addEventListener('blur', validateConfirmPassword);
            
            // Валидация при вводе
            fullNameInput.addEventListener('input', function() {
                if (fullNameInput.value.trim() !== '') {
                    validateFullName();
                } else {
                    fullNameError.textContent = '';
                    fullNameInput.style.borderColor = '';
                }
            });
            
            emailInput.addEventListener('input', function() {
                if (emailInput.value.trim() !== '') {
                    validateEmail();
                } else {
                    emailError.textContent = '';
                    emailInput.style.borderColor = '';
                }
            });
            
            passwordInput.addEventListener('input', function() {
                if (passwordInput.value !== '') {
                    validatePassword();
                } else {
                    passwordError.textContent = '';
                    passwordInput.style.borderColor = '';
                }
            });
            
            confirmPasswordInput.addEventListener('input', function() {
                if (confirmPasswordInput.value !== '') {
                    validateConfirmPassword();
                } else {
                    confirmPasswordError.textContent = '';
                    confirmPasswordInput.style.borderColor = '';
                }
            });
            
            // Валидация формы перед отправкой
            form.addEventListener('submit', function(e) {
                const isFullNameValid = validateFullName();
                const isEmailValid = validateEmail();
                const isPasswordValid = validatePassword();
                const isConfirmPasswordValid = validateConfirmPassword();
                
                if (!isFullNameValid || !isEmailValid || !isPasswordValid || !isConfirmPasswordValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>