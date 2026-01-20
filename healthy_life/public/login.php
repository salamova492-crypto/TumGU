<div class="form-container">
    <h2>Вход в аккаунт</h2>
    
    <?php if (isset($login_error)): ?>
        <div class="alert alert-error"><?= $login_error ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="login_email">Email *</label>
            <input type="email" id="login_email" name="login_email" required>
        </div>
        
        <div class="form-group">
            <label for="login_password">Пароль *</label>
            <input type="password" id="login_password" name="login_password" required>
        </div>
        
        <button type="submit" name="login" class="btn btn-primary">Войти</button>
    </form>
    
    <p><a href="index.php">Вернуться на главную</a> | <a href="index.php?page=register">Регистрация</a></p>
</div>