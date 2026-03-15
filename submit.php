<?php

declare(strict_types=1);

function respond(int $statusCode, string $message): never {
    http_response_code($statusCode);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;
    exit;
}

function redirectToQuote(string $location): never {
    http_response_code(303);
    header('Location: ' . $location);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Redirecting...";
    exit;
}

function quoteLocation(string $submitted): string {
    // Query string must come before the fragment.
    return '/?submitted=' . rawurlencode($submitted) . '#quote';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    respond(405, 'Method Not Allowed');
}

// Optional same-origin check (only enforced when Origin header exists).
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '') {
    $originHost = parse_url($origin, PHP_URL_HOST);
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (!is_string($originHost) || $originHost === '' || $host === '' || strcasecmp($originHost, $host) !== 0) {
        respond(403, 'Forbidden');
    }
}

// Basic anti-bot honeypot.
$botField = trim((string)($_POST['bot-field'] ?? ''));
if ($botField !== '') {
    // Pretend success to avoid tipping off bots.
    redirectToQuote(quoteLocation('1'));
}

$name = trim((string)($_POST['name'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$zip = trim((string)($_POST['zip'] ?? ''));

if ($name === '' || $phone === '' || $email === '' || $zip === '') {
    redirectToQuote(quoteLocation('0'));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectToQuote(quoteLocation('0'));
}

// Light normalization to keep CSV clean.
$name = preg_replace('/\s+/', ' ', $name) ?? $name;
$phone = preg_replace('/\s+/', ' ', $phone) ?? $phone;
$zip = preg_replace('/\s+/', ' ', $zip) ?? $zip;

$ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
$userAgent = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
$referrer = (string)($_SERVER['HTTP_REFERER'] ?? '');
$timestamp = gmdate('c');

// Basic per-IP rate limiting.
$rateLimitFile = __DIR__ . '/data/rate_limit.json';
$rateWindowSeconds = 60 * 60; // 1 hour
$rateMax = 12;
$now = time();

$rateData = [];
if (is_file($rateLimitFile)) {
    $raw = @file_get_contents($rateLimitFile);
    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $rateData = $decoded;
        }
    }
}

if ($ip !== '') {
    $events = $rateData[$ip] ?? [];
    if (!is_array($events)) {
        $events = [];
    }
    $events = array_values(array_filter($events, fn($t) => is_int($t) && ($now - $t) <= $rateWindowSeconds));
    if (count($events) >= $rateMax) {
        redirectToQuote(quoteLocation('0'));
    }
    $events[] = $now;
    $rateData[$ip] = $events;

    @file_put_contents($rateLimitFile, json_encode($rateData), LOCK_EX);
}

$leadsFile = __DIR__ . '/data/leads.csv';
$isNew = !is_file($leadsFile);

$fp = @fopen($leadsFile, 'ab');
if ($fp === false) {
    respond(500, 'Server misconfiguration: cannot write leads file');
}

if (!flock($fp, LOCK_EX)) {
    fclose($fp);
    respond(500, 'Server error');
}

if ($isNew) {
    fputcsv($fp, ['timestamp', 'name', 'phone', 'email', 'zip', 'ip', 'user_agent', 'referrer']);
}

fputcsv($fp, [$timestamp, $name, $phone, $email, $zip, $ip, $userAgent, $referrer]);

flock($fp, LOCK_UN);
fclose($fp);

redirectToQuote(quoteLocation('1'));
