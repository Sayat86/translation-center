<?php

header('Content-Type: application/json; charset=UTF-8');

$config = require "config.php";

define('APP_STARTED', true);

require 'security.php';

function telegramLog(string $message): void
{
    $logDir = __DIR__ . '/logs';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/telegram.log';

    $line = sprintf(
        "[%s] %s%s",
        date('Y-m-d H:i:s'),
        $message,
        PHP_EOL
    );

    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}



/* ==========================
   Telegram
========================== */

$token = $config["token"];
$chatId = $config["chat_id"];

/* ==========================
   Получение данных
========================== */

$service = trim($_POST["service"] ?? "");
$name = trim($_POST["name"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$email = trim($_POST["email"] ?? "");
$comment = trim($_POST["comment"] ?? "");
$page = trim($_POST["page"] ?? "");

// Защита от слишком длинного URL
if (mb_strlen($page) > 500) {
    $page = mb_substr($page, 0, 500);
}

// Ограничение длины комментария
if (mb_strlen($comment) > 3000) {
    $comment = mb_substr($comment, 0, 3000);
}

$ip = $_SERVER["REMOTE_ADDR"] ?? "-";
$date = date("d.m.Y H:i");

/* ==========================
   Валидация данных
========================== */

// Обязательные поля
if ($name === '' || $phone === '') {

    echo json_encode([
        "ok" => false,
        "error" => "Заполните обязательные поля."
    ]);

    exit;
}

// Имя
if (!preg_match('/^[\p{L}\s\-]{2,100}$/u', $name)) {

    echo json_encode([
        "ok" => false,
        "error" => "Введите корректное имя."
    ]);

    exit;
}

// Телефон
$phoneDigits = preg_replace('/\D+/', '', $phone);

if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {

    echo json_encode([
        "ok" => false,
        "error" => "Введите корректный номер телефона."
    ]);

    exit;
}

// Email (если заполнен)
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo json_encode([
        "ok" => false,
        "error" => "Введите корректный Email."
    ]);

    exit;
}

/* ==========================
   Сообщение Telegram
========================== */

$message = "📩 Новая заявка с сайта Aibaniz\n\n";

$message .= "📄 Услуга: " . ($service !== '' ? $service : "—") . "\n";
$message .= "👤 Имя: {$name}\n";
$message .= "📞 Телефон: {$phone}\n";

if ($email !== '') {
    $message .= "📧 Email: {$email}\n";
}

if ($comment !== '') {
    $message .= "💬 Комментарий:\n{$comment}\n";
}

$message .= "\n🌐 Страница: {$page}";
$message .= "\n🕒 Время: {$date}";
$message .= "\n🌍 IP: {$ip}";

/* ==========================
   Отправка в Telegram
========================== */

$url = "https://api.telegram.org/bot{$token}/sendMessage";

$data = [
    "chat_id" => $chatId,
    "text" => $message
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$result = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

/* ==========================
   Ответ JavaScript
========================== */

if ($result === false) {

    telegramLog("cURL error: " . $error);

    echo json_encode([
        "ok" => false,
        "error" => "Не удалось отправить заявку. Попробуйте позже."
    ]);

    exit;
}

if ($httpCode !== 200) {

    telegramLog("Telegram API HTTP {$httpCode}. Response: {$result}");

    echo json_encode([
        "ok" => false,
        "error" => "Не удалось отправить заявку. Попробуйте позже."
    ]);

    exit;
}

echo json_encode([
    "ok" => true
]);