<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once dirname(__DIR__) . '/lib/history_store.php';
require_once dirname(__DIR__) . '/lib/films_catalog.php';

function json_response(int $code, array $body): void
{
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

$login = history_cookie_login();
if ($login === null) {
    json_response(401, ['ok' => false, 'error' => 'login_required']);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET' && ($_GET['action'] ?? '') === 'status') {
    json_response(200, [
        'ok' => true,
        'purchasedFilmIds' => history_purchased_film_ids($login),
    ]);
}

if ($method !== 'POST') {
    json_response(405, ['ok' => false, 'error' => 'method_not_allowed']);
}

$raw = file_get_contents('php://input');
$input = json_decode($raw !== false ? $raw : '', true);
if (!is_array($input)) {
    json_response(400, ['ok' => false, 'error' => 'invalid_json']);
}

$action = $input['action'] ?? '';

if ($action === 'checkout') {
    $items = $input['items'] ?? null;
    if (!is_array($items) || $items === []) {
        json_response(400, ['ok' => false, 'error' => 'items_required']);
    }
    $catalog = films_catalog();
    $owned = array_flip(history_purchased_film_ids($login));
    $normalized = [];
    $seenInRequest = [];
    foreach ($items as $row) {
        if (!is_array($row)) {
            continue;
        }
        $fid = isset($row['filmId']) ? (string) $row['filmId'] : '';
        $title = isset($row['title']) ? (string) $row['title'] : '';
        $price = isset($row['price']) ? (int) $row['price'] : 0;
        if ($fid === '' || $title === '') {
            continue;
        }
        if (!isset($catalog[$fid])) {
            continue;
        }
        if ((int) ($catalog[$fid]['price'] ?? -1) !== $price) {
            continue;
        }
        if (isset($owned[$fid])) {
            continue;
        }
        if (isset($seenInRequest[$fid])) {
            continue;
        }
        $seenInRequest[$fid] = true;
        $normalized[] = ['filmId' => $fid, 'title' => $title, 'price' => $price];
    }
    if ($normalized === []) {
        json_response(400, ['ok' => false, 'error' => 'nothing_new_to_buy']);
    }
    history_append_purchases($login, $normalized);
    json_response(200, ['ok' => true, 'purchasedFilmIds' => history_purchased_film_ids($login)]);
}

if ($action === 'view') {
    $filmId = isset($input['filmId']) ? (string) $input['filmId'] : '';
    $title = isset($input['title']) ? (string) $input['title'] : '';
    if ($filmId === '' || $title === '') {
        json_response(400, ['ok' => false, 'error' => 'film_required']);
    }
    if (!isset(films_catalog()[$filmId])) {
        json_response(400, ['ok' => false, 'error' => 'invalid_film_id']);
    }
    if (!history_has_purchased($login, $filmId)) {
        json_response(403, ['ok' => false, 'error' => 'not_purchased']);
    }
    history_append_view($login, $filmId, $title);
    json_response(200, ['ok' => true]);
}

json_response(400, ['ok' => false, 'error' => 'unknown_action']);
