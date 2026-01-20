<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('DB_TYPE', 'sqlite');
define('DB_NAME', 'health_clinic_db.sqlite');
define('DB_FILE', __DIR__ . '/../db/' . DB_NAME);

// Создаем директорию для базы данных, если её нет
if (!file_exists(__DIR__ . '/../db')) {
    mkdir(__DIR__ . '/../db', 0755, true);
}

/**
 * Получение соединения с базой данных
 * @return PDO Объект соединения с базой данных
 */
function getDBConnection(): PDO
{
    try {
        $dsn = sprintf('sqlite:%s', DB_FILE);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, null, null, $options);
    } catch (PDOException $exception) {
        die('Ошибка подключения к базе данных: ' . $exception->getMessage());
    }
}

$databaseConnection = getDBConnection();
?>