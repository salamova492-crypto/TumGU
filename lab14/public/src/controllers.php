<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/models.php';

/**
 * Контроллер для работы с клиникой "Здоровый образ жизни"
 */
class HealthClinicController
{
    /**
     * Репозиторий для работы с данными
     */
    private HealthClinicRepository $repository;
    
    /**
     * Конструктор контроллера
     */
    public function __construct(PDO $db)
    {
        $this->repository = new HealthClinicRepository($db);
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
     * Отображение формы регистрации
     */
    public function showRegisterForm(): void
    {
        $html = renderLayout('register');
        echo $html;
    }
    
    /**
     * Обработка регистрации пользователя
     */
    public function register(): void
    {
        $errors = [];
        $formData = $_POST ?? [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Валидация данных
            if (empty($fullName)) {
                $errors[] = 'Полное имя обязательно для заполнения';
            } else {
                // Проверка, что имя содержит только кириллические буквы и пробелы
                if (!preg_match('/^[А-Яа-яЁё\s]+$/u', $fullName)) {
                    $errors[] = 'Полное имя должно содержать только кириллические буквы и пробелы';
                }
            }
            
            if (empty($email)) {
                $errors[] = 'Электронная почта обязательна для заполнения';
            } else {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Некорректный формат электронной почты';
                } else {
                    // Проверка уникальности email в базе данных
                    $existingUser = $this->repository->getUserByEmail($email);
                    if ($existingUser) {
                        $errors[] = 'Пользователь с такой электронной почтой уже существует';
                    }
                }
            }
            
            if (empty($password)) {
                $errors[] = 'Пароль обязателен для заполнения';
            } else {
                if (strlen($password) < 6) {
                    $errors[] = 'Пароль должен содержать не менее 6 символов';
                }
                // Проверка, что пароль использует английскую раскладку
                if (!preg_match('/^[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]*$/', $password)) {
                    $errors[] = 'Пароль должен использовать английскую клавиатурную раскладку';
                }
            }
            
            if (empty($confirmPassword)) {
                $errors[] = 'Подтверждение пароля обязательно';
            } else {
                if ($password !== $confirmPassword) {
                    $errors[] = 'Пароли не совпадают';
                }
            }
            
            if (empty($errors)) {
                // Хешируем пароль с помощью md5 (как указано в требованиях)
                $passwordHash = md5($password);
                
                $data = [
                    'full_name' => $fullName,
                    'email' => $email,
                    'password_hash' => $passwordHash
                ];
                
                $userId = $this->repository->createUser($data);
                
                // Устанавливаем сессию для зарегистрированного пользователя
                session_start();
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_full_name'] = $fullName;
                
                header('Location: /profile');
                exit;
            }
        }
        
        $html = renderLayout('register', [
            'errors' => $errors,
            'formData' => $formData
        ]);
        echo $html;
    }
    
    /**
     * Отображение формы авторизации
     */
    public function showLoginForm(): void
    {
        $html = renderLayout('login');
        echo $html;
    }
    
    /**
     * Обработка авторизации пользователя
     */
    public function login(): void
    {
        $errors = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = trim($_POST['login'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($login)) {
                $errors[] = 'Логин обязателен для заполнения';
            }
            
            if (empty($password)) {
                $errors[] = 'Пароль обязателен для заполнения';
            }
            
            if (empty($errors)) {
                // Проверяем, не является ли пользователь администратором
                if ($this->repository->isAdmin($login, $password)) {
                    session_start();
                    $_SESSION['admin_logged_in'] = true;
                    
                    header('Location: /admin');
                    exit;
                } else {
                    // Ищем пользователя по email
                    $user = $this->repository->getUserByEmail($login);
                    
                    if ($user && $user['password_hash'] === md5($password)) {
                        // Успешная авторизация
                        session_start();
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_full_name'] = $user['full_name'];
                        
                        header('Location: /profile');
                        exit;
                    } else {
                        $errors[] = 'Неверный логин или пароль';
                    }
                }
            }
        }
        
        $html = renderLayout('login', ['errors' => $errors]);
        echo $html;
    }
    
    /**
     * Выход из системы
     */
    public function logout(): void
    {
        session_start();
        session_destroy();
        header('Location: /');
        exit;
    }
    
    /**
     * Отображение профиля пользователя
     */
    public function profile(): void
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $appointments = $this->repository->getAppointmentsByUserId($userId);
        
        $html = renderLayout('profile', [
            'appointments' => $appointments
        ]);
        echo $html;
    }
    
    /**
     * Отображение списка специальностей врачей
     */
    public function showSpecialties(): void
    {
        $specialties = $this->repository->getAllSpecialties();
        
        $html = renderLayout('specialties', [
            'specialties' => $specialties
        ]);
        echo $html;
    }
    
    /**
     * Отображение формы бронирования
     */
    public function showBookingForm(): void
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $specialties = $this->repository->getAllSpecialties();
        
        $selectedSpecialty = $_GET['specialty'] ?? '';
        $selectedDate = $_GET['date'] ?? '';
        
        $availableDoctors = [];
        if ($selectedSpecialty && $selectedDate) {
            $availableDoctors = $this->repository->getAvailableDoctorsForDate($selectedDate);
            // Фильтруем врачей по выбранной специальности
            $availableDoctors = array_filter($availableDoctors, function($doctor) use ($selectedSpecialty) {
                return $doctor['specialty'] === $selectedSpecialty;
            });
        }
        
        $html = renderLayout('booking', [
            'specialties' => $specialties,
            'selectedSpecialty' => $selectedSpecialty,
            'selectedDate' => $selectedDate,
            'availableDoctors' => $availableDoctors
        ]);
        echo $html;
    }
    
    /**
     * Обработка бронирования
     */
    public function bookAppointment(): void
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $errors = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $doctorId = (int)($_POST['doctor_id'] ?? 0);
            $date = trim($_POST['date'] ?? '');
            
            if (empty($doctorId) || $doctorId <= 0) {
                $errors[] = 'Выберите врача';
            }
            
            if (empty($date)) {
                $errors[] = 'Выберите дату';
            } else {
                // Проверяем формат даты
                $dateObj = DateTime::createFromFormat('Y-m-d', $date);
                if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
                    $errors[] = 'Неверный формат даты';
                } else {
                    // Проверяем, что дата не в прошлом
                    $today = new DateTime();
                    $today->setTime(0, 0, 0); // Установим время на начало дня для сравнения
                    if ($dateObj < $today) {
                        $errors[] = 'Нельзя записаться на прошедшую дату';
                    }
                }
            }
            
            if (empty($errors)) {
                // Проверяем, есть ли свободные места у врача на эту дату
                $availableSlots = $this->repository->getAvailableSlotsForDoctorOnDate($doctorId, $date);
                if ($availableSlots <= 0) {
                    $errors[] = 'У выбранного врача нет свободных мест на выбранную дату';
                }
            }
            
            if (empty($errors)) {
                $data = [
                    'user_id' => $_SESSION['user_id'],
                    'doctor_id' => $doctorId,
                    'appointment_date' => $date
                ];
                
                $appointmentId = $this->repository->createAppointment($data);
                
                header('Location: /profile?booked=1');
                exit;
            }
        }
        
        // Если были ошибки, возвращаемся к форме бронирования
        $specialties = $this->repository->getAllSpecialties();
        $selectedSpecialty = $_POST['specialty'] ?? '';
        $selectedDate = $_POST['date'] ?? '';
        
        $availableDoctors = [];
        if ($selectedSpecialty && $selectedDate) {
            $availableDoctors = $this->repository->getAvailableDoctorsForDate($selectedDate);
            // Фильтруем врачей по выбранной специальности
            $availableDoctors = array_filter($availableDoctors, function($doctor) use ($selectedSpecialty) {
                return $doctor['specialty'] === $selectedSpecialty;
            });
        }
        
        $html = renderLayout('booking', [
            'specialties' => $specialties,
            'selectedSpecialty' => $selectedSpecialty,
            'selectedDate' => $selectedDate,
            'availableDoctors' => $availableDoctors,
            'errors' => $errors
        ]);
        echo $html;
    }
    
    /**
     * Отображение формы добавления врача (для администратора)
     */
    public function showAddDoctorForm(): void
    {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header('Location: /login');
            exit;
        }
        
        $html = renderLayout('add_doctor');
        echo $html;
    }
    
    /**
     * Добавление врача
     */
    public function addDoctor(): void
    {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header('Location: /login');
            exit;
        }
        
        $errors = [];
        $formData = $_POST ?? [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = trim($_POST['full_name'] ?? '');
            $specialty = trim($_POST['specialty'] ?? '');
            
            if (empty($fullName)) {
                $errors[] = 'Полное имя обязательно для заполнения';
            } else {
                // Проверка, что имя содержит только кириллические буквы и пробелы
                if (!preg_match('/^[А-Яа-яЁё\s]+$/u', $fullName)) {
                    $errors[] = 'Полное имя должно содержать только кириллические буквы и пробелы';
                }
            }
            
            if (empty($specialty)) {
                $errors[] = 'Специальность обязательна для заполнения';
            }
            
            if (empty($errors)) {
                $data = [
                    'full_name' => $fullName,
                    'specialty' => $specialty
                ];
                
                $doctorId = $this->repository->createDoctor($data);
                
                header('Location: /admin?added=1');
                exit;
            }
        }
        
        $html = renderLayout('add_doctor', [
            'errors' => $errors,
            'formData' => $formData
        ]);
        echo $html;
    }
    
    /**
     * Отображение админ панели
     */
    public function adminPanel(): void
    {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header('Location: /login');
            exit;
        }
        
        $doctors = $this->repository->getAllDoctors();
        $appointments = $this->repository->getAllAppointments();
        
        $html = renderLayout('admin', [
            'doctors' => $doctors,
            'appointments' => $appointments
        ]);
        echo $html;
    }
    
    /**
     * Отмена записи
     */
    public function cancelAppointment(): void
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $appointmentId = (int)($_GET['id'] ?? 0);
        $userId = $_SESSION['user_id'];
        
        if ($appointmentId > 0) {
            // Получаем информацию о записи для проверки даты
            $stmt = $this->repository->db->prepare("
                SELECT appointment_date FROM appointments 
                WHERE id = :id AND user_id = :user_id
            ");
            $stmt->bindValue(':id', $appointmentId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $appointment = $stmt->fetch();
            
            if ($appointment) {
                // Проверяем, можно ли отменить запись (не позже, чем за день до)
                $appointmentDate = new DateTime($appointment['appointment_date']);
                $today = new DateTime();
                $today->setTime(0, 0, 0);
                
                // Если запись на сегодня или на будущее, и еще не прошло 24 часа до приема
                if ($appointmentDate >= $today) {
                    $this->repository->deleteAppointment($appointmentId, $userId);
                }
            }
        }
        
        header('Location: /profile');
        exit;
    }
}
?>