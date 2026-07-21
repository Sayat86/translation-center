<?php

if (!defined('APP_STARTED')) {
    http_response_code(403);
    exit;
}

/*
|--------------------------------------------------------------------------
| Только POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);

if ($contentLength > 10000) {

    securityLog('Large POST');

    http_response_code(413);

    exit;
}

/*
|--------------------------------------------------------------------------
| Honeypot
|--------------------------------------------------------------------------
*/

if (!empty($_POST['website'])) {

    securityLog('Honeypot');

    http_response_code(403);
    exit;
}

if (mb_strlen($_POST['name'] ?? '') > 100) {

    securityLog('Long name');

    http_response_code(400);

    exit;
}

if (mb_strlen($_POST['comment'] ?? '') > 3000) {

    securityLog('Long comment');

    http_response_code(400);

    exit;
}

/*
|--------------------------------------------------------------------------
| Проверка времени заполнения формы
|--------------------------------------------------------------------------
*/

$formStart = (int)($_POST['form_start'] ?? 0);

if ($formStart > 0) {

    $seconds = (microtime(true) * 1000 - $formStart) / 1000;

    if ($seconds > 0 && $seconds < 2) {

        securityLog('Too Fast');

        http_response_code(403);

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| User-Agent
|--------------------------------------------------------------------------
*/

if (empty($_SERVER['HTTP_USER_AGENT'])) {

    securityLog('Empty User-Agent');

    http_response_code(403);
    exit;
}

/*
|--------------------------------------------------------------------------
| Проверка Referer
|--------------------------------------------------------------------------
*/

$allowedHosts = [
    'aibaniz.kz',
    'www.aibaniz.kz'
];

$referer = $_SERVER['HTTP_REFERER'] ?? '';

if ($referer !== '') {

    $host = parse_url($referer, PHP_URL_HOST);

    if (!in_array($host, $allowedHosts, true)) {

        securityLog('Bad Referer');

        http_response_code(403);
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Проверка Origin
|--------------------------------------------------------------------------
*/

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin !== '') {

    $host = parse_url($origin, PHP_URL_HOST);

    if (!in_array($host, $allowedHosts, true)) {

        securityLog('Bad Origin');

        http_response_code(403);
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Ограничение запросов по IP
|--------------------------------------------------------------------------
*/

checkRateLimit();

function checkRateLimit(): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $dir = __DIR__ . '/logs/rate_limit';

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $file = $dir . '/' . md5($ip);

    $fp = fopen($file, 'c+');

    if (!$fp) {
        return;
    }

    if (!flock($fp, LOCK_EX)) {
    fclose($fp);
    return;
}

    $content = stream_get_contents($fp);

    $requests = json_decode($content, true);

    if (!is_array($requests)) {
        $requests = [];
    }

    $now = time();

    $requests = array_filter($requests, function ($t) use ($now) {
        return ($now - $t) < 60;
    });

    if (count($requests) >= 5) {

        flock($fp, LOCK_UN);
        fclose($fp);

        securityLog('Rate Limit');

        http_response_code(429);

        echo json_encode([
            'ok' => false,
            'error' => 'Слишком много запросов. Попробуйте позже.'
        ]);

        exit;
    }

    $requests[] = $now;

    rewind($fp);
    ftruncate($fp, 0);
    $json = json_encode(array_values($requests));

if ($json !== false) {
    fwrite($fp, $json);
}

    fflush($fp);

    flock($fp, LOCK_UN);

    fclose($fp);
}

/*
|--------------------------------------------------------------------------
| Логирование
|--------------------------------------------------------------------------
*/

function securityLog(string $reason): void
{
    $dir = __DIR__ . '/logs';

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $line =
    "[" . date('Y-m-d H:i:s') . "] " .
    $reason .
    " | IP: " . ($_SERVER['REMOTE_ADDR'] ?? '-') .
    " | Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? '-') .
    " | Referer: " . ($_SERVER['HTTP_REFERER'] ?? '-') .
    " | UA: " . ($_SERVER['HTTP_USER_AGENT'] ?? '-') .
    PHP_EOL;

    file_put_contents(
        $dir . '/security.log',
        $line,
        FILE_APPEND | LOCK_EX
    );
}