<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once dirname(__DIR__) . '/lib/analytics_store.php';

function json_response(int $code, array $body): void
{
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') {
    json_response(405, ['ok' => false, 'error' => 'method_not_allowed']);
}

$raw = file_get_contents('php://input');
$input = json_decode($raw !== false ? $raw : '', true);
if (!is_array($input)) {
    json_response(400, ['ok' => false, 'error' => 'invalid_json']);
}

$action = (string)($input['action'] ?? '');
if ($action === '') {
    json_response(400, ['ok' => false, 'error' => 'action_required']);
}

$ts = time();
$login = history_cookie_login(); 
$path = (string)($input['path'] ?? '');


if ($path !== '') {
    $u = parse_url($path);
    $path = is_array($u) && isset($u['path']) ? (string)$u['path'] : $path;
}

if ($action === 'pageview') {
    if ($path === '' || $path[0] !== '/') {
        $path = (string)($_SERVER['REQUEST_URI'] ?? '/');
        $u = parse_url($path);
        $path = is_array($u) && isset($u['path']) ? (string)$u['path'] : $path;
    }


    if (str_starts_with($path, '/api/')) {
        json_response(200, ['ok' => true, 'skipped' => true]);
    }

    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    $ipHash = $ip !== '' ? substr(hash('sha256', $ip), 0, 16) : '';
    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');

    analytics_append_jsonl('pageviews.jsonl', [
        'ts' => $ts,
        'day' => date('Y-m-d', $ts),
        'path' => $path,
        'user' => $login,
        'ipHash' => $ipHash,
        'ua' => substr($ua, 0, 180),
    ]);

    json_response(200, ['ok' => true]);
}

if ($action === 'rating') {
    $score = isset($input['score']) ? (int)$input['score'] : 0;
    if ($score < 1 || $score > 10) {
        json_response(400, ['ok' => false, 'error' => 'invalid_score']);
    }

    $filmId = (string)($input['filmId'] ?? '');
    $context = (string)($input['context'] ?? '');
    if (strlen($context) > 60) $context = substr($context, 0, 60);

    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    $ipHash = $ip !== '' ? substr(hash('sha256', $ip), 0, 16) : '';

    analytics_append_jsonl('ratings.jsonl', [
        'ts' => $ts,
        'day' => date('Y-m-d', $ts),
        'score' => $score,
        'user' => $login,
        'filmId' => $filmId,
        'context' => $context,
        'ipHash' => $ipHash,
    ]);

    json_response(200, ['ok' => true]);
}

json_response(400, ['ok' => false, 'error' => 'unknown_action']);

