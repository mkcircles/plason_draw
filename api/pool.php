<?php
/**
 * API Endpoint: Draw Pool Numbers
 * Returns list of eligible phone numbers for the rolling reel
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../config.php';

try {
    $config = new Config();
    $region = isset($_GET['region']) ? trim($_GET['region']) : null;
    $limit = isset($_GET['limit']) ? max(50, min(1000, (int)$_GET['limit'])) : 300;

    $numbers = $config->getPoolNumbers($region, $limit);

    // Format display versions
    $formatted = [];
    foreach ($numbers as $num) {
        $formatted[] = [
            'raw' => $num,
            'display' => Config::formatPhone($num, false),
            'masked' => Config::formatPhone($num, true)
        ];
    }

    echo json_encode([
        'status' => 'success',
        'count' => count($numbers),
        'region' => $region ?: 'All Regions',
        'numbers' => $numbers,
        'items' => $formatted
    ], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
