<?php
/**
 * API Endpoint: Export Drawn Winners as CSV
 */

require_once __DIR__ . '/../config.php';

try {
    $config = new Config();
    $region = isset($_GET['region']) ? trim($_GET['region']) : null;
    $records = $config->getDrawnWinners($region, 1000);

    $filename = 'plascon_winners_' . ($region ? strtolower(preg_replace('/[^a-z0-9]/i', '_', $region)) . '_' : '') . date('Ymd_His') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // UTF-8 BOM for Microsoft Excel compatibility
    fputs($output, "\xEF\xBB\xBF");

    // CSV Header row
    fputcsv($output, [
        'ID',
        'Phone Number (Standard)',
        'Formatted (Local)',
        'Region',
        'Draw Type',
        'Prize Won',
        'Drawn At (Timestamp)'
    ], ',', '"', "\\");

    foreach ($records as $r) {
        fputcsv($output, [
            $r['id'],
            $r['msisdn'],
            Config::formatPhone($r['msisdn'], false),
            $r['region'] ?: 'National',
            ucfirst($r['draw_type'] ?? 'daily'),
            $r['prize'] ?: 'Prize',
            $r['drawn_at']
        ], ',', '"', "\\");
    }

    fclose($output);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo "Export Error: " . $e->getMessage();
}
