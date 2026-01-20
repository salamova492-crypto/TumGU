<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Генерация HTML для главной страницы
 */
function renderIndexView(): string
{
    ob_start();
    ?>
    <h2>База данных "Аренда"</h2>
    <div class="report-container">
        <p>Добро пожаловать в систему управления арендой! Здесь вы можете:</p>
        <ul>
            <li>Управлять объектами аренды</li>
            <li>Управлять арендаторами</li>
            <li>Вести учет сведений об аренде</li>
            <li>Формировать отчеты по различным критериям</li>
        </ul>
        <div class="form-actions">
            <a href="/objects" class="btn-primary">Объекты аренды</a>
            <a href="/renters" class="btn-primary">Арендаторы</a>
            <a href="/rentals" class="btn-primary">Сведения об аренде</a>
        </div>
        <h3>Доступные отчеты:</h3>
        <ul>
            <li><a href="/report/1">Список объектов указанных типов</a></li>
            <li><a href="/report/2">Список арендаторов с количеством аренд</a></li>
            <li><a href="/report/3">Список объектов, которые не сдавались</a></li>
            <li><a href="/report/4">Список объектов, которые сдавались более 3 раз</a></li>
            <li><a href="/report/5">Список объектов, которые сдавались больше 2 раз на срок более 1 года</a></li>
            <li><a href="/report/6">Список объектов с количеством сдач и общей суммой</a></li>
            <li><a href="/report/7">Список арендаторов со средним сроком аренды</a></li>
            <li><a href="/report/8">Список объектов, сданных в заданном квартале</a></li>
            <li><a href="/report/9">Список арендаторов с количеством различных арендуемых объектов</a></li>
            <li><a href="/report/10">Изменить цену аренды у объектов заданного типа</a></li>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для списка объектов аренды
 */
function renderObjectsView(array $objects): string
{
    ob_start();
    ?>
    <h2>Список объектов аренды</h2>
    <div class="form-actions">
        <a href="/objects/add" class="btn-primary">Добавить объект</a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Тип</th>
                    <th>Цена за месяц (руб.)</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($objects as $object): ?>
                    <tr>
                        <td><?= htmlspecialchars($object['id']) ?></td>
                        <td><?= htmlspecialchars($object['type']) ?></td>
                        <td><?= number_format($object['price_per_month'], 2) ?></td>
                        <td>
                            <form method="POST" action="/objects/delete/<?= $object['id'] ?>" style="display: inline;">
                                <button type="submit" class="btn-delete" onclick="return confirm('Вы уверены, что хотите удалить этот объект?')">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для формы добавления объекта аренды
 */
function renderAddObjectView(array $errors = [], array $formData = []): string
{
    ob_start();
    ?>
    <h2>Добавить объект аренды</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/objects/add">
        <div class="form-group">
            <label for="type">Тип объекта:</label>
            <input type="text" id="type" name="type" value="<?= htmlspecialchars($formData['type'] ?? '') ?>" required>
            <small>Тип объекта должен начинаться с заглавной буквы</small>
        </div>
        
        <div class="form-group">
            <label for="price_per_month">Цена за месяц (руб.):</label>
            <input type="number" id="price_per_month" name="price_per_month" step="0.01" value="<?= htmlspecialchars($formData['price_per_month'] ?? '') ?>" required>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">Добавить</button>
            <a href="/objects" class="btn-secondary-gray">Отмена</a>
        </div>
    </form>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для подтверждения удаления объекта
 */
function renderDeleteObjectView(array $object): string
{
    ob_start();
    ?>
    <h2>Удаление объекта аренды</h2>
    <div class="confirmation-box">
        <p>Вы уверены, что хотите удалить следующий объект?</p>
        <div class="person-details">
            <p><strong>Тип:</strong> <?= htmlspecialchars($object['type']) ?></p>
            <p><strong>Цена за месяц:</strong> <?= number_format($object['price_per_month'], 2) ?> руб.</p>
        </div>
        
        <form method="POST" action="/objects/delete/<?= $object['id'] ?>">
            <div class="form-actions">
                <button type="submit" class="btn-danger">Да, удалить</button>
                <a href="/objects" class="btn-secondary-gray">Отмена</a>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для списка арендаторов
 */
function renderRentersView(array $renters): string
{
    ob_start();
    ?>
    <h2>Список арендаторов</h2>
    <div class="form-actions">
        <a href="/renters/add" class="btn-primary">Добавить арендатора</a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Фамилия</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($renters as $renter): ?>
                    <tr>
                        <td><?= htmlspecialchars($renter['id']) ?></td>
                        <td><?= htmlspecialchars($renter['last_name']) ?></td>
                        <td>
                            <form method="POST" action="/renters/delete/<?= $renter['id'] ?>" style="display: inline;">
                                <button type="submit" class="btn-delete" onclick="return confirm('Вы уверены, что хотите удалить этого арендатора?')">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для формы добавления арендатора
 */
function renderAddRenterView(array $errors = [], array $formData = []): string
{
    ob_start();
    ?>
    <h2>Добавить арендатора</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/renters/add">
        <div class="form-group">
            <label for="last_name">Фамилия:</label>
            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>" required>
            <small>Фамилия должна начинаться с заглавной буквы</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">Добавить</button>
            <a href="/renters" class="btn-secondary-gray">Отмена</a>
        </div>
    </form>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для подтверждения удаления арендатора
 */
function renderDeleteRenterView(array $renter): string
{
    ob_start();
    ?>
    <h2>Удаление арендатора</h2>
    <div class="confirmation-box">
        <p>Вы уверены, что хотите удалить следующего арендатора?</p>
        <div class="person-details">
            <p><strong>Фамилия:</strong> <?= htmlspecialchars($renter['last_name']) ?></p>
        </div>
        
        <form method="POST" action="/renters/delete/<?= $renter['id'] ?>">
            <div class="form-actions">
                <button type="submit" class="btn-danger">Да, удалить</button>
                <a href="/renters" class="btn-secondary-gray">Отмена</a>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для списка сведений об аренде
 */
function renderRentalsView(array $rentals): string
{
    ob_start();
    ?>
    <h2>Сведения об аренде</h2>
    <div class="form-actions">
        <a href="/rentals/add" class="btn-primary">Добавить запись</a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Объект</th>
                    <th>Арендатор</th>
                    <th>Дата начала</th>
                    <th>Продолжительность (мес.)</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rentals as $rental): ?>
                    <tr>
                        <td><?= htmlspecialchars($rental['id']) ?></td>
                        <td><?= htmlspecialchars($rental['object_type']) ?></td>
                        <td><?= htmlspecialchars($rental['renter_last_name']) ?></td>
                        <td><?= htmlspecialchars($rental['start_date']) ?></td>
                        <td><?= htmlspecialchars($rental['duration_months']) ?></td>
                        <td>
                            <form method="POST" action="/rentals/delete/<?= $rental['id'] ?>" style="display: inline;">
                                <button type="submit" class="btn-delete" onclick="return confirm('Вы уверены, что хотите удалить эту запись?')">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для формы добавления сведений об аренде
 */
function renderAddRentalView(array $objects, array $renters, array $errors = [], array $formData = []): string
{
    ob_start();
    ?>
    <h2>Добавить сведение об аренде</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/rentals/add">
        <div class="form-group">
            <label for="object_id">Объект аренды:</label>
            <select id="object_id" name="object_id" required>
                <option value="">Выберите объект</option>
                <?php foreach ($objects as $object): ?>
                    <option value="<?= $object['id'] ?>" <?= ($formData['object_id'] ?? '') == $object['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($object['type']) ?> - <?= number_format($object['price_per_month'], 2) ?> руб./мес.
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="renter_id">Арендатор:</label>
            <select id="renter_id" name="renter_id" required>
                <option value="">Выберите арендатора</option>
                <?php foreach ($renters as $renter): ?>
                    <option value="<?= $renter['id'] ?>" <?= ($formData['renter_id'] ?? '') == $renter['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($renter['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="start_date">Дата начала аренды:</label>
            <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($formData['start_date'] ?? date('Y-m-01')) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="duration_months">Продолжительность (месяцев):</label>
            <input type="number" id="duration_months" name="duration_months" value="<?= htmlspecialchars($formData['duration_months'] ?? '') ?>" min="1" required>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">Добавить</button>
            <a href="/rentals" class="btn-secondary-gray">Отмена</a>
        </div>
    </form>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для подтверждения удаления сведений об аренде
 */
function renderDeleteRentalView(array $rental): string
{
    ob_start();
    ?>
    <h2>Удаление сведения об аренде</h2>
    <div class="confirmation-box">
        <p>Вы уверены, что хотите удалить следующую запись?</p>
        <div class="person-details">
            <p><strong>Объект:</strong> <?= htmlspecialchars($rental['object_type']) ?></p>
            <p><strong>Арендатор:</strong> <?= htmlspecialchars($rental['renter_last_name']) ?></p>
            <p><strong>Дата начала:</strong> <?= htmlspecialchars($rental['start_date']) ?></p>
            <p><strong>Продолжительность:</strong> <?= $rental['duration_months'] ?> месяцев</p>
        </div>
        
        <form method="POST" action="/rentals/delete/<?= $rental['id'] ?>">
            <div class="form-actions">
                <button type="submit" class="btn-danger">Да, удалить</button>
                <a href="/rentals" class="btn-secondary-gray">Отмена</a>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для отчета 1
 */
function renderReport1View(array $types, array $selectedTypes, string $sortOrder, array $results): string
{
    ob_start();
    ?>
    <h2>Отчет 1: Список объектов указанных типов</h2>
    <div class="report-container">
        <form method="GET" action="/report/1" class="filter-form">
            <div class="form-group">
                <label>Выберите типы объектов:</label>
                <div class="checkbox-group">
                    <?php foreach ($types as $type): ?>
                        <div>
                            <input type="checkbox" id="type_<?= md5($type) ?>" name="types[]" value="<?= htmlspecialchars($type) ?>" <?= in_array($type, $selectedTypes) ? 'checked' : '' ?>>
                            <label for="type_<?= md5($type) ?>"><?= htmlspecialchars($type) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-group">
                <label>Порядок сортировки:</label>
                <div class="radio-group">
                    <input type="radio" id="sort_alphabet_desc" name="sort" value="alphabet_desc" <?= $sortOrder === 'alphabet_desc' ? 'checked' : '' ?> required>
                    <label for="sort_alphabet_desc">По убыванию по алфавиту</label>
                    
                    <input type="radio" id="sort_price_asc" name="sort" value="price_asc" <?= $sortOrder === 'price_asc' ? 'checked' : '' ?>>
                    <label for="sort_price_asc">По возрастанию цены</label>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary">Показать</button>
            </div>
        </form>
        
        <?php if (!empty($results)): ?>
            <div class="table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Тип</th>
                            <th>Цена за месяц (руб.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?= htmlspecialchars($result['id']) ?></td>
                                <td><?= htmlspecialchars($result['type']) ?></td>
                                <td><?= number_format($result['price_per_month'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif (!empty($selectedTypes)): ?>
            <p class="alert error">По указанным критериям объекты не найдены.</p>
        <?php else: ?>
            <p class="alert">Пожалуйста, выберите хотя бы один тип объекта для отображения результатов.</p>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для отчета 2
 */
function renderReport2View(array $results): string
{
    ob_start();
    ?>
    <h2>Отчет 2: Список арендаторов с количеством аренд</h2>
    <div class="table-container">
        <table class="report-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Фамилия</th>
                    <th>Количество аренд</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?= htmlspecialchars($result['id']) ?></td>
                        <td><?= htmlspecialchars($result['last_name']) ?></td>
                        <td><?= $result['rental_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для отчета 3
 */
function renderReport3View(array $results): string
{
    ob_start();
    ?>
    <h2>Отчет 3: Список объектов, которые не сдавались</h2>
    <div class="table-container">
        <table class="report-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Тип</th>
                    <th>Цена за месяц (руб.)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?= htmlspecialchars($result['id']) ?></td>
                        <td><?= htmlspecialchars($result['type']) ?></td>
                        <td><?= number_format($result['price_per_month'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для отчета 4
 */
function renderReport4View(array $results): string
{
    ob_start();
    ?>
    <h2>Отчет 4: Список объектов, которые сдавались более 3 раз</h2>
    <div class="table-container">
        <table class="report-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Тип</th>
                    <th>Цена за месяц (руб.)</th>
                    <th>Количество аренд</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?= htmlspecialchars($result['id']) ?></td>
                        <td><?= htmlspecialchars($result['type']) ?></td>
                        <td><?= number_format($result['price_per_month'], 2) ?></td>
                        <td><?= $result['rental_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для отчета 5
 */
function renderReport5View(array $results): string
{
    ob_start();
    ?>
    <h2>Отчет 5: Список объектов, которые сдавались больше 2 раз на срок более 1 года</h2>
    <div class="table-container">
        <table class="report-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Тип</th>
                    <th>Цена за месяц (руб.)</th>
                    <th>Количество долгосрочных аренд</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?= htmlspecialchars($result['id']) ?></td>
                        <td><?= htmlspecialchars($result['type']) ?></td>
                        <td><?= number_format($result['price_per_month'], 2) ?></td>
                        <td><?= $result['long_rental_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для отчета 6
 */
function renderReport6View(array $results): string
{
    ob_start();
    ?>
    <h2>Отчет 6: Список объектов с количеством сдач и общей суммой</h2>
    <div class="table-container">
        <table class="report-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Тип</th>
                    <th>Цена за месяц (руб.)</th>
                    <th>Количество сдач</th>
                    <th>Общая сумма (руб.)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?= htmlspecialchars($result['id']) ?></td>
                        <td><?= htmlspecialchars($result['type']) ?></td>
                        <td><?= number_format($result['price_per_month'], 2) ?></td>
                        <td><?= $result['rental_count'] ?></td>
                        <td><?= number_format($result['total_amount'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для отчета 7
 */
function renderReport7View(array $results): string
{
    ob_start();
    ?>
    <h2>Отчет 7: Список арендаторов со средним сроком аренды</h2>
    <div class="table-container">
        <table class="report-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Фамилия</th>
                    <th>Количество аренд</th>
                    <th>Средний срок аренды (мес.)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?= htmlspecialchars($result['id']) ?></td>
                        <td><?= htmlspecialchars($result['last_name']) ?></td>
                        <td><?= $result['rental_count'] ?></td>
                        <td><?= number_format($result['avg_duration'], 1) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для формы отчета 8
 */
function renderReport8FormView(array $types, int $currentYear): string
{
    ob_start();
    ?>
    <h2>Отчет 8: Список объектов, сданных в заданном квартале</h2>
    <div class="report-container">
        <form method="POST" action="/report/8" class="filter-form">
            <div class="form-group">
                <label for="year">Год:</label>
                <input type="number" id="year" name="year" value="<?= $currentYear ?>" min="2000" max="<?= $currentYear + 1 ?>" required>
            </div>
            <div class="form-group">
                <label for="quarter">Квартал:</label>
                <select id="quarter" name="quarter" required>
                    <option value="1">I квартал (январь-март)</option>
                    <option value="2">II квартал (апрель-июнь)</option>
                    <option value="3">III квартал (июль-сентябрь)</option>
                    <option value="4">IV квартал (октябрь-декабрь)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Выберите типы объектов (необязательно):</label>
                <div class="checkbox-group">
                    <?php foreach ($types as $type): ?>
                        <div>
                            <input type="checkbox" id="type_<?= md5($type) ?>" name="types[]" value="<?= htmlspecialchars($type) ?>">
                            <label for="type_<?= md5($type) ?>"><?= htmlspecialchars($type) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary">Показать</button>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для результатов отчета 8
 */
function renderReport8ResultView(int $year, int $quarter, array $selectedTypes, array $results): string
{
    ob_start();
    ?>
    <h2>Отчет 8: Список объектов, сданных в <?= $quarter ?> квартале <?= $year ?> года</h2>
    <div class="report-container">
        <?php if (!empty($selectedTypes)): ?>
            <p><strong>Выбранные типы объектов:</strong> <?= implode(', ', array_map('htmlspecialchars', $selectedTypes)) ?></p>
        <?php endif; ?>
        
        <div class="table-container">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Объект</th>
                        <th>Тип</th>
                        <th>Арендатор</th>
                        <th>Дата начала</th>
                        <th>Продолжительность (мес.)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Нет записей для выбранного периода</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?= htmlspecialchars($result['type'] ?? '') ?></td>
                                <td><?= htmlspecialchars($result['object_type'] ?? $result['type']) ?></td>
                                <td><?= htmlspecialchars($result['renter_last_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($result['start_date'] ?? '') ?></td>
                                <td><?= $result['duration_months'] ?? 0 ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="form-actions">
            <a href="/report/8" class="btn-secondary">Новый запрос</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для отчета 9
 */
function renderReport9View(array $results): string
{
    ob_start();
    ?>
    <h2>Отчет 9: Список арендаторов с количеством различных арендуемых объектов</h2>
    <div class="table-container">
        <table class="report-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Фамилия</th>
                    <th>Количество различных объектов</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?= htmlspecialchars($result['id']) ?></td>
                        <td><?= htmlspecialchars($result['last_name']) ?></td>
                        <td><?= $result['unique_objects_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для формы отчета 10
 */
function renderReport10FormView(array $types): string
{
    ob_start();
    ?>
    <h2>Отчет 10: Изменить цену аренды у объектов заданного типа</h2>
    <div class="report-container">
        <p>Этот отчет увеличит цену аренды на 12% для всех объектов указанного типа.</p>
        <form method="POST" action="/report/10" class="filter-form">
            <div class="form-group">
                <label for="object_type">Тип объекта:</label>
                <select id="object_type" name="object_type" required>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>">
                            <?= htmlspecialchars($type) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-danger" onclick="return confirm('Вы уверены, что хотите увеличить цену на 12% для всех объектов этого типа?')">Изменить цены</button>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для результатов отчета 10
 */
function renderReport10ResultView(string $type, int $updatedCount): string
{
    ob_start();
    ?>
    <h2>Отчет 10: Изменение цены аренды</h2>
    <div class="report-container">
        <div class="alert success">
            <p>Цена аренды успешно увеличена на 12% для <?= $updatedCount ?> объектов типа "<?= htmlspecialchars($type) ?>"</p>
        </div>
        <div class="form-actions">
            <a href="/report/10" class="btn-secondary">Изменить другой тип</a>
            <a href="/" class="btn-secondary-gray">На главную</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерация HTML для страницы 404
 */
function render404View(array $data): string
{
    ob_start();
    ?>
    <h1>404 - Страница не найдена</h1>
    <p><?= htmlspecialchars($data['message'] ?? 'Запрашиваемая страница не существует') ?></p>
    <p><a href="/" class="btn-primary">Вернуться на главную</a></p>
    <?php
    return ob_get_clean();
}

/**
 * Генерация полного HTML макета
 */
function renderLayout(string $view, array $data = []): string
{
    extract($data);
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>База данных "Аренда"</title>
        <link rel="stylesheet" href="/style.css">
    </head>
    <body>
        <div class="container">
            <header>
                <h1>База данных "Аренда"</h1>
                <nav>
                    <ul>
                        <li><a href="/">Главная</a></li>
                        <li><a href="/objects">Объекты аренды</a></li>
                        <li><a href="/renters">Арендаторы</a></li>
                        <li><a href="/rentals">Сведения об аренде</a></li>
                        <li class="dropdown">
                            <a href="javascript:void(0)">Отчеты</a>
                            <div class="dropdown-content">
                                <a href="/report/1">Список объектов указанных типов</a>
                                <a href="/report/2">Список арендаторов с количеством аренд</a>
                                <a href="/report/3">Список объектов, которые не сдавались</a>
                                <a href="/report/4">Список объектов, которые сдавались более 3 раз</a>
                                <a href="/report/5">Список объектов, которые сдавались больше 2 раз на срок более 1 года</a>
                                <a href="/report/6">Список объектов с количеством сдач и общей суммой</a>
                                <a href="/report/7">Список арендаторов со средним сроком аренды</a>
                                <a href="/report/8">Список объектов, сданных в заданном квартале</a>
                                <a href="/report/9">Список арендаторов с количеством различных арендуемых объектов</a>
                                <a href="/report/10">Изменить цену аренды у объектов заданного типа</a>
                            </div>
                        </li>
                    </ul>
                </nav>
            </header>
            
            <main>
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert success">Запись успешно добавлена!</div>
                <?php endif; ?>
                
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert success">Запись успешно удалена!</div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert error">
                        <?php 
                        $errorMessage = match($_GET['error']) {
                            'not_found' => 'Запись не найдена!',
                            'invalid_id' => 'Некорректный ID записи!',
                            default => 'Произошла ошибка при обработке запроса.',
                        };
                        echo htmlspecialchars($errorMessage);
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php
                switch ($view) {
                    case 'index':
                        echo renderIndexView();
                        break;
                    case 'objects':
                        echo renderObjectsView($objects ?? []);
                        break;
                    case 'add_object':
                        echo renderAddObjectView($errors ?? [], $formData ?? []);
                        break;
                    case 'delete_object':
                        echo renderDeleteObjectView($object ?? []);
                        break;
                    case 'renters':
                        echo renderRentersView($renters ?? []);
                        break;
                    case 'add_renter':
                        echo renderAddRenterView($errors ?? [], $formData ?? []);
                        break;
                    case 'delete_renter':
                        echo renderDeleteRenterView($renter ?? []);
                        break;
                    case 'rentals':
                        echo renderRentalsView($rentals ?? []);
                        break;
                    case 'add_rental':
                        echo renderAddRentalView(
                            $objects ?? [],
                            $renters ?? [],
                            $errors ?? [],
                            $formData ?? []
                        );
                        break;
                    case 'delete_rental':
                        echo renderDeleteRentalView($rental ?? []);
                        break;
                    case 'report1':
                        echo renderReport1View(
                            $types ?? [],
                            $selectedTypes ?? [],
                            $sortOrder ?? 'alphabet_desc',
                            $results ?? []
                        );
                        break;
                    case 'report2':
                        echo renderReport2View($results ?? []);
                        break;
                    case 'report3':
                        echo renderReport3View($results ?? []);
                        break;
                    case 'report4':
                        echo renderReport4View($results ?? []);
                        break;
                    case 'report5':
                        echo renderReport5View($results ?? []);
                        break;
                    case 'report6':
                        echo renderReport6View($results ?? []);
                        break;
                    case 'report7':
                        echo renderReport7View($results ?? []);
                        break;
                    case 'report8_form':
                        echo renderReport8FormView(
                            $types ?? [],
                            $currentYear ?? date('Y')
                        );
                        break;
                    case 'report8_result':
                        echo renderReport8ResultView(
                            $year ?? date('Y'),
                            $quarter ?? 1,
                            $selectedTypes ?? [],
                            $results ?? []
                        );
                        break;
                    case 'report9':
                        echo renderReport9View($results ?? []);
                        break;
                    case 'report10_form':
                        echo renderReport10FormView($types ?? []);
                        break;
                    case 'report10_result':
                        echo renderReport10ResultView(
                            $type ?? '',
                            $updatedCount ?? 0
                        );
                        break;
                    case '404':
                        echo render404View($data ?? []);
                        break;
                    default:
                        echo '<p>Страница не найдена</p>';
                }
                ?>
            </main>
            
            <footer>
                <p>&copy; <?= date('Y') ?> База данных "Аренда". Все права защищены.</p>
            </footer>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
?>