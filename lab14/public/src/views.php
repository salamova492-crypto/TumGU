<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Генерация HTML для главной страницы
 */
function renderIndexView(): string
{
    ob_start();
    ?>
    <h2>Здоровый образ жизни!</h2>
    <div class="welcome-container">
        <p>Добро пожаловать в клинику "Здоровый образ жизни!" Здесь вы можете:</p>
        <ul>
            <li>Зарегистрироваться в системе</li>
            <li>Войти в свой аккаунт</li>
            <li>Просмотреть список врачей по специальностям</li>
            <li>Записаться на прием к врачу</li>
            <li>Просмотреть свои записи на прием</li>
        </ul>
        <div class="form-actions">
            <a href="/login" class="btn-primary">Войти</a>
            <a href="/register" class="btn-primary">Регистрация</a>
            <a href="/specialties" class="btn-primary">Специальности врачей</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для формы регистрации
 */
function renderRegisterView(array $errors = [], array $formData = []): string
{
    ob_start();
    ?>
    <h2>Регистрация</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/register">
        <div class="form-group">
            <label for="full_name">Полное имя:</label>
            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($formData['full_name'] ?? '') ?>" required>
            <small>Имя должно содержать только кириллические буквы и пробелы</small>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
            <small>Пароль должен содержать не менее 6 символов и использовать английскую клавиатурную раскладку</small>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Подтверждение пароля:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">Зарегистрироваться</button>
            <a href="/" class="btn-secondary-gray">Назад</a>
        </div>
    </form>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для формы входа
 */
function renderLoginView(array $errors = []): string
{
    ob_start();
    ?>
    <h2>Вход в систему</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/login">
        <div class="form-group">
            <label for="login">Email или Логин:</label>
            <input type="text" id="login" name="login" required>
        </div>
        
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">Войти</button>
            <a href="/" class="btn-secondary-gray">Назад</a>
        </div>
    </form>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для профиля пользователя
 */
function renderProfileView(array $appointments): string
{
    ob_start();
    session_start();
    ?>
    <h2>Ваш профиль</h2>
    <div class="user-info">
        <p><strong>Имя:</strong> <?= htmlspecialchars($_SESSION['user_full_name'] ?? '') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
    </div>
    
    <div class="form-actions">
        <a href="/booking" class="btn-primary">Записаться на прием</a>
        <a href="/logout" class="btn-secondary">Выйти</a>
    </div>
    
    <h3>Ваши записи на прием</h3>
    <?php if (!empty($appointments)): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Врач</th>
                        <th>Специальность</th>
                        <th>Дата приема</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['specialty']) ?></td>
                            <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                            <td>
                                <a href="/cancel-appointment?id=<?= $appointment['id'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Вы уверены, что хотите отменить запись?')">Отменить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>У вас пока нет записей на прием.</p>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для списка специальностей
 */
function renderSpecialtiesView(array $specialties): string
{
    ob_start();
    ?>
    <h2>Специальности врачей</h2>
    <div class="specialties-list">
        <?php if (!empty($specialties)): ?>
            <ul>
                <?php foreach ($specialties as $specialty): ?>
                    <li><?= htmlspecialchars($specialty) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>В настоящее время нет доступных специальностей.</p>
        <?php endif; ?>
    </div>
    <div class="form-actions">
        <a href="/" class="btn-secondary-gray">Назад</a>
        <a href="/login" class="btn-primary">Войти</a>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для формы бронирования
 */
function renderBookingView(array $specialties, string $selectedSpecialty = '', string $selectedDate = '', array $availableDoctors = [], array $errors = []): string
{
    ob_start();
    ?>
    <h2>Записаться на прием</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="GET" action="/booking" class="filter-form">
        <div class="form-group">
            <label for="specialty">Выберите специальность врача:</label>
            <select id="specialty" name="specialty" onchange="this.form.submit()">
                <option value="">Все специальности</option>
                <?php foreach ($specialties as $specialty): ?>
                    <option value="<?= htmlspecialchars($specialty) ?>" <?= $specialty === $selectedSpecialty ? 'selected' : '' ?>>
                        <?= htmlspecialchars($specialty) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="date">Выберите дату:</label>
            <input type="date" id="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>" onchange="this.form.submit()">
        </div>
    </form>
    
    <?php if ($selectedSpecialty && $selectedDate): ?>
        <h3>Доступные врачи на <?= htmlspecialchars($selectedDate) ?> (<?= htmlspecialchars($selectedSpecialty) ?>)</h3>
        <?php if (!empty($availableDoctors)): ?>
            <form method="POST" action="/book">
                <input type="hidden" name="date" value="<?= htmlspecialchars($selectedDate) ?>">
                <input type="hidden" name="specialty" value="<?= htmlspecialchars($selectedSpecialty) ?>">
                
                <div class="doctors-list">
                    <?php foreach ($availableDoctors as $doctor): ?>
                        <div class="doctor-item">
                            <label>
                                <input type="radio" name="doctor_id" value="<?= $doctor['id'] ?>" required>
                                <span class="doctor-info">
                                    <strong><?= htmlspecialchars($doctor['full_name']) ?></strong> 
                                    - <?= $doctor['available_slots'] ?> свободных мест
                                </span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Записаться</button>
                    <a href="/profile" class="btn-secondary-gray">Назад</a>
                </div>
            </form>
        <?php else: ?>
            <p>На выбранную дату нет доступных врачей по данной специальности.</p>
            <div class="form-actions">
                <a href="/profile" class="btn-secondary-gray">Назад</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для формы добавления врача (админ панель)
 */
function renderAddDoctorView(array $errors = [], array $formData = []): string
{
    ob_start();
    ?>
    <h2>Добавить врача</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/admin/add-doctor">
        <div class="form-group">
            <label for="full_name">Полное имя врача:</label>
            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($formData['full_name'] ?? '') ?>" required>
            <small>Имя должно содержать только кириллические буквы и пробелы</small>
        </div>
        
        <div class="form-group">
            <label for="specialty">Специальность:</label>
            <input type="text" id="specialty" name="specialty" value="<?= htmlspecialchars($formData['specialty'] ?? '') ?>" required>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">Добавить врача</button>
            <a href="/admin" class="btn-secondary-gray">Назад</a>
        </div>
    </form>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для админ панели
 */
function renderAdminView(array $doctors, array $appointments): string
{
    ob_start();
    ?>
    <h2>Панель администратора</h2>
    <div class="form-actions">
        <a href="/admin/add-doctor" class="btn-primary">Добавить врача</a>
        <a href="/logout" class="btn-secondary">Выйти</a>
    </div>
    
    <h3>Врачи</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Специальность</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($doctors as $doctor): ?>
                    <tr>
                        <td><?= htmlspecialchars($doctor['id']) ?></td>
                        <td><?= htmlspecialchars($doctor['full_name']) ?></td>
                        <td><?= htmlspecialchars($doctor['specialty']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <h3>Все записи на прием</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Пациент</th>
                    <th>Врач</th>
                    <th>Специальность</th>
                    <th>Дата приема</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?= htmlspecialchars($appointment['id']) ?></td>
                        <td><?= htmlspecialchars($appointment['user_name']) ?></td>
                        <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
                        <td><?= htmlspecialchars($appointment['specialty']) ?></td>
                        <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Основной шаблон макета страницы
 */
function renderLayout(string $view, array $data = []): string
{
    $content = '';
    
    switch ($view) {
        case 'index':
            $content = renderIndexView();
            break;
        case 'register':
            $content = renderRegisterView($data['errors'] ?? [], $data['formData'] ?? []);
            break;
        case 'login':
            $content = renderLoginView($data['errors'] ?? []);
            break;
        case 'profile':
            $content = renderProfileView($data['appointments'] ?? []);
            break;
        case 'specialties':
            $content = renderSpecialtiesView($data['specialties'] ?? []);
            break;
        case 'booking':
            $content = renderBookingView(
                $data['specialties'] ?? [],
                $data['selectedSpecialty'] ?? '',
                $data['selectedDate'] ?? '',
                $data['availableDoctors'] ?? [],
                $data['errors'] ?? []
            );
            break;
        case 'add_doctor':
            $content = renderAddDoctorView($data['errors'] ?? [], $data['formData'] ?? []);
            break;
        case 'admin':
            $content = renderAdminView($data['doctors'] ?? [], $data['appointments'] ?? []);
            break;
    }
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Здоровый образ жизни!</title>
        <link rel="stylesheet" href="/style.css">
    </head>
    <body>
        <header>
            <nav>
                <div class="logo">Здоровый образ жизни!</div>
                <ul>
                    <li><a href="/">Главная</a></li>
                    <?php
                    session_start();
                    if (isset($_SESSION['user_id'])) {
                        echo '<li><a href="/profile">Профиль</a></li>';
                        echo '<li><a href="/logout">Выйти</a></li>';
                    } elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
                        echo '<li><a href="/admin">Админка</a></li>';
                        echo '<li><a href="/logout">Выйти</a></li>';
                    } else {
                        echo '<li><a href="/login">Вход</a></li>';
                        echo '<li><a href="/register">Регистрация</a></li>';
                    }
                    session_write_close();
                    ?>
                </ul>
            </nav>
        </header>
        
        <main>
            <?= $content ?>
        </main>
        
        <footer>
            <p>&copy; 2023 Здоровый образ жизни! Все права защищены.</p>
        </footer>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
?>