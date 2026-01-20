<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/src/database.php';
require __DIR__ . '/src/models.php';
require __DIR__ . '/src/controllers.php';
require_once __DIR__ . '/src/views.php';

$controller = new RentController($databaseConnection);

// Роутинг
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/lab14/public', '', $path);
$path = trim($path, '/');

// Обработка маршрутов
if ($path === '' || $path === 'index.php') {
    $controller->index();
} elseif ($path === 'objects') {
    $controller->showObjects();
} elseif ($path === 'objects/add') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->addRentalObject();
    } else {
        $controller->showAddObjectForm();
    }
} elseif (preg_match('#^objects/delete/(\d+)$#', $path, $matches)) {
    $controller->deleteRentalObject($matches[1]);
} elseif ($path === 'renters') {
    $controller->showRenters();
} elseif ($path === 'renters/add') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->addRenter();
    } else {
        $controller->showAddRenterForm();
    }
} elseif (preg_match('#^renters/delete/(\d+)$#', $path, $matches)) {
    $controller->deleteRenter($matches[1]);
} elseif ($path === 'rentals') {
    $controller->showRentals();
} elseif ($path === 'rentals/add') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->addRentalDetail();
    } else {
        $controller->showAddRentalForm();
    }
} elseif (preg_match('#^rentals/delete/(\d+)$#', $path, $matches)) {
    $controller->deleteRentalDetail($matches[1]);
} elseif ($path === 'report/1') {
    $controller->report1();
} elseif ($path === 'report/2') {
    $controller->report2();
} elseif ($path === 'report/3') {
    $controller->report3();
} elseif ($path === 'report/4') {
    $controller->report4();
} elseif ($path === 'report/5') {
    $controller->report5();
} elseif ($path === 'report/6') {
    $controller->report6();
} elseif ($path === 'report/7') {
    $controller->report7();
} elseif ($path === 'report/8') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $_POST;
        $types = isset($data['types']) ? $data['types'] : [];
        $controller->report8($data['year'], $data['quarter'], $types);
    } else {
        $controller->report8Form();
    }
} elseif ($path === 'report/9') {
    $controller->report9();
} elseif ($path === 'report/10') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->report10($_POST['object_type']);
    } else {
        $controller->report10Form();
    }
} else {
    http_response_code(404);
    echo '<h1>404 - Страница не найдена</h1>';
}
?>