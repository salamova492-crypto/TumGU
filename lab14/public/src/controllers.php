<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/models.php';

/**
 * Контроллер для работы с базой данных аренды
 */
class RentController
{
    /**
     * Репозиторий для работы с данными
     */
    private RentRepository $repository;
    
    /**
     * Конструктор контроллера
     */
    public function __construct(PDO $db)
    {
        $this->repository = new RentRepository($db);
    }
    
    /**
     * Отображение главной страницы
     */
    public function index(): void
    {
        $html = renderLayout('index');
        echo $html;
    }
    
    /**
     * Отображение списка объектов аренды
     */
    public function showObjects(): void
    {
        $objects = $this->repository->getAllRentalObjects();
        $html = renderLayout('objects', ['objects' => $objects]);
        echo $html;
    }
    
    /**
     * Отображение формы добавления объекта аренды
     */
    public function showAddObjectForm(): void
    {
        $html = renderLayout('add_object');
        echo $html;
    }
    
    /**
     * Добавление нового объекта аренды
     */
    public function addRentalObject(): void
    {
        $errors = [];
        $formData = $_POST ?? [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = trim($_POST['type'] ?? '');
            $price = trim($_POST['price_per_month'] ?? '');
            
            // Валидация данных
            if (empty($type)) $errors[] = 'Тип объекта обязателен для заполнения';
            if (empty($price) || !is_numeric($price) || (float)$price <= 0) {
                $errors[] = 'Цена за месяц должна быть положительным числом';
            }
            // Добавляем валидацию на заглавную букву - исправленная версия
            if (!empty($type) && !preg_match('/^[А-ЯA-ZЁ]/u', $type)) {
                $errors[] = 'Тип объекта должен начинаться с заглавной буквы';
            }
            
            if (empty($errors)) {
                $data = [
                    'type' => $type,
                    'price_per_month' => (float)$price
                ];
                
                $id = $this->repository->createRentalObject($data);
                header('Location: /objects?success=1');
                exit;
            }
        }
        
        $html = renderLayout('add_object', [
            'errors' => $errors,
            'formData' => $formData
        ]);
        echo $html;
    }
    
    /**
     * Удаление объекта аренды
     */
    public function deleteRentalObject(string $id): void
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            header('Location: /objects?error=invalid_id');
            exit;
        }
        
        $objectId = (int)$id;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->repository->deleteRentalObject($objectId)) {
                header('Location: /objects?deleted=1');
            } else {
                header('Location: /objects?error=not_found');
            }
            exit;
        }
        
        $object = $this->repository->getRentalObjectById($objectId);
        
        if (!$object) {
            header('Location: /objects?error=not_found');
            exit;
        }
        
        $html = renderLayout('delete_object', ['object' => $object]);
        echo $html;
    }
    
    /**
     * Отображение списка арендаторов
     */
    public function showRenters(): void
    {
        $renters = $this->repository->getAllRenters();
        $html = renderLayout('renters', ['renters' => $renters]);
        echo $html;
    }
    
    /**
     * Отображение формы добавления арендатора
     */
    public function showAddRenterForm(): void
    {
        $html = renderLayout('add_renter');
        echo $html;
    }
    
    /**
     * Добавление нового арендатора
     */
    public function addRenter(): void
    {
        $errors = [];
        $formData = $_POST ?? [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lastName = trim($_POST['last_name'] ?? '');
            
            // Валидация данных
            if (empty($lastName)) $errors[] = 'Фамилия арендатора обязательна для заполнения';
            if (strlen($lastName) < 2) $errors[] = 'Фамилия должна содержать минимум 2 символа';
            if (strlen($lastName) > 50) $errors[] = 'Фамилия должна содержать максимум 50 символов';
            // Добавляем валидацию на заглавную букву - исправленная версия
            if (!empty($lastName) && !preg_match('/^[А-ЯA-ZЁ]/u', $lastName)) {
                $errors[] = 'Фамилия должна начинаться с заглавной буквы';
            }
            
            if (empty($errors)) {
                $data = [
                    'last_name' => $lastName
                ];
                
                $id = $this->repository->createRenter($data);
                header('Location: /renters?success=1');
                exit;
            }
        }
        
        $html = renderLayout('add_renter', [
            'errors' => $errors,
            'formData' => $formData
        ]);
        echo $html;
    }
    
    /**
     * Удаление арендатора
     */
    public function deleteRenter(string $id): void
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            header('Location: /renters?error=invalid_id');
            exit;
        }
        
        $renterId = (int)$id;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->repository->deleteRenter($renterId)) {
                header('Location: /renters?deleted=1');
            } else {
                header('Location: /renters?error=not_found');
            }
            exit;
        }
        
        $renter = $this->repository->getRenterById($renterId);
        
        if (!$renter) {
            header('Location: /renters?error=not_found');
            exit;
        }
        
        $html = renderLayout('delete_renter', ['renter' => $renter]);
        echo $html;
    }
    
    /**
     * Отображение списка сведений об аренде
     */
    public function showRentals(): void
    {
        $rentals = $this->repository->getAllRentalDetails();
        $html = renderLayout('rentals', ['rentals' => $rentals]);
        echo $html;
    }
    
    /**
     * Отображение формы добавления сведений об аренде
     */
    public function showAddRentalForm(): void
    {
        $objects = $this->repository->getAllRentalObjects();
        $renters = $this->repository->getAllRenters();
        $html = renderLayout('add_rental', [
            'objects' => $objects,
            'renters' => $renters
        ]);
        echo $html;
    }
    
    /**
     * Добавление новых сведений об аренде
     */
    public function addRentalDetail(): void
    {
        $objects = $this->repository->getAllRentalObjects();
        $renters = $this->repository->getAllRenters();
        $errors = [];
        $formData = $_POST ?? [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $objectId = $_POST['object_id'] ?? '';
            $renterId = $_POST['renter_id'] ?? '';
            $startDate = trim($_POST['start_date'] ?? '');
            $durationMonths = trim($_POST['duration_months'] ?? ''); // Исправлено: $durationMonths вместо $duration_months
            
            // Валидация данных
            if (empty($objectId) || !is_numeric($objectId) || (int)$objectId <= 0) {
                $errors[] = 'Выберите объект аренды';
            }
            if (empty($renterId) || !is_numeric($renterId) || (int)$renterId <= 0) {
                $errors[] = 'Выберите арендатора';
            }
            if (empty($startDate)) {
                $errors[] = 'Дата начала аренды обязательна';
            } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
                $errors[] = 'Неверный формат даты (должен быть ГГГГ-ММ-ДД)';
            } elseif (strtotime($startDate) === false) {
                $errors[] = 'Неверная дата';
            }
            
            if (empty($durationMonths) || !is_numeric($durationMonths)) {
                $errors[] = 'Продолжительность аренды должна быть числом';
            } elseif ((int)$durationMonths <= 0) {
                $errors[] = 'Продолжительность аренды должна быть положительным числом';
            } elseif ((int)$durationMonths > 120) { // Добавляем ограничение для предотвращения нарушения check constraint
                $errors[] = 'Продолжительность аренды не может превышать 120 месяцев (10 лет)';
            }
            
            // Дополнительная проверка на корректность даты
            if (!empty($startDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
                $dateParts = explode('-', $startDate);
                if (!checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0])) {
                    $errors[] = 'Неверная дата';
                }
            }
            
            if (empty($errors)) {
                $data = [
                    'object_id' => (int)$objectId,
                    'renter_id' => (int)$renterId,
                    'start_date' => $startDate,
                    'duration_months' => (int)$durationMonths // Исправлено: $durationMonths вместо $duration_months
                ];
                
                $id = $this->repository->createRentalDetail($data);
                header('Location: /rentals?success=1');
                exit;
            }
        }
        
        $html = renderLayout('add_rental', [
            'objects' => $objects,
            'renters' => $renters,
            'errors' => $errors,
            'formData' => $formData
        ]);
        echo $html;
    }
    
    /**
     * Удаление записи об аренде
     */
    public function deleteRentalDetail(string $id): void
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            header('Location: /rentals?error=invalid_id');
            exit;
        }
        
        $rentalId = (int)$id;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->repository->deleteRentalDetail($rentalId)) {
                header('Location: /rentals?deleted=1');
            } else {
                header('Location: /rentals?error=not_found');
            }
            exit;
        }
        
        $rental = $this->repository->getRentalDetailById($rentalId);
        
        if (!$rental) {
            header('Location: /rentals?error=not_found');
            exit;
        }
        
        $html = renderLayout('delete_rental', ['rental' => $rental]);
        echo $html;
    }
    
    /**
     * Запрос 1: Список объектов указанных типов
     */
    public function report1(): void
    {
        $types = $this->repository->getAllObjectTypes();
        $selectedTypes = $_GET['types'] ?? [];
        $sortOrder = $_GET['sort'] ?? 'alphabet_desc';
        
        // Преобразуем выбранные типы в массив, если передан один тип
        if (!is_array($selectedTypes) && !empty($selectedTypes)) {
            $selectedTypes = [$selectedTypes];
        }
        
        $results = $this->repository->report1($selectedTypes, $sortOrder);
        $html = renderLayout('report1', [
            'types' => $types,
            'selectedTypes' => $selectedTypes,
            'sortOrder' => $sortOrder,
            'results' => $results
        ]);
        echo $html;
    }
    
    /**
     * Запрос 2: Список арендаторов с количеством аренд
     */
    public function report2(): void
    {
        $results = $this->repository->report2();
        $html = renderLayout('report2', ['results' => $results]);
        echo $html;
    }
    
    /**
     * Запрос 3: Список объектов, которые не сдавались
     */
    public function report3(): void
    {
        $results = $this->repository->report3();
        $html = renderLayout('report3', ['results' => $results]);
        echo $html;
    }
    
    /**
     * Запрос 4: Список объектов, которые сдавались более 3 раз
     */
    public function report4(): void
    {
        $results = $this->repository->report4();
        $html = renderLayout('report4', ['results' => $results]);
        echo $html;
    }
    
    /**
     * Запрос 5: Список объектов, которые сдавались больше 2 раз на срок более 1 года
     */
    public function report5(): void
    {
        $results = $this->repository->report5();
        $html = renderLayout('report5', ['results' => $results]);
        echo $html;
    }
    
    /**
     * Запрос 6: Список объектов с количеством сдач и общей суммой
     */
    public function report6(): void
    {
        $results = $this->repository->report6();
        $html = renderLayout('report6', ['results' => $results]);
        echo $html;
    }
    
    /**
     * Запрос 7: Список арендаторов со средним сроком аренды
     */
    public function report7(): void
    {
        $results = $this->repository->report7();
        $html = renderLayout('report7', ['results' => $results]);
        echo $html;
    }
    
    /**
     * Форма для запроса 8
     */
    public function report8Form(): void
    {
        $types = $this->repository->getAllObjectTypes();
        $currentYear = date('Y');
        $html = renderLayout('report8_form', [
            'types' => $types,
            'currentYear' => $currentYear
        ]);
        echo $html;
    }
    
    /**
     * Запрос 8: Список объектов, сданных в заданном квартале
     */
    public function report8(int $year, int $quarter, array $types): void
    {
        // Очищаем от пустых значений
        $types = array_filter($types, fn($type) => !empty($type));
        
        $results = $this->repository->report8($year, $quarter, $types);
        $html = renderLayout('report8_result', [
            'year' => $year,
            'quarter' => $quarter,
            'selectedTypes' => $types,
            'results' => $results
        ]);
        echo $html;
    }
    
    /**
     * Запрос 9: Список арендаторов с количеством различных арендуемых объектов
     */
    public function report9(): void
    {
        $results = $this->repository->report9();
        $html = renderLayout('report9', ['results' => $results]);
        echo $html;
    }
    
    /**
     * Форма для запроса 10
     */
    public function report10Form(): void
    {
        $types = $this->repository->getAllObjectTypes();
        $html = renderLayout('report10_form', ['types' => $types]);
        echo $html;
    }
    
    /**
     * Запрос 10: Изменение цены аренды у объектов заданного типа
     */
    public function report10(string $type): void
    {
        $updatedCount = $this->repository->report10($type);
        $html = renderLayout('report10_result', [
            'type' => $type,
            'updatedCount' => $updatedCount
        ]);
        echo $html;
    }
}
?>