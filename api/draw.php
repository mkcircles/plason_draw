<?php
/**
 * API Endpoint: Draw Winner
 * Atomically selects, commits, and returns a verified winning number
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../config.php';

try {
    $config = new Config();

    // Support both JSON and standard POST payload
    $rawInput = file_get_contents('php://input');
    $data = [];
    if (!empty($rawInput)) {
        $data = json_decode($rawInput, true) ?: [];
    }
    if (empty($data)) {
        $data = $_POST;
    }

    $region = !empty($data['region']) ? trim($data['region']) : (!empty($_GET['region']) ? trim($_GET['region']) : null);
    $drawType = !empty($data['draw_type']) ? trim($data['draw_type']) : 'daily';
    $prize = !empty($data['prize']) ? trim($data['prize']) : 'Paint and Win Prize';
    $msisdn = !empty($data['msisdn']) ? preg_replace('/[^0-9]/', '', $data['msisdn']) : null;
    $fileUsed = !empty($data['file_used']) ? trim($data['file_used']) : 'live_draw_ui';

    // If msisdn was not passed, pick an eligible random winner from codes (area vs full draw)
    if (empty($msisdn)) {
        $msisdn = $config->drawWinnerFromPool($region);
        if (empty($msisdn)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'No eligible entries available for draw in this region.'
            ]);
            exit;
        }
    }

    // Verify not in excluded list
    $excluded = array_flip($config->getExclude());
    if (isset($excluded[$msisdn])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Number ' . Config::formatPhone($msisdn) . ' has already won or is excluded.'
        ]);
        exit;
    }

    // Record winner atomically
    $saved = $config->recordDrawnWinner($msisdn, $drawType, $region, $prize, $fileUsed);

    if (!$saved) {
        throw new Exception('Database error while recording winner.');
    }

    echo json_encode([
        'status' => 'success',
        'winner' => [
            'msisdn' => $msisdn,
            'formatted' => Config::formatPhone($msisdn, false),
            'masked' => Config::formatPhone($msisdn, true),
            'region' => $region ?: 'National',
            'draw_type' => $drawType,
            'prize' => $prize,
            'drawn_at' => date('Y-m-d H:i:s')
        ]
    ], JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
