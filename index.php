<?php
/**
 * Kansai Plascon Lucky Draw - Modern Broadcast Application
 * Unified, responsive system for promotional live draws across Uganda.
 */

require_once __DIR__ . '/config.php';

$config = new Config();

// Determine pre-selected region and settings (supports URL parameters and wrappers)
$selectedRegion = isset($_GET['region']) ? trim($_GET['region']) : 'All Regions';
$selectedType = isset($_GET['type']) ? trim($_GET['type']) : 'daily';
$targetCount = isset($_GET['count']) ? max(1, (int) $_GET['count']) : 8;

$regions = Config::getAvailableRegions();
if ($selectedRegion !== 'All Regions' && !in_array($selectedRegion, $regions, true)) {
    $regions[] = $selectedRegion;
    sort($regions);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kansai Plascon • Paint & Win Lucky Draw</title>

    <!-- Modern Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Application Styles -->
    <link rel="stylesheet" href="css/plascon-draw.css">

    <!-- Configuration bootstrap -->
    <script>
        window.PLASCON_INIT_REGION = <?= json_encode($selectedRegion) ?>;
        window.PLASCON_INIT_TYPE = <?= json_encode($selectedType) ?>;
        window.PLASCON_INIT_TARGET = <?= json_encode($targetCount) ?>;
    </script>
</head>

<body>
    <!-- Confetti Particle Layer -->
    <canvas id="confetti-canvas"></canvas>

    <div class="app-container">
        <!-- Top Broadcast Header -->
        <header class="broadcast-header">
            <div class="brand-section">
                <div class="brand-logo-badge">
                    <!-- Kansai Plascon SVG Icon -->
                    <div class="brand-title-group">
                        <h1>KANSAI PLASCON</h1>
                        <div class="subtitle">Paint &amp; Win Promotional Draw</div>
                    </div>
                </div>
                <div class="badge-tag">
                    <span class="badge-live-pulse"></span>
                    <span>LIVE SYSTEM</span>
                </div>
            </div>

            <div class="header-actions">
                <button type="button" class="btn-icon" id="btn-toggle-sound" title="Toggle Sound (M)">
                    <span>🔊 SOUND ON</span>
                </button>
                <button type="button" class="btn-icon" id="btn-toggle-privacy"
                    title="Mask phone numbers for TV broadcast">
                    <span>🔒 PRIVACY MASK</span>
                </button>
                <button type="button" class="btn-icon" id="btn-toggle-fullscreen" title="Fullscreen Broadcast Mode (F)">
                    <span>⛶ FULLSCREEN</span>
                </button>
                <button type="button" class="btn-icon" id="btn-toggle-history" title="View Drawn Winners (H)">
                    <span>📋 WINNERS LIST</span>
                </button>
            </div>
        </header>

        <!-- Main Stage -->
        <main class="main-stage">
            <div class="campaign-banner">
                <div class="campaign-tagline">COLOUR YOUR WORLD</div>
                <h2 class="campaign-headline">OFFICIAL LUCKY DRAW</h2>
                <div class="region-display-badge">
                    <span>📍 REGION:</span>
                    <span id="current-region-display"><?= htmlspecialchars(strtoupper($selectedRegion)) ?></span>
                </div>
            </div>

            <!-- 3D Mechanical Odometer Stage -->
            <div class="odometer-wrapper" id="odometer-wrapper">
                <div class="odometer-label-row">
                    <span>UGANDA MOBILE MSISDN</span>
                    <span>AUTOMATED RANDOMIZER</span>
                </div>

                <!-- Dynamic Reels Board -->
                <div class="reels-board" id="reels-board">
                    <!-- Reels populated dynamically by plascon-draw.js -->
                </div>
            </div>

            <!-- Presenter Controls HUD -->
            <div class="controls-hud">
                <button type="button" class="btn-primary-draw" id="btn-main-action">
                    <span>▶ START SPIN</span>
                </button>
                <button type="button" class="btn-icon" id="btn-next-winner"
                    style="padding: 16px 24px; font-size: 1.05rem; border-radius: 40px;">
                    <span>⚡ NEXT DRAW</span>
                </button>
            </div>

            <!-- Options & Filter Bar -->
            <div class="options-bar" style="margin-top: 20px;">
                <div class="select-control-group">
                    <label for="select-region">Region:</label>
                    <select id="select-region" class="select-control">
                        <option value="All Regions" <?= $selectedRegion === 'All Regions' ? 'selected' : '' ?>>All Regions
                            (National)</option>
                        <?php foreach ($regions as $r): ?>
                            <option value="<?= htmlspecialchars($r) ?>" <?= strcasecmp($selectedRegion, $r) === 0 ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="select-control-group">
                    <label for="select-draw-type">Draw Category:</label>
                    <select id="select-draw-type" class="select-control">
                        <option value="daily" <?= $selectedType === 'daily' ? 'selected' : '' ?>>Daily Draw</option>
                        <option value="weekly" <?= $selectedType === 'weekly' ? 'selected' : '' ?>>Weekly Draw</option>
                        <option value="regional" <?= $selectedType === 'regional' ? 'selected' : '' ?>>Regional Draw
                        </option>
                        <option value="grand" <?= $selectedType === 'grand' ? 'selected' : '' ?>>Grand Draw</option>
                    </select>
                </div>

                <div class="select-control-group">
                    <label for="select-target-count">Winners Target:</label>
                    <select id="select-target-count" class="select-control">
                        <option value="1" <?= $targetCount === 1 ? 'selected' : '' ?>>1 Winner</option>
                        <option value="2" <?= $targetCount === 2 ? 'selected' : '' ?>>2 Winners</option>
                        <option value="5" <?= $targetCount === 5 ? 'selected' : '' ?>>5 Winners</option>
                        <option value="8" <?= $targetCount === 8 ? 'selected' : '' ?>>8 Winners</option>
                        <option value="10" <?= $targetCount === 10 ? 'selected' : '' ?>>10 Winners</option>
                    </select>
                </div>
            </div>

            <!-- Keyboard Shortcuts Hint Bar -->
            <div class="hotkey-bar">
                <span class="hotkey-item"><kbd>Space</kbd> / <kbd>Enter</kbd> Start &amp; Stop</span>
                <span class="hotkey-item"><kbd>N</kbd> Next Draw</span>
                <span class="hotkey-item"><kbd>F</kbd> TV Broadcast Fullscreen</span>
                <span class="hotkey-item"><kbd>M</kbd> Mute Audio</span>
                <span class="hotkey-item"><kbd>Esc</kbd> Close Overlay</span>
            </div>
        </main>
    </div>

    <!-- Winner Celebration Modal -->
    <div class="winner-modal-overlay" id="winner-modal">
        <div class="winner-card">
            <div class="winner-trophy-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#FFC20E" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                    <path d="M4 22h16"></path>
                    <path d="M10 14.66V17c0 .55-.45 1-1 1H8v4h8v-4h-1c-.55 0-1-.45-1-1v-2.34"></path>
                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
                </svg>
            </div>
            <div class="winner-card-title">CONGRATULATIONS! WINNER DRAWN</div>
            <div class="winner-phone-number" id="modal-winner-phone">0772 ••• 413</div>

            <div class="winner-meta-row">
                <span class="winner-meta-badge">📍 <span id="modal-winner-region">Kampala</span></span>
                <span class="winner-meta-badge">🎁 <span id="modal-winner-prize">Paint &amp; Win Prize</span></span>
            </div>

            <div class="winner-actions">
                <button type="button" class="btn-secondary" id="btn-modal-close">Close</button>
                <button type="button" class="btn-primary-draw" id="btn-modal-next"
                    style="padding: 12px 32px; font-size: 1.1rem;">
                    <span>Next Winner ▶</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Past Winners History Slide-out Drawer -->
    <aside class="history-drawer" id="history-drawer">
        <div class="drawer-header">
            <h3>Drawn Winners</h3>
            <button type="button" class="drawer-close-btn" id="btn-close-drawer">&times;</button>
        </div>
        <div class="drawer-body">
            <ul class="winner-list-items" id="winner-list-items">
                <!-- Populated dynamically via API -->
            </ul>
        </div>
        <div class="drawer-footer">
            <button type="button" class="btn-export" id="btn-export-csv">
                <span>📥 Export to CSV (Excel)</span>
            </button>
        </div>
    </aside>

    <!-- Core Broadcast Script -->
    <script src="js/plascon-draw.js"></script>
</body>

</html>