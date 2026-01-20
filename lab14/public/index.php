<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/src/database.php';
require __DIR__ . '/src/models.php';
require __DIR__ . '/src/controllers.php';
require_once __DIR__ . '/src/views.php';

$controller = new HealthClinicController($databaseConnection);

// Роутинг
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/lab14/public', '', $path);
$path = trim($path, '/');

// Обработка маршрутов
if ($path === '' || $path === 'index.php') {
    $controller->index();
} elseif ($path === 'register') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->register();
    } else {
        $controller->showRegisterForm();
    }
} elseif ($path === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->login();
    } else {
        $controller->showLoginForm();
    }
} elseif ($path === 'logout') {
    $controller->logout();
} elseif ($path === 'profile') {
    $controller->profile();
} elseif ($path === 'specialties') {
    $controller->showSpecialties();
} elseif ($path === 'booking') {
    $controller->showBookingForm();
} elseif ($path === 'book') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->bookAppointment();
    }
} elseif ($path === 'cancel-appointment') {
    $controller->cancelAppointment();
} elseif ($path === 'admin') {
    $controller->adminPanel();
} elseif ($path === 'admin/add-doctor') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->addDoctor();
    } else {
        $controller->showAddDoctorForm();
    }
} else {
    http_response_code(404);
    echo '<h1>404 - Страница не найдена</h1>';
}
?>