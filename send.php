<?php

header('Content-Type: application/json');

$token="ВАШ_ТОКЕН";

$chat_id="ВАШ_CHAT_ID";

$service = htmlspecialchars($_POST["service"] ?? "");

$name=htmlspecialchars($_POST["name"] ?? "");

$phone=htmlspecialchars($_POST["phone"] ?? "");

$email=htmlspecialchars($_POST["email"] ?? "");

$comment=htmlspecialchars($_POST["comment"] ?? "");

$page=htmlspecialchars($_POST["page"] ?? "");

$ip=$_SERVER["REMOTE_ADDR"];

$date=date("d.m.Y H:i");

$message="📩 Новая заявка с сайта Aibaniz

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

🕒 Время:
$date";

$url="https://api.telegram.org/bot$token/sendMessage";

$data=[

"chat_id"=>$chat_id,

"text"=>$message

];

$options=[

"http"=>[

"header"=>"Content-Type: application/x-www-form-urlencoded\r\n",

"method"=>"POST",

"content"=>http_build_query($data)

]

];

$context=stream_context_create($options);

$result=file_get_contents($url,false,$context);

echo json_encode([

"ok"=>$result!==false

]);