<?php
/**
 * API Endpoint: Draw History
 * Returns list of past drawn winners
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../config.php';

try {
    $config = new Config();
    $region = isset($_GET['region']) ? trim($_GET['region']) : null;
    $limit = isset($_GET['limit']) ? max(1, min(500, (int)$_GET['limit'])) : 100;

    $records = $config->getDrawnWinners($region, $limit);

    $formatted = [];
    foreach ($records as $r) {
        $formatted[] = [
            'id' => (int)$r['id'],
            'msisdn' => $r['msisdn'],
            'formatted' => Config::formatPhone($r['msisdn'], false),
            'masked' => Config::formatPhone($r['msisdn'], true),
            'region' => $r['region'] ?: 'National',
            'draw_type' => $r['draw_type'] ?: 'daily',
            'prize' => $r['prize'] ?: 'Prize',
            'drawn_at' => $r['drawn_at']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'count' => count($formatted),
        'winners' => $formatted
    ], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
