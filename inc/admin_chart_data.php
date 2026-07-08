<?php
/**
 * Counto Analytics – Admin Chart Data Preparation
 *
 * Transforms raw DB query results into chart-ready arrays for the admin panel.
 * Computes: browser/OS/device distribution, pages referrers data,
 *           and the $adminChartData array for JavaScript consumption.
 *
 * Included from admin.php after data fetching.
 *
 * @package    Counto
 * @copyright  2026 Counto Analytics
 * @license    GPL-3.0-or-later
 * @version 1.4.0
 *
 * @global array $browsers      Browser distribution from Tracker::getBrowserDistribution()
 * @global array $osDist        OS distribution from Tracker::getOSDistribution()
 * @global array $devices       Device distribution from Tracker::getDeviceDistribution()
 * @global array $topPages      Top pages from Tracker::getTopPages()
 * @global array $topReferrers  Top referrers from Tracker::getTopReferrers()
 *
 * @var array $browserLabels  (output) Browser chart labels
 * @var array $browserValues  (output) Browser chart values
 * @var array $osLabels       (output) OS chart labels
 * @var array $osValues       (output) OS chart values
 * @var array $deviceLabels   (output) Device chart labels
 * @var array $deviceValues   (output) Device chart values
 * @var array $pagesData      (output) Pages table data
 * @var array $pagesLabels    (output) Pages chart labels
 * @var array $pagesValues    (output) Pages chart values
 * @var array $refData        (output) Referrers table data
 * @var array $refLabels      (output) Referrers chart labels
 * @var array $refValues      (output) Referrers chart values
 * @var array $adminChartData (output) Combined JSON-ready chart data
 */

declare(strict_types=1);

// =========================================================================
// PREPARE CHART DATA (for Visitors tab)
// =========================================================================
// Browser distribution
$browserLabels = [];
$browserValues = [];
$browserRows = [];
foreach ($browsers as $row) {
    $browserRows[$row['browser'] ?? 'Unknown'] = (int)($row['count'] ?? 0);
}
arsort($browserRows);
foreach (array_slice($browserRows, 0, 10) as $b => $c) {
    $browserLabels[] = $b;
    $browserValues[] = $c;
}

// OS distribution
$osLabels = [];
$osValues = [];
$osRows = [];
foreach ($osDist as $row) {
    $osRows[$row['os'] ?? 'Unknown'] = (int)($row['count'] ?? 0);
}
arsort($osRows);
foreach (array_slice($osRows, 0, 8) as $o => $c) {
    $osLabels[] = $o;
    $osValues[] = $c;
}

// Device distribution
$deviceLabels = [];
$deviceValues = [];
$deviceRows = [];
foreach ($devices as $row) {
    $deviceRows[$row['device_type'] ?? 'Unknown'] = (int)($row['count'] ?? 0);
}
arsort($deviceRows);
foreach ($deviceRows as $d => $c) {
    $deviceLabels[] = ucfirst($d);
    $deviceValues[] = $c;
}

// Pages data
$pagesLabels = [];
$pagesValues = [];
$pagesData = [];
$rank = 1;
foreach ($topPages as $idx => $page) {
    if (is_array($page)) {
        $url = $page['page_url'] ?? (array_key_first($page) ?? '');
        $count = (int)($page['total_views'] ?? (array_values($page)[0] ?? 0));
    } else {
        $url = (string)$idx;
        $count = (int)$page;
    }
    if ($url === '') continue;
    $pagesLabels[] = $url;
    $pagesValues[] = $count;
    $pagesData[] = ['url' => $url, 'count' => $count, 'rank' => $rank++];
}

// Referrers data
$refLabels = [];
$refValues = [];
$refData = [];
$refRank = 1;
foreach ($topReferrers as $refEntry) {
    $refLabel = is_array($refEntry)
        ? ($refEntry['referrer_domain'] ?? array_key_first($refEntry) ?? 'Direct')
        : (string)$refEntry;
    $refCount = is_array($refEntry)
        ? (int)($refEntry['visits'] ?? (array_values($refEntry)[0] ?? 0))
        : 0;
    if ($refLabel === '') $refLabel = 'Direct';
    $refLabels[] = $refLabel;
    $refValues[] = $refCount;
    $refData[] = ['label' => $refLabel, 'count' => $refCount, 'rank' => $refRank++];
}

// Build JSON chart data for initial render
$adminChartData = [
    'browsers' => ['labels' => $browserLabels, 'values' => $browserValues],
    'os' => ['labels' => $osLabels, 'values' => $osValues],
    'devices' => ['labels' => $deviceLabels, 'values' => $deviceValues],
    'pages' => ['labels' => $pagesLabels, 'values' => $pagesValues],
    'referrers' => ['labels' => $refLabels, 'values' => $refValues],
];