<div class="dashboard">
    <h2>Личный кабинет</h2>
    <p>Добро пожаловать, <?= htmlspecialchars($_SESSION['fullname']) ?>!</p>
    
    <!-- Форма записи к врачу -->
    <div class="appointment-form">
        <h3>Записаться к врачу</h3>
        <form method="GET">
            <input type="hidden" name="page" value="dashboard">
            <div class="form-group">
                <label for="specialty">Выберите специальность врача:</label>
                <select id="specialty" name="specialty" onchange="this.form.submit()">
                    <option value="">Все специальности</option>
                    <?php foreach ($specialties as $spec): ?>
                        <option value="<?= htmlspecialchars($spec) ?>" 
                            <?= (isset($_GET['specialty']) && $_GET['specialty'] === $spec) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($spec) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if (isset($_GET['specialty']) && !empty($_GET['specialty'])): ?>
                <div class="form-group">
                    <label for="appointment_date">Выберите дату:</label>
                    <input type="date" id="appointment_date" name="appointment_date" 
                           value="<?= $_GET['appointment_date'] ?? date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
                </div>
                
                <button type="submit" class="btn btn-secondary">Показать доступных врачей</button>
            <?php endif; ?>
        </form>
        
        <?php if (isset($_GET['specialty']) && !empty($_GET['specialty']) && isset($_GET['appointment_date'])): ?>
            <?php
            $selectedSpecialty = $_GET['specialty'];
            $selectedDate = $_GET['appointment_date'];
            
            // Получаем врачей этой специальности с количеством доступных слотов
            $sql = "SELECT d.id, d.fullname, d.specialty,
                           (5 - COALESCE(appointments_count.cnt, 0)) AS available_slots
                    FROM doctors d
                    LEFT JOIN (
                        SELECT doctor_id, COUNT(*) AS cnt
                        FROM appointments
                        WHERE appointment_date = ?
                        GROUP BY doctor_id
                    ) AS appointments_count ON d.id = appointments_count.doctor_id
                    WHERE d.specialty = ?
                    ORDER BY d.fullname";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$selectedDate, $selectedSpecialty]);
            $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <?php if (!empty($doctors)): ?>
                <h4>Доступные врачи на <?= $selectedDate ?></h4>
                <table class="doctors-table">
                    <thead>
                        <tr>
                            <th>ФИО врача</th>
                            <th>Специальность</th>
                            <th>Доступные слоты</th>
                            <th>Действие</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doctors as $doctor): ?>
                            <tr>
                                <td><?= htmlspecialchars($doctor['fullname']) ?></td>
                                <td><?= htmlspecialchars($doctor['specialty']) ?></td>
                                <td><?= $doctor['available_slots'] > 0 ? $doctor['available_slots'] : 'Нет мест' ?></td>
                                <td>
                                    <?php if ($doctor['available_slots'] > 0): ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Вы уверены, что хотите записаться к этому врачу?');">
                                            <input type="hidden" name="doctor_id" value="<?= $doctor['id'] ?>">
                                            <input type="hidden" name="appointment_date" value="<?= $selectedDate ?>">
                                            <button type="submit" name="book_appointment" class="btn btn-primary">Записаться</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="no-slots">Нет мест</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Нет доступных врачей этой специальности.</p>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if (isset($appointment_error)): ?>
            <div class="alert alert-error"><?= $appointment_error ?></div>
        <?php endif; ?>
        
        <?php if (isset($appointment_success)): ?>
            <div class="alert alert-success"><?= $appointment_success ?></div>
        <?php endif; ?>
    </div>
    
    <!-- Мои записи -->
    <div class="my-appointments">
        <h3>Мои записи</h3>
        <?php
        $stmt = $pdo->prepare("
            SELECT a.id, a.appointment_date, d.fullname as doctor_name, d.specialty
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.user_id = ?
            ORDER BY a.appointment_date ASC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <?php if (!empty($appointments)): ?>
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Врач</th>
                        <th>Специальность</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                            <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['specialty']) ?></td>
                            <td>
                                <?php 
                                $appointmentDate = new DateTime($appointment['appointment_date']);
                                $today = new DateTime();
                                $interval = $today->diff($appointmentDate);
                                
                                // Можно отменить только если до визита больше 1 дня
                                $canCancel = $interval->days > 1 || 
                                             ($interval->days == 1 && $appointmentDate->format('Y-m-d') !== $today->format('Y-m-d')) ||
                                             $appointmentDate->format('Y-m-d') !== $today->format('Y-m-d');
                                ?>
                                
                                <?php if ($canCancel): ?>
                                    <a href="?page=dashboard&cancel_appointment=<?= $appointment['id'] ?>" 
                                       onclick="return confirm('Вы уверены, что хотите отменить запись?');" 
                                       class="btn btn-danger">Отменить</a>
                                <?php else: ?>
                                    <span class="cant-cancel">Нельзя отменить</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>У вас нет записей к врачам.</p>
        <?php endif; ?>
    </div>
</div>