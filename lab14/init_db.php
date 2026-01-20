<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/public/src/database.php';

try {
    // Подключаемся к базе данных
    $databaseConnection = getDBConnection();

    // Создаем таблицу пользователей
    $databaseConnection->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");
    echo "Таблица users создана успешно.\n";

    // Создаем таблицу врачей
    $databaseConnection->exec("
        CREATE TABLE IF NOT EXISTS doctors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name TEXT NOT NULL,
            specialty TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");
    echo "Таблица doctors создана успешно.\n";

    // Создаем таблицу записей на прием
    $databaseConnection->exec("
        CREATE TABLE IF NOT EXISTS appointments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            doctor_id INTEGER NOT NULL,
            appointment_date DATE NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
        );
    ");
    echo "Таблица appointments создана успешно.\n";

    // Добавляем тестовых врачей
    $stmt = $databaseConnection->prepare("SELECT COUNT(*) FROM doctors");
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $testDoctors = [
            ['full_name' => 'Иванов Иван Иванович', 'specialty' => 'Терапевт'],
            ['full_name' => 'Петрова Мария Сергеевна', 'specialty' => 'Терапевт'],
            ['full_name' => 'Сидоров Алексей Петрович', 'specialty' => 'Хирург'],
            ['full_name' => 'Козлова Екатерина Андреевна', 'specialty' => 'Хирург'],
            ['full_name' => 'Волков Дмитрий Николаевич', 'specialty' => 'Невролог'],
            ['full_name' => 'Морозова Анна Владимировна', 'specialty' => 'Кардиолог'],
            ['full_name' => 'Лебедев Сергей Игоревич', 'specialty' => 'Офтальмолог']
        ];

        $stmt = $databaseConnection->prepare("INSERT INTO doctors (full_name, specialty) VALUES (?, ?)");
        foreach ($testDoctors as $doctor) {
            $stmt->execute([$doctor['full_name'], $doctor['specialty']]);
        }
        echo "Тестовые данные врачей добавлены.\n";
    } else {
        echo "Таблица doctors уже содержит данные.\n";
    }

    echo "Инициализация базы данных завершена успешно!\n";
} catch (PDOException $e) {
    die("Ошибка инициализации базы данных: " . $e->getMessage());
}
?>