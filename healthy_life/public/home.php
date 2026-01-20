<div class="welcome-section">
    <h2>Добро пожаловать в "Здоровый образ жизни!"</h2>
    <p>Наш сервис позволяет записаться к врачу онлайн быстро и удобно.</p>
    
    <div class="actions">
        <?php if (!$isLoggedIn): ?>
            <a href="index.php?page=login" class="btn btn-primary">Войти в аккаунт</a>
            <a href="index.php?page=register" class="btn btn-secondary">Зарегистрироваться</a>
        <?php endif; ?>
    </div>
    
    <h3>Специальности врачей:</h3>
    <?php if (!empty($specialties)): ?>
        <ul class="specialties-list">
            <?php foreach ($specialties as $specialty): ?>
                <li><?= htmlspecialchars($specialty) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>В настоящее время нет доступных специальностей врачей.</p>
    <?php endif; ?>
</div>