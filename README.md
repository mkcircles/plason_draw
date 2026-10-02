# Kansai Plascon • Paint & Win Lucky Draw Application

An interactive, broadcast-ready digital raffle and lucky draw system designed for **Kansai Plascon Uganda's** promotional campaigns (such as the *Paint & Win* promotion).

Built with a unified PHP backend and modern vanilla JavaScript frontend featuring a 3D mechanical odometer animation, Web Audio synthesis, confetti celebration, broadcast privacy protection, and automated winner tracking.

---

## 🌟 Key Features

- **🎰 3D Mechanical Reel / Odometer Animation**:
  - High-performance, multi-digit mechanical spinning reel with realistic deceleration, staggered column stopping, and suspense buildup.
- **🔊 Zero-Dependency Web Audio Synthesizer**:
  - Synthesizes mechanical clicking, suspense risers, and victory fanfare directly using the browser's Web Audio API without requiring external audio files.
- **🎉 Particle Confetti Celebration**:
  - Canvas-based confetti burst on winner selection with realistic gravity and wind physics.
- **🔒 Broadcast Privacy Mode**:
  - Mask intermediate digits (e.g., `0772 ••• 413`) for on-air live TV compliance and participant data protection.
- **📍 Dynamic Regional & National Draws**:
  - Fully supports **All Regions (National)** or area-specific draws: *Kampala, Jinja, Mbarara, Mbale, Gulu, Arua, Fort Portal, Masaka, Hoima, Lira, Iganga, Masindi, Soroti*, and any dynamically registered campaign regions in the database.
- **🎯 Draw Categories & Multi-Winner Targets**:
  - Configure draws by category: **Daily**, **Weekly**, **Regional**, and **Grand Draw**.
  - Target winner counters (1, 2, 5, 8, 10 winners) with consecutive draw support.
- **🛡️ Strict Duplicate Prevention & Winner Exclusion**:
  - Automatically queries and excludes entries already present in `past_winners` and numbers already drawn in `drawn_numbers`.
- **📜 Live Winners Drawer & CSV Export**:
  - Slide-out history panel displaying drawn numbers with timestamps and regions.
  - One-click UTF-8 BOM CSV export compatible with Microsoft Excel and Google Sheets.
- **⌨️ Presenter Keyboard Shortcuts**:
  - Optimized for TV studio hosts and live event presenters.

---

## ⌨️ Presenter Keyboard Shortcuts

| Shortcut | Action | Description |
| :--- | :--- | :--- |
| <kbd>Space</kbd> / <kbd>Enter</kbd> | **Start / Stop Spin** | Triggers the reels or stops them on the selected winner |
| <kbd>N</kbd> | **Next Draw** | Resets reels and loads pool for next draw |
| <kbd>F</kbd> | **Fullscreen** | Toggles full-screen presentation mode |
| <kbd>M</kbd> | **Mute / Unmute** | Toggles synthesized sound effects |
| <kbd>H</kbd> | **History Drawer** | Toggles the drawn winners list |
| <kbd>Esc</kbd> | **Close Overlay** | Closes active winner modal or history drawer |

---

## 📁 Project Architecture

```
plascon_draw/
├── api/
│   ├── draw.php            # Endpoint: Draw and atomically commit winner
│   ├── pool.php            # Endpoint: Retrieve eligible phone pool for reel animation
│   ├── history.php         # Endpoint: Fetch list of recorded winners
│   └── export.php          # Endpoint: Download winners as CSV spreadsheet
├── css/
│   └── plascon-draw.css    # Responsive broadcast styling, glassmorphism, 3D reels
├── js/
│   └── plascon-draw.js     # SoundEngine, ReelController, Confetti, and State logic
├── img/                    # Campaign branding graphics and backgrounds
├── config.php              # Database engine, exclusion rules, and helper methods
├── index.php               # Unified broadcast interface and presenter HUD
├── schema.sql              # Database table definition for drawn_numbers
├── README.md               # Documentation
│
└── Regional Presets (Convenience Launchers):
    ├── daily.php           # Daily draw launcher preset
    ├── draw.php            # National draw launcher preset
    ├── kampala.php         # Kampala regional preset
    ├── jinja.php           # Jinja regional preset
    ├── mbarara.php         # Mbarara regional preset
    ├── mbale.php           # Mbale regional preset
    ├── gulu.php            # Gulu regional preset
    ├── arua.php            # Arua regional preset
    ├── fort.php            # Fort Portal regional preset
    ├── masaka.php          # Masaka regional preset
    ├── hoima.php           # Hoima regional preset
    ├── lira.php            # Lira regional preset
    ├── iganga.php          # Iganga regional preset
    ├── masindi.php         # Masindi regional preset
    └── soroti.php          # Soroti regional preset
```

---

## 🗄️ Database Setup & Schema

The application connects to a MySQL/MariaDB database containing consumer participation records.

### 1. `drawn_numbers` Table

Automatically created by [`config.php`](file:///Volumes/Dev/Sites/plascon_draw/config.php) if not present, or run manually from [`schema.sql`](file:///Volumes/Dev/Sites/plascon_draw/schema.sql):

```sql
CREATE TABLE IF NOT EXISTS `drawn_numbers` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Campaign Tables Expected

- **`codes`**: Contains incoming participant SMS/entries.
  - `inMessageId` *(VARCHAR)*: The participant's phone number (MSISDN).
  - `brand` or `area` *(VARCHAR)*: Region / Location name (e.g. `Kampala`, `Jinja`, `Mbarara`).
- **`past_winners`** *(Optional but recommended)*:
  - `msisdn` *(VARCHAR)*: Numbers excluded from winning again.

---

## ⚙️ Environment & Configuration

Database credentials can be configured via environment variables or default settings in [`config.php`](file:///Volumes/Dev/Sites/plascon_draw/config.php):

| Environment Variable | Default | Description |
| :--- | :--- | :--- |
| `DB_HOST` | `127.0.0.1` | Database server hostname / IP |
| `DB_USER` | `root` | Database user |
| `DB_PASS` | `""` | Database password |
| `DB_NAME` | `plascon` | Database name |

---

## 🚀 Getting Started

### Prerequisites

- **PHP 7.4+** or **PHP 8.x** with `mysqli` extension enabled.
- **MySQL 5.7+** or **MariaDB 10.3+**.
- Modern web browser (Chrome, Edge, Firefox, Safari) with Web Audio and Canvas support.

### Running with PHP Built-in Server

For local development or testing:

```bash
cd /Volumes/Dev/Sites/plascon_draw

# Export environment variables if different from defaults
export DB_HOST="127.0.0.1"
export DB_USER="root"
export DB_PASS=""
export DB_NAME="plascon"

# Start PHP development server
php -S 127.0.0.1:8080
```

Open your browser and navigate to:
- **Unified Main Draw**: [http://127.0.0.1:8080/](http://127.0.0.1:8080/)
- **Regional Preset (e.g., Kampala)**: [http://127.0.0.1:8080/kampala.php](http://127.0.0.1:8080/kampala.php)
- **Daily Draw Preset**: [http://127.0.0.1:8080/daily.php](http://127.0.0.1:8080/daily.php)

---

## 🔌 API Endpoints

### 1. `GET /api/pool.php`
Fetches a list of randomized, eligible phone numbers for the animation reel.
- **Query Parameters**:
  - `region` *(string, optional)*: Filter by region (e.g., `Kampala`). If omitted or set to `All Regions`, returns national pool.
  - `limit` *(int, default: 300)*: Maximum numbers to return.

### 2. `POST /api/draw.php`
Draws, verifies, and records an official winner.
- **Payload / Parameters**:
  - `region` *(string)*: Target region.
  - `draw_type` *(string)*: `daily`, `weekly`, `regional`, or `grand`.
  - `prize` *(string, optional)*: Prize name.
- **Response**:
  ```json
  {
    "status": "success",
    "winner": {
      "msisdn": "256782356234",
      "formatted": "0782 356 234",
      "masked": "0782 ••• 234",
      "region": "Kampala",
      "draw_type": "regional",
      "prize": "Paint and Win Prize",
      "drawn_at": "2026-10-02 11:30:00"
    }
  }
  ```

### 3. `GET /api/history.php`
Returns recently drawn winners.
- **Query Parameters**:
  - `region` *(string, optional)*: Filter by region.
  - `limit` *(int, default: 100)*: Limit results.

### 4. `GET /api/export.php`
Downloads a CSV spreadsheet of drawn winners with UTF-8 BOM encoding for direct opening in Microsoft Excel.
- **Query Parameters**:
  - `region` *(string, optional)*: Export for a specific region or all.

---

## 🛡️ License & Trademarks

- Developed for the **Kansai Plascon Uganda** promotional campaign.
- All brand logos and trademarks are the property of Kansai Plascon.
