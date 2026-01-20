<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Репозиторий для работы с данными аренды
 */
class RentRepository
{
    /**
     * Конструктор репозитория
     */
    public function __construct(private PDO $db) {}
    
    /**
     * Получение всех объектов аренды
     */
    public function getAllRentalObjects(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM rental_objects
            ORDER BY type, price_per_month
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Получение объекта аренды по ID
     */
    public function getRentalObjectById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM rental_objects
            WHERE id = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Создание нового объекта аренды
     */
    public function createRentalObject(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO rental_objects (type, price_per_month)
            VALUES (:type, :price_per_month)
        ");
        
        $stmt->bindValue(':type', $data['type']);
        $stmt->bindValue(':price_per_month', $data['price_per_month'], PDO::PARAM_STR);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Удаление объекта аренды
     */
    public function deleteRentalObject(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM rental_objects WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }
    
    /**
     * Получение всех арендаторов
     */
    public function getAllRenters(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM renters
            ORDER BY last_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Получение арендатора по ID
     */
    public function getRenterById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM renters
            WHERE id = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Создание нового арендатора
     */
    public function createRenter(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO renters (last_name)
            VALUES (:last_name)
        ");
        
        $stmt->bindValue(':last_name', $data['last_name']);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Удаление арендатора
     */
    public function deleteRenter(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM renters WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }
    
    /**
     * Получение всех сведений об аренде
     */
    public function getAllRentalDetails(): array
    {
        $stmt = $this->db->prepare("
            SELECT rd.*, ro.type as object_type, ro.price_per_month,
                   r.last_name as renter_last_name
            FROM rental_details rd
            JOIN rental_objects ro ON rd.object_id = ro.id
            JOIN renters r ON rd.renter_id = r.id
            ORDER BY rd.start_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Получение сведений об аренде по ID
     */
    public function getRentalDetailById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT rd.*, ro.type as object_type, ro.price_per_month,
                   r.last_name as renter_last_name
            FROM rental_details rd
            JOIN rental_objects ro ON rd.object_id = ro.id
            JOIN renters r ON rd.renter_id = r.id
            WHERE rd.id = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Создание новой записи об аренде
     */
    public function createRentalDetail(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO rental_details (object_id, renter_id, start_date, duration_months)
            VALUES (:object_id, :renter_id, :start_date, :duration_months)
        ");
        
        $stmt->bindValue(':object_id', $data['object_id'], PDO::PARAM_INT);
        $stmt->bindValue(':renter_id', $data['renter_id'], PDO::PARAM_INT);
        $stmt->bindValue(':start_date', $data['start_date']);
        $stmt->bindValue(':duration_months', $data['duration_months'], PDO::PARAM_INT);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Удаление записи об аренде
     */
    public function deleteRentalDetail(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM rental_details WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }
    
    /**
     * Запрос 1: Список объектов указанных типов, упорядоченный по убыванию по алфавиту или по возрастанию цены
     */
    public function report1(array $types, string $sortOrder = 'alphabet_desc'): array
    {
        if (empty($types)) {
            return [];
        }
        
        if ($sortOrder === 'alphabet_desc') {
            $orderBy = "type DESC, price_per_month ASC";
        } else {
            $orderBy = "price_per_month ASC, type ASC";
        }
        
        $placeholders = implode(',', array_fill(0, count($types), '?'));
        
        $stmt = $this->db->prepare("
            SELECT * FROM rental_objects
            WHERE type IN ($placeholders)
            ORDER BY $orderBy
        ");
        
        $stmt->execute($types);
        return $stmt->fetchAll();
    }
    
    /**
     * Запрос 2: Список арендаторов, которым сдавались объекты с указанием количества аренд
     */
    public function report2(): array
    {
        $stmt = $this->db->prepare("
            SELECT r.id, r.last_name, COUNT(rd.id) as rental_count
            FROM renters r
            LEFT JOIN rental_details rd ON r.id = rd.renter_id
            GROUP BY r.id, r.last_name
            ORDER BY rental_count DESC, r.last_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Запрос 3: Список объектов, которые не сдавались
     */
    public function report3(): array
    {
        $stmt = $this->db->prepare("
            SELECT ro.* 
            FROM rental_objects ro
            LEFT JOIN rental_details rd ON ro.id = rd.object_id
            WHERE rd.id IS NULL
            ORDER BY ro.type, ro.price_per_month
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Запрос 4: Список объектов, которые сдавались более 3 раз
     */
    public function report4(): array
    {
        $stmt = $this->db->prepare("
            SELECT ro.*, COUNT(rd.id) as rental_count
            FROM rental_objects ro
            JOIN rental_details rd ON ro.id = rd.object_id
            GROUP BY ro.id, ro.type, ro.price_per_month
            HAVING COUNT(rd.id) > 3
            ORDER BY rental_count DESC, ro.type, ro.price_per_month
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Запрос 5: Список объектов, которые сдавались в аренду больше 2 раз на срок более 1 года со столбцом количество таких аренд
     */
    public function report5(): array
    {
        $stmt = $this->db->prepare("
            SELECT ro.*, COUNT(rd.id) as long_rental_count
            FROM rental_objects ro
            JOIN rental_details rd ON ro.id = rd.object_id
            WHERE rd.duration_months > 12
            GROUP BY ro.id, ro.type, ro.price_per_month
            HAVING COUNT(rd.id) > 2
            ORDER BY long_rental_count DESC, ro.type, ro.price_per_month
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Запрос 6: Список объектов со столбцами, содержащими количество сдач каждого объекта и выплаченную общую сумму
     */
    public function report6(): array
    {
        $stmt = $this->db->prepare("
            SELECT ro.*, 
                   COUNT(rd.id) as rental_count,
                   SUM(ro.price_per_month * rd.duration_months) as total_amount
            FROM rental_objects ro
            LEFT JOIN rental_details rd ON ro.id = rd.object_id
            GROUP BY ro.id, ro.type, ro.price_per_month
            ORDER BY total_amount DESC, rental_count DESC, ro.type
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Запрос 7: Список арендаторов с указанием, сколько раз он арендовал объекты и среднего срока аренды
     */
    public function report7(): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   COUNT(rd.id) as rental_count,
                   COALESCE(AVG(rd.duration_months), 0) as avg_duration
            FROM renters r
            LEFT JOIN rental_details rd ON r.id = rd.renter_id
            GROUP BY r.id, r.last_name
            ORDER BY rental_count DESC, avg_duration DESC, r.last_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Запрос 8: Список объектов (с указанием типа), сданных в аренду в заданном квартале определенного года
     */
    public function report8(int $year, int $quarter, array $types = []): array
    {
        // Определяем диапазон дат для квартала
        $quarterStartMonth = ($quarter - 1) * 3 + 1;
        $quarterEndMonth = $quarter * 3;
        
        $sql = "
            SELECT ro.*, rd.start_date, rd.duration_months, r.last_name as renter_last_name
            FROM rental_details rd
            JOIN rental_objects ro ON rd.object_id = ro.id
            JOIN renters r ON rd.renter_id = r.id
            WHERE YEAR(rd.start_date) = :year
              AND MONTH(rd.start_date) BETWEEN :start_month AND :end_month
        ";
        
        $params = [
            ':year' => $year,
            ':start_month' => $quarterStartMonth,
            ':end_month' => $quarterEndMonth
        ];
        
        if (!empty($types)) {
            $typePlaceholders = [];
            foreach ($types as $index => $type) {
                $placeholder = ":type_$index";
                $typePlaceholders[] = $placeholder;
                $params[$placeholder] = $type;
            }
            $sql .= " AND ro.type IN (" . implode(',', $typePlaceholders) . ")";
        }
        
        $sql .= " ORDER BY rd.start_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Запрос 9: Список арендаторов с указанием количества различных арендуемых объектов
     */
    public function report9(): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, COUNT(DISTINCT rd.object_id) as unique_objects_count
            FROM renters r
            LEFT JOIN rental_details rd ON r.id = rd.renter_id
            GROUP BY r.id, r.last_name
            ORDER BY unique_objects_count DESC, r.last_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Запрос 10: Изменить цену аренды у объектов заданного типа: увеличить на 12%
     */
    public function report10(string $type): int
    {
        $stmt = $this->db->prepare("
            UPDATE rental_objects
            SET price_per_month = price_per_month * 1.12
            WHERE type = :type
        ");
        $stmt->bindValue(':type', $type);
        $stmt->execute();
        return $stmt->rowCount();
    }
    
    /**
     * Получение всех типов объектов
     */
    public function getAllObjectTypes(): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT type FROM rental_objects
            ORDER BY type
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }
}
?>