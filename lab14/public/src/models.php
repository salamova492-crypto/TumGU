<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Репозиторий для работы с данными клиники "Здоровый образ жизни"
 */
class HealthClinicRepository
{
    /**
     * Конструктор репозитория
     */
    public function __construct(private PDO $db) {}
    
    /**
     * Получение пользователя по email
     */
    public function getUserByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM users
            WHERE email = :email
        ");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Создание нового пользователя
     */
    public function createUser(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (full_name, email, password_hash)
            VALUES (:full_name, :email, :password_hash)
        ");
        
        $stmt->bindValue(':full_name', $data['full_name']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':password_hash', $data['password_hash']);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Получение всех специальностей врачей
     */
    public function getAllSpecialties(): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT specialty FROM doctors
            ORDER BY specialty
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    
    /**
     * Получение всех врачей
     */
    public function getAllDoctors(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM doctors
            ORDER BY specialty, full_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Получение врачей по специальности
     */
    public function getDoctorsBySpecialty(string $specialty): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM doctors
            WHERE specialty = :specialty
            ORDER BY full_name
        ");
        $stmt->bindValue(':specialty', $specialty, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Получение врача по ID
     */
    public function getDoctorById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM doctors
            WHERE id = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Создание нового врача
     */
    public function createDoctor(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO doctors (full_name, specialty)
            VALUES (:full_name, :specialty)
        ");
        
        $stmt->bindValue(':full_name', $data['full_name']);
        $stmt->bindValue(':specialty', $data['specialty']);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Получение всех записей пользователей
     */
    public function getAllAppointments(): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, u.full_name as user_name, d.full_name as doctor_name, d.specialty
            FROM appointments a
            JOIN users u ON a.user_id = u.id
            JOIN doctors d ON a.doctor_id = d.id
            ORDER BY a.appointment_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Получение записей пользователя по ID
     */
    public function getAppointmentsByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, d.full_name as doctor_name, d.specialty, d.id as doctor_id
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.user_id = :user_id
            ORDER BY a.appointment_date DESC
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Получение записей на конкретную дату и врачу
     */
    public function getAppointmentsForDateAndDoctor(string $date, int $doctorId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM appointments
            WHERE appointment_date = :date AND doctor_id = :doctor_id
        ");
        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Создание новой записи
     */
    public function createAppointment(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO appointments (user_id, doctor_id, appointment_date)
            VALUES (:user_id, :doctor_id, :appointment_date)
        ");
        
        $stmt->bindValue(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':doctor_id', $data['doctor_id'], PDO::PARAM_INT);
        $stmt->bindValue(':appointment_date', $data['appointment_date']);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Удаление записи
     */
    public function deleteAppointment(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM appointments WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }
    
    /**
     * Получение доступных врачей для записи на определенную дату
     */
    public function getAvailableDoctorsForDate(string $date): array
    {
        $stmt = $this->db->prepare("
            SELECT d.id, d.full_name, d.specialty, 
                   (5 - COALESCE(appointment_counts.count, 0)) as available_slots
            FROM doctors d
            LEFT JOIN (
                SELECT doctor_id, COUNT(*) as count
                FROM appointments
                WHERE appointment_date = :date
                GROUP BY doctor_id
            ) appointment_counts ON d.id = appointment_counts.doctor_id
            WHERE COALESCE(appointment_counts.count, 0) < 5
            ORDER BY d.specialty, d.full_name
        ");
        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Получение количества доступных слотов для врача на определенную дату
     */
    public function getAvailableSlotsForDoctorOnDate(int $doctorId, string $date): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as booked_count
            FROM appointments
            WHERE doctor_id = :doctor_id AND appointment_date = :date
        ");
        $stmt->bindValue(':doctor_id', $doctorId, PDO::PARAM_INT);
        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        return max(0, 5 - (int)$result['booked_count']);
    }
    
    /**
     * Проверка, является ли пользователь администратором
     */
    public function isAdmin(string $login, string $password): bool
    {
        return $login === 'admin' && $password === 'WwSsRr';
    }
}
?>