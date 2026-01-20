<div class="admin-panel">
    <h2>Панель управления администратора</h2>
    
    <?php if ($userRole !== 'admin'): ?>
        <div class="alert alert-error">Доступ запрещен. Только администратор может просматривать эту страницу.</div>
        <?php return; endif; ?>
    
    <!-- Форма добавления врача -->
    <div class="add-doctor-form">
        <h3>Добавить нового врача</h3>
        
        <?php if (isset($doctor_success)): ?>
            <div class="alert alert-success"><?= $doctor_success ?></div>
        <?php endif; ?>
        
        <?php if (isset($doctor_error)): ?>
            <div class="alert alert-error"><?= $doctor_error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="doctor_fullname">ФИО врача *</label>
                <input type="text" id="doctor_fullname" name="doctor_fullname" required>
            </div>
            
            <div class="form-group">
                <label for="specialty">Специальность *</label>
                <input type="text" id="specialty" name="specialty" required>
            </div>
            
            <button type="submit" name="add_doctor" class="btn btn-primary">Добавить врача</button>
        </form>
    </div>
    
    <!-- Список всех врачей -->
    <div class="doctors-list">
        <h3>Все врачи</h3>
        <?php
        $stmt = $pdo->query("SELECT id, fullname, specialty FROM doctors ORDER BY specialty, fullname");
        $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <?php if (!empty($doctors)): ?>
            <table class="doctors-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ФИО врача</th>
                        <th>Специальность</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($doctors as $doctor): ?>
                        <tr>
                            <td><?= $doctor['id'] ?></td>
                            <td><?= htmlspecialchars($doctor['fullname']) ?></td>
                            <td><?= htmlspecialchars($doctor['specialty']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Нет зарегистрированных врачей.</p>
        <?php endif; ?>
    </div>
    
    <!-- Все записи -->
    <div class="all-appointments">
        <h3>Все записи пациентов</h3>
        <?php
        $stmt = $pdo->query("
            SELECT a.id, u.fullname as patient_name, u.email, 
                   d.fullname as doctor_name, d.specialty, 
                   a.appointment_date, a.created_at
            FROM appointments a
            JOIN users u ON a.user_id = u.id
            JOIN doctors d ON a.doctor_id = d.id
            ORDER BY a.appointment_date DESC
        ");
        $all_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <?php if (!empty($all_appointments)): ?>
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Пациент</th>
                        <th>Email</th>
                        <th>Врач</th>
                        <th>Специальность</th>
                        <th>Дата записи</th>
                        <th>Дата создания</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_appointments as $appointment): ?>
                        <tr>
                            <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['email']) ?></td>
                            <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['specialty']) ?></td>
                            <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                            <td><?= htmlspecialchars($appointment['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Нет записей пациентов.</p>
        <?php endif; ?>
    </div>
</div>