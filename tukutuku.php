<?php
/**
 * Tukutuku Winners Draw Page
 * Draws winners strictly from the explicitly provided phone numbers list below
 * instead of querying the database `codes` table.
 *
 * You can either:
 * 1. Provide/paste the numbers directly in the $tukutukuNumbers array below, OR
 * 2. Click the "📝 NUMBERS" button on the page to paste or upload (.csv / .txt) numbers in the browser.
 */

$tukutukuNumbers = [
    "256753877181",
    "256702744655",
    "256742188928",
    "256785307974",
    "256776802093",
    "256778311498",
    "256783537850",
    "256777400163",
    "256750414911",
    "256770985073",
    "256787979528",
    "256781476322",
    "256791376362",
    "256791830773",
    "256782122194",
    "256783364514",
    "256778700327",
    "256784267941",
    "256755336511",
    "256787583806"
];

// Draw settings for Tukutuku page
$explicitDrawMode = true;
$drawHeadline = 'TUKUTUKU WINNERS DRAW';
$drawPrize = 'Tukutuku Winner';
$drawFileUsed = 'tukutuku.php';

$_GET['region'] = $_GET['region'] ?? 'All Regions';
$_GET['type'] = 'tukutuku';
$_GET['count'] = $_GET['count'] ?? 1;

require __DIR__ . '/index.php';
