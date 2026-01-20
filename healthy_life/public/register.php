<div class="form-container">
    <h2>Регистрация</h2>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" id="registrationForm">
        <div class="form-group">
            <label for="fullname">ФИО *</label>
            <input type="text" id="fullname" name="fullname" value="<?= $_POST['fullname'] ?? '' ?>" required>
            <span class="error-message" id="fullnameError"></span>
        </div>
        
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" value="<?= $_POST['email'] ?? '' ?>" required>
            <span class="error-message" id="emailError"></span>
        </div>
        
        <div class="form-group">
            <label for="password">Пароль * (минимум 6 символов)</label>
            <input type="password" id="password" name="password" required>
            <span class="error-message" id="passwordError"></span>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Подтверждение пароля *</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <span class="error-message" id="confirmPasswordError"></span>
        </div>
        
        <button type="submit" name="register" class="btn btn-primary">Зарегистрироваться</button>
    </form>
    
    <p><a href="index.php">Вернуться на главную</a></p>
</div>

<script>
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    let isValid = true;
    
    // Получаем значения полей
    const fullname = document.getElementById('fullname').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    // Сброс ошибок
    document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
    document.querySelectorAll('.form-group input').forEach(el => el.classList.remove('error'));
    
    // Проверка ФИО (только кириллические буквы и пробелы)
    const cyrillicRegex = /^[А-ЯЁа-яё\s]+$/u;
    if (!cyrillicRegex.test(fullname)) {
        document.getElementById('fullnameError').textContent = 'ФИО должно содержать только кириллические буквы и пробелы';
        document.getElementById('fullname').classList.add('error');
        isValid = false;
    }
    
    // Проверка email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        document.getElementById('emailError').textContent = 'Неверный формат email';
        document.getElementById('email').classList.add('error');
        isValid = false;
    }
    
    // Проверка пароля
    if (password.length < 6) {
        document.getElementById('passwordError').textContent = 'Пароль должен быть не менее 6 символов';
        document.getElementById('password').classList.add('error');
        isValid = false;
    }
    
    // Проверка английской раскладки
    const englishRegex = /^[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+$/;
    if (!englishRegex.test(password)) {
        document.getElementById('passwordError').textContent = 'Пароль может содержать только символы английской клавиатуры';
        document.getElementById('password').classList.add('error');
        isValid = false;
    }
    
    // Проверка совпадения паролей
    if (password !== confirmPassword) {
        document.getElementById('confirmPasswordError').textContent = 'Пароли не совпадают';
        document.getElementById('confirm_password').classList.add('error');
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
    }
});
</script>