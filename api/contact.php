<?php
/**
 * Contact form endpoint.
 *
 * Accepts POST (JSON or form-encoded), validates the fields and forwards the
 * message to both WhatsApp numbers via the Meta WhatsApp Cloud API.
 * Credentials are read from environment variables / .env only.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../config/env.php';

function contact_response(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function contact_field(array $data, string $key): string
{
    $value = $data[$key] ?? '';
    if (!is_scalar($value)) return '';
    return trim((string)$value);
}

function contact_clean_text(string $text, int $maxLength): string
{
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? $text;
    $text = trim($text);

    return function_exists('mb_substr') ? mb_substr($text, 0, $maxLength) : substr($text, 0, $maxLength);
}

function contact_trim_error(string $message): string
{
    $message = trim($message);
    return function_exists('mb_substr') ? mb_substr($message, 0, 220) : substr($message, 0, 220);
}

function contact_recipients(): array
{
    $configured = [
        env('WHATSAPP_RECIPIENT_1', '255774634254'),
        env('WHATSAPP_RECIPIENT_2', '255719160587'),
    ];

    $numbers = [];
    foreach ($configured as $number) {
        $digits = preg_replace('/\D+/', '', (string)$number) ?? '';
        if ($digits !== '' && !in_array($digits, $numbers, true)) {
            $numbers[] = $digits;
        }
    }

    return $numbers;
}

function contact_send_whatsapp(string $to, string $body, string $token, string $phoneNumberId, string $version): array
{
    $url = sprintf(
        'https://graph.facebook.com/%s/%s/messages',
        rawurlencode($version),
        rawurlencode($phoneNumberId)
    );

    $payload = [
        'messaging_product' => 'whatsapp',
        'to' => $to,
        'type' => 'text',
        'text' => [
            'preview_url' => false,
            'body' => $body,
        ],
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $raw = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($raw === false) {
        return ['ok' => false, 'error' => contact_trim_error($error !== '' ? $error : 'Hitilafu ya muunganisho.')];
    }

    $response = json_decode($raw, true);
    if ($httpCode < 200 || $httpCode >= 300) {
        $message = is_array($response) && isset($response['error']['message'])
            ? (string)$response['error']['message']
            : 'WhatsApp API ilirudisha HTTP ' . $httpCode;
        return ['ok' => false, 'error' => contact_trim_error($message)];
    }

    if (!is_array($response)) {
        return ['ok' => false, 'error' => 'Jibu lisilotarajiwa kutoka WhatsApp API.'];
    }

    return ['ok' => true, 'id' => $response['messages'][0]['id'] ?? null];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    contact_response(405, [
        'success' => false,
        'message' => 'Njia hii hairuhusiwi. Tuma maombi kwa POST.',
    ]);
}

load_env();

$contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
if (strpos($contentType, 'application/json') !== false) {
    $data = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($data)) {
        contact_response(400, [
            'success' => false,
            'message' => 'Muundo wa maombi si sahihi.',
        ]);
    }
} else {
    $data = $_POST;
}

// Honeypot: real users never fill this hidden field.
if (contact_field($data, 'website') !== '') {
    contact_response(400, [
        'success' => false,
        'message' => 'Ombi halikukubaliwa.',
    ]);
}

$name    = contact_clean_text(contact_field($data, 'name'), 100);
$email   = contact_clean_text(contact_field($data, 'email'), 150);
$phone   = contact_clean_text(contact_field($data, 'phone'), 40);
$subject = contact_clean_text(contact_field($data, 'subject'), 150);
$message = contact_clean_text(contact_field($data, 'message'), 2000);

$errors = [];
if ($name === '') {
    $errors['name'] = 'Jina kamili linahitajika.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Barua pepe halali inahitajika.';
}
if ($phone === '' || !preg_match('/^[+0-9\s\-()]{7,30}$/', $phone)) {
    $errors['phone'] = 'Namba ya simu halali inahitajika.';
}
if ($subject === '') {
    $errors['subject'] = 'Kichwa cha ujumbe kinahitajika.';
}
if ($message === '') {
    $errors['message'] = 'Ujumbe unahitajika.';
}

if (!empty($errors)) {
    contact_response(422, [
        'success' => false,
        'message' => 'Tafadhali rekebisha taarifa zilizokosekana.',
        'errors' => $errors,
    ]);
}

$token = (string)env('WHATSAPP_ACCESS_TOKEN', '');
$phoneNumberId = (string)env('WHATSAPP_PHONE_NUMBER_ID', '');
$version = (string)env('WHATSAPP_API_VERSION', 'v23.0');

if ($token === '' || $phoneNumberId === '') {
    error_log('[contact] WhatsApp credentials are not configured.');
    contact_response(500, [
        'success' => false,
        'message' => 'Seva haijasanidiwa bado kutuma ujumbe. Tafadhali wasiliana na msimamizi.',
    ]);
}

$body = "*UJUMBE MPYA - Madrasatul Ibadu-rrahman*\n\n"
    . '*Jina:* ' . $name . "\n"
    . '*Barua Pepe:* ' . $email . "\n"
    . '*Simu:* ' . $phone . "\n"
    . '*Kichwa:* ' . $subject . "\n\n"
    . "*Ujumbe:*\n" . $message . "\n\n"
    . '_Umetumwa: ' . date('Y-m-d H:i:s') . '_';

$recipients = contact_recipients();
$sent = 0;
$failed = 0;

foreach ($recipients as $to) {
    $result = contact_send_whatsapp($to, $body, $token, $phoneNumberId, $version);
    if ($result['ok']) {
        $sent++;
    } else {
        $failed++;
        error_log('[contact] WhatsApp send failed for ' . $to . ': ' . $result['error']);
    }
}

if ($sent === count($recipients)) {
    contact_response(200, [
        'success' => true,
        'message' => 'Ujumbe wako umetumwa kwa WhatsApp. Asante kwa kuwasiliana nasi!',
        'sent' => $sent,
    ]);
}

if ($sent > 0) {
    contact_response(200, [
        'success' => true,
        'message' => 'Ujumbe umefika kwa namba ' . $sent . ' kati ya ' . count($recipients)
            . '. Namba nyingine haikupokea; tafadhali jaribu tena baadaye.',
        'sent' => $sent,
        'failed' => $failed,
    ]);
}

contact_response(502, [
    'success' => false,
    'message' => 'Ujumbe huukuweza kutumwa kwa sasa. Tafadhali jaribu tena baadaye.',
    'sent' => 0,
    'failed' => $failed,
]);
