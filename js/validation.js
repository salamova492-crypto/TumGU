// Файл для клиентской валидации форм

// Функция проверки, является ли строка кириллической
function isCyrillic(text) {
    // Проверяем, состоит ли строка только из кириллических символов и пробелов
    const cyrillicRegex = /^[\u0400-\u04FF\s]+$/;
    return cyrillicRegex.test(text);
}

// Функция проверки email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Функция проверки пароля (минимум 6 символов, только латинские буквы и цифры)
function isValidPassword(password) {
    return password.length >= 6 && /^[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]*$/.test(password);
}

// Функция проверки совпадения паролей
function passwordsMatch(password, confirmPassword) {
    return password === confirmPassword;
}