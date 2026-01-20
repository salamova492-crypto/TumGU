<?php
session_start();

// Проверка авторизации пользователя
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Проверка прав администратора
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Получение информации о текущем пользователе
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    require_once 'db_connect.php';
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Проверка, является ли строка кириллическим текстом
function isCyrillic($text) {
    return preg_match('/^[\p{Cyrillic}\s]+$/u', $text);
}

// Валидация email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Валидация пароля (минимум 6 символов, только латинские буквы и цифры)
function isValidPassword($password) {
    return strlen($password) >= 6 && preg_match('/^[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]*$/', $password);
}

// Проверка, совпадают ли пароли
function passwordsMatch($password, $confirmPassword) {
    return $password === $confirmPassword;
}
?>