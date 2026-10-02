<?php
/**
 * Modern Configuration & Database Engine for Plascon Draw System
 * Handles database connection, pool generation, winner recording, and history.
 */

class Config
{
    private $host;
    private $user;
    private $password;
    private $database;
    private $connection = null;
    private $tableChecked = false;

    public function __construct()
    {
        $this->host = getenv('DB_HOST') ?: '127.0.0.1';
        $this->user = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
        $this->database = getenv('DB_NAME') ?: 'plascon';
    }

    /**
     * Get database connection with robust error handling
     * @return mysqli|false
     */
    public function getConnection()
    {
        if ($this->connection === null) {
            try {
                // Suppress default fatal exception behavior to handle errors gracefully
                mysqli_report(MYSQLI_REPORT_OFF);

                $conn = @new mysqli($this->host, $this->user, $this->password, $this->database);

                if ($conn->connect_error) {
                    error_log("Plascon Draw DB Connection Failed: " . $conn->connect_error);
                    return false;
                }

                $conn->set_charset("utf8mb4");
                $this->connection = $conn;

                if (!$this->tableChecked) {
                    $this->ensureDrawnNumbersTable();
                    $this->tableChecked = true;
                }
            } catch (Throwable $e) {
                error_log("Plascon Draw DB Exception: " . $e->getMessage());
                return false;
            }
        }

        return $this->connection;
    }

    /**
     * Automatically ensure drawn_numbers table exists
     */
    private function ensureDrawnNumbersTable()
    {
        if (!$this->connection) {
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `drawn_numbers` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `msisdn` VARCHAR(32) NOT NULL,
            `file_used` VARCHAR(100) DEFAULT NULL,
            `region` VARCHAR(50) DEFAULT NULL,
            `draw_type` VARCHAR(50) DEFAULT 'daily',
            `prize` VARCHAR(100) DEFAULT NULL,
            `drawn_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `ip_address` VARCHAR(45) DEFAULT NULL,
            INDEX `idx_drawn_msisdn` (`msisdn`),
            INDEX `idx_drawn_region` (`region`),
            INDEX `idx_drawn_type` (`draw_type`),
            INDEX `idx_drawn_at` (`drawn_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        @$this->connection->query($sql);
    }

    /**
     * Detect region column name in 'codes' table ('brand' vs 'area')
     */
    private function getRegionColumn(): string
    {
        $con = $this->getConnection();
        if (!$con) {
            return 'brand';
        }

        $res = @$con->query("SHOW COLUMNS FROM `codes` LIKE 'brand'");
        if ($res && $res->num_rows > 0) {
            return 'brand';
        }

        $res = @$con->query("SHOW COLUMNS FROM `codes` LIKE 'area'");
        if ($res && $res->num_rows > 0) {
            return 'area';
        }

        return 'brand';
    }

    /**
     * Get list of excluded phone numbers (past winners + already drawn)
     * @return array
     */
    public function getExclude(): array
    {
        $con = $this->getConnection();
        if (!$con) {
            return [];
        }

        $excluded = [];

        // Exclude past winners and blacklisted numbers
        $stmt = @$con->prepare("SELECT msisdn FROM past_winners");
        if ($stmt) {
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                if (!empty($row['msisdn'])) {
                    $excluded[trim($row['msisdn'])] = true;
                }
            }
            $stmt->close();
        }

        // Exclude numbers already drawn in the current system
        $stmt = @$con->prepare("SELECT msisdn FROM drawn_numbers");
        if ($stmt) {
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                if (!empty($row['msisdn'])) {
                    $excluded[trim($row['msisdn'])] = true;
                }
            }
            $stmt->close();
        }

        return array_keys($excluded);
    }

    /**
     * Get draw pool of phone numbers for live animation and drawing
     * - Area Draw: gets inMessageId where brand is the selected area
     * - Full Draw: gets from all inMessageId across all brands
     *
     * @param string|null $region Optional region/brand filter
     * @param int $limit Maximum numbers to retrieve (default 500)
     * @return array Array of valid phone numbers
     */
    public function getPoolNumbers(?string $region = null, int $limit = 500): array
    {
        $con = $this->getConnection();
        $numbers = [];
        $excluded = array_flip($this->getExclude());

        if ($con) {
            $regionCol = $this->getRegionColumn();
            $isAreaDraw = !empty($region) && !in_array(strtolower(trim($region)), ['all', 'all regions', 'national', 'full', 'full draw']);

            if ($isAreaDraw) {
                // Area Draw: inMessageId where brand is the selected area
                $stmt = @$con->prepare("SELECT inMessageId FROM codes WHERE `{$regionCol}` = ? AND inMessageId IS NOT NULL AND TRIM(inMessageId) != '' ORDER BY RAND() LIMIT ?");
                if ($stmt) {
                    $stmt->bind_param("si", $region, $limit);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        $phone = trim($row['inMessageId']);
                        if (!empty($phone) && !isset($excluded[$phone])) {
                            $numbers[] = $phone;
                        }
                    }
                    $stmt->close();
                }
            } else {
                // Full Draw: from all inMessageId across all brands
                $stmt = @$con->prepare("SELECT inMessageId FROM codes WHERE inMessageId IS NOT NULL AND TRIM(inMessageId) != '' ORDER BY RAND() LIMIT ?");
                if ($stmt) {
                    $stmt->bind_param("i", $limit);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        $phone = trim($row['inMessageId']);
                        if (!empty($phone) && !isset($excluded[$phone])) {
                            $numbers[] = $phone;
                        }
                    }
                    $stmt->close();
                }
            }
        }

        // If no real entries exist yet in the database, fall back to realistic demo numbers
        if (empty($numbers)) {
            // $fallback = $this->generateRealisticUgandaNumbers(300, $region);
            // foreach ($fallback as $fn) {
            //     if (!isset($excluded[$fn])) {
            //         $numbers[] = $fn;
            //     }
            // }
        }

        return array_values(array_unique($numbers));
    }

    /**
     * Draw a single winning phone number directly from codes table
     * - Area Draw: inMessageId where brand matches the selected area
     * - Full Draw: from all inMessageId across all brands
     * Excludes past_winners and already drawn numbers
     *
     * @param string|null $region
     * @return string|null Phone number (inMessageId)
     */
    public function drawWinnerFromPool(?string $region = null): ?string
    {
        $con = $this->getConnection();
        $excluded = $this->getExclude();

        if ($con) {
            $regionCol = $this->getRegionColumn();
            $isAreaDraw = !empty($region) && !in_array(strtolower(trim($region)), ['all', 'all regions', 'national', 'full', 'full draw']);

            $sql = "SELECT inMessageId FROM codes WHERE inMessageId IS NOT NULL AND TRIM(inMessageId) != ''";
            $types = "";
            $params = [];

            if ($isAreaDraw) {
                $sql .= " AND `{$regionCol}` = ?";
                $types .= "s";
                $params[] = $region;
            }

            if (!empty($excluded)) {
                $placeholders = implode(',', array_fill(0, count($excluded), '?'));
                $sql .= " AND inMessageId NOT IN ({$placeholders})";
                $types .= str_repeat('s', count($excluded));
                $params = array_merge($params, $excluded);
            }

            $sql .= " ORDER BY RAND() LIMIT 1";

            $stmt = @$con->prepare($sql);
            if ($stmt) {
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $winnerPhone = trim($row['inMessageId']);
                    $stmt->close();
                    if (!empty($winnerPhone)) {
                        return $winnerPhone;
                    }
                }
                $stmt->close();
            }
        }

        // Fallback to pool if direct database query didn't yield
        $pool = $this->getPoolNumbers($region, 100);
        if (!empty($pool)) {
            return $pool[array_rand($pool)];
        }

        return null;
    }

    /**
     * Generate realistic Ugandan phone numbers (MTN, Airtel) for testing & demo
     */
    public function generateRealisticUgandaNumbers(int $count = 200, ?string $region = null): array
    {
        $prefixes = [
            '25677',
            '25678',
            '25676', // MTN Uganda
            '25670',
            '25675',
            '25674'  // Airtel Uganda
        ];

        // Seeded or random generation
        $numbers = [];
        // Add the curated test numbers first
        $curated = explode(',', $this->getTestNumbers());
        foreach ($curated as $num) {
            $cleaned = trim($num);
            if (strlen($cleaned) >= 12) {
                $numbers[] = $cleaned;
            }
        }

        while (count($numbers) < $count) {
            $prefix = $prefixes[array_rand($prefixes)];
            $suffix = str_pad(mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
            $numbers[] = $prefix . $suffix;
        }

        shuffle($numbers);
        return array_slice($numbers, 0, $count);
    }

    /**
     * Backward-compatible getNumbers method
     */
    public function getNumbers(): string
    {
        $pool = $this->getPoolNumbers(null, 500);
        return implode(',', $pool);
    }

    /**
     * Backward-compatible getAreaNumbers method
     */
    public function getAreaNumbers(string $area): string
    {
        $pool = $this->getPoolNumbers($area, 200);
        return implode(',', $pool);
    }

    /**
     * Backward-compatible storeDrawnWinners method
     */
    public function storeDrawnWinners($number, $file)
    {
        return $this->recordDrawnWinner($number, 'daily', null, null, $file);
    }

    /**
     * Atomically record a drawn winner
     * @param string $msisdn
     * @param string $drawType
     * @param string|null $region
     * @param string|null $prize
     * @param string|null $fileUsed
     * @return bool
     */
    public function recordDrawnWinner(string $msisdn, string $drawType = 'daily', ?string $region = null, ?string $prize = null, ?string $fileUsed = null): bool
    {
        $con = $this->getConnection();
        if (!$con) {
            return false;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $stmt = @$con->prepare("INSERT INTO drawn_numbers (msisdn, draw_type, region, prize, file_used, ip_address, drawn_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        if (!$stmt) {
            error_log("Failed to prepare recordDrawnWinner statement: " . $con->error);
            return false;
        }

        $stmt->bind_param("ssssss", $msisdn, $drawType, $region, $prize, $fileUsed, $ip);
        $res = $stmt->execute();
        $stmt->close();

        return (bool) $res;
    }

    /**
     * Fetch list of drawn winners
     */
    public function getDrawnWinners(?string $region = null, int $limit = 100): array
    {
        $con = $this->getConnection();
        if (!$con) {
            return [];
        }

        $winners = [];
        if (!empty($region) && strtolower($region) !== 'all') {
            $stmt = @$con->prepare("SELECT id, msisdn, region, draw_type, prize, file_used, drawn_at FROM drawn_numbers WHERE region = ? ORDER BY id DESC LIMIT ?");
            if ($stmt) {
                $stmt->bind_param("si", $region, $limit);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $winners[] = $row;
                }
                $stmt->close();
            }
        } else {
            $stmt = @$con->prepare("SELECT id, msisdn, region, draw_type, prize, file_used, drawn_at FROM drawn_numbers ORDER BY id DESC LIMIT ?");
            if ($stmt) {
                $stmt->bind_param("i", $limit);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $winners[] = $row;
                }
                $stmt->close();
            }
        }

        return $winners;
    }

    /**
     * Format phone number to Ugandan standard (e.g. 0772 123 456)
     */
    public static function formatPhone(string $phone, bool $masked = false): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        // Convert 2567... to 07...
        if (strpos($clean, '256') === 0 && strlen($clean) === 12) {
            $clean = '0' . substr($clean, 3);
        }

        if (strlen($clean) === 10) {
            if ($masked) {
                return substr($clean, 0, 4) . ' ••• ' . substr($clean, 7);
            }
            return substr($clean, 0, 4) . ' ' . substr($clean, 4, 3) . ' ' . substr($clean, 7);
        }

        return $clean;
    }

    /**
     * Get distinct brands/regions from the codes table in the database
     * @return array
     */
    public function fetchAvailableRegions(): array
    {
        $con = $this->getConnection();
        if (!$con) {
            return self::getDefaultRegions();
        }

        $regionCol = $this->getRegionColumn();
        $regions = [];

        $res = @$con->query("SELECT DISTINCT `{$regionCol}` AS brand_name FROM `codes` WHERE `{$regionCol}` IS NOT NULL AND TRIM(`{$regionCol}`) != '' ORDER BY `{$regionCol}` ASC");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $name = trim($row['brand_name']);
                if (!empty($name)) {
                    $regions[] = $name;
                }
            }
        }

        return !empty($regions) ? $regions : self::getDefaultRegions();
    }

    /**
     * List of supported Plascon campaign regions (queries database dynamically)
     */
    public static function getAvailableRegions(): array
    {
        $instance = new self();
        return $instance->fetchAvailableRegions();
    }

    /**
     * Default fallback regions if table is empty or inaccessible
     */
    public static function getDefaultRegions(): array
    {
        return [
            'Kampala',
            'Jinja',
            'Mbarara',
            'Mbale',
            'Gulu',
            'Arua',
            'Fort Portal',
            'Masaka',
            'Hoima',
            'Lira',
            'Iganga',
            'Masindi',
            'Soroti'
        ];
    }

    /**
     * Curated test phone numbers
     */
    public function getTestNumbers(): string
    {
        $testNumbers = [
            "256788106120",
            "256700402006",
            "256772860277",
            "256774276726",
            "256781477172",
            "256787035753",
            "256784310451",
            "256782835736",
            "256777025670",
            "256757871664",
            "256774471028",
            "256785623138",
            "256782564613",
            "256782722142",
            "256775354046",
            "256782644814",
            "256782854231",
            "256782378185",
            "256750072626",
            "256701005383",
            "256782356234",
            "256775561306",
            "256772468773",
            "256700825258",
            "256772878780",
            "256700806873",
            "256776320775",
            "256781138043",
            "256772120413",
            "256774344016",
            "256772688234",
            "256702683355",
            "256778317767",
            "256774081368",
            "256788735411",
            "256783283114",
            "256777807661",
            "256783687487",
            "256773478235",
            "256776240376",
            "256782425014",
            "256702830645",
            "256702627446",
            "256758518358",
            "256784116045",
            "256774854635",
            "256774004311",
            "256703751844",
            "256778877105",
            "256754168573",
            "256784574245",
            "256782265268",
            "256772637820",
            "256705811524",
            "256783552777",
            "256774414524",
            "256782580541",
            "256782451322",
            "256782887364",
            "256774823581",
            "256782647174",
            "256782017676",
            "256773728050",
            "256756128184",
            "256784860717",
            "256705270652",
            "256705477301",
            "256772667103",
            "256706200003",
            "256782835330",
            "256783288882",
            "256772616488",
            "256702667001",
            "256772382077",
            "256772510362",
            "256772630684",
            "256777322214",
            "256702475137",
            "256704421561",
            "256773046173",
            "256783441080",
            "256772644716",
            "256752460714",
            "256772555057",
            "256774308286"
        ];
        return implode(',', $testNumbers);
    }

    public function __destruct()
    {
        if ($this->connection) {
            @$this->connection->close();
        }
    }
}
