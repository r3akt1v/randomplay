<?php
require_once __DIR__ . '/../lib/analytics_json_export.php';

header('Content-Type: application/json');

$type = isset($_GET['type']) ? (string)$_GET['type'] : '';

if (!in_array($type, ['report', 'charts'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid type']);
    exit;
}

$data = generate_analytics_json($type);
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
