<?php

header('Content-Type: application/json; charset=UTF-8');

/* ==========================
   Telegram
========================== */

$config = require "config.php";

$token = $config["token"];
$chat_id = $config["chat_id"];

/* ==========================
   Получение данных
========================== */

$service = trim(htmlspecialchars($_POST["service"] ?? ""));
$name = trim(htmlspecialchars($_POST["name"] ?? ""));
$phone = trim(htmlspecialchars($_POST["phone"] ?? ""));
$email = trim(htmlspecialchars($_POST["email"] ?? ""));
$comment = trim(htmlspecialchars($_POST["comment"] ?? ""));
$page = trim(htmlspecialchars($_POST["page"] ?? ""));

$ip = $_SERVER["REMOTE_ADDR"] ?? "Неизвестно";
$date = date("d.m.Y H:i");

/* ==========================
   Проверка обязательных полей
========================== */

if (empty($name) || empty($phone)) {

    echo json_encode([
        "ok" => false,
        "error" => "Заполните обязательные поля."
    ]);

    exit;
}

/* ==========================
   Сообщение Telegram
========================== */

$message =
"📩 Новая заявка с сайта Aibaniz

📄 Услуга:
$service

👤 Имя:
$name

📞 Телефон:
$phone

📧 Email:
$email

💬 Комментарий:
$comment

🌐 Страница:
$page

🕒 Время:
$date

🌍 IP:
$ip";

/* ==========================
   Отправка в Telegram
========================== */

$url = "https://api.telegram.org/bot{$token}/sendMessage";

$data = [
    "chat_id" => $chat_id,
    "text" => $message
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$result = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

/* ==========================
   Ответ JavaScript
========================== */

if ($result === false || $httpCode !== 200) {

    echo json_encode([
        "ok" => false,
        "error" => $error ?: "Ошибка Telegram API (HTTP {$httpCode})."
    ]);

} else {

    echo json_encode([
        "ok" => true
    ]);

}