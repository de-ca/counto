<?php
/**
 * AnalyticsQueries - Analytics-specific query methods extracted from the legacy Database.
 *
 * Depends on a QueryBuilder instance (injected).
 *
 * @package Counto\Core\Database
 * @copyright  2026 Counto Analytics
 * @version 1.4.1
 * @license    GPL-3.0-or-later
 */

declare(strict_types=1);

namespace Counto\Core\Database;

use DateTime;

class AnalyticsQueries
{
    /** @var QueryBuilder Query builder for database operations */
    private QueryBuilder $qb;

    // =========================================================================
    // CONSTRUCTOR (Dependency Injection)
    // =========================================================================

    /**
     * @param QueryBuilder $qb Query builder instance (injected)
     */
    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
    }

    // =========================================================================
    // DAILY STATISTICS
    // =========================================================================

    /**
     * Get daily statistics for a specific date.
     *
     * @param string $date Date in Y-m-d format
     * @return array|null
     */
    public function getDailyStats(string $date): ?array
    {
        // Query the raw visits table directly — the v_daily_summary view
        // may not exist in databases created from the inline schema fallback.
        $row = $this->qb->queryOne(
            'SELECT DATE(timestamp) as date,
                    COUNT(DISTINCT visitor_id) as visitors,
                    COUNT(DISTINCT visitor_id) as unique_visitors,
                    COUNT(*) as pageviews,
                    SUM(CASE WHEN is_bounce = 1 THEN 1 ELSE 0 END) as bounces,
                    COALESCE(AVG(load_time), 0) as avg_load_time
             FROM visits
             WHERE DATE(timestamp) = :date
             GROUP BY DATE(timestamp)',
            [':date' => $date]
        );

        return $row;
    }

    /**
     * Get daily stats for a date range.
     *
     * @param string $from Start date Y-m-d
     * @param string $to   End date Y-m-d
     * @return array
     */
    public function getDailyStatsRange(string $from, string $to): array
    {
        // Query the raw visits table directly — the v_daily_summary view
        // may not exist in databases created from the inline schema fallback.
        return $this->qb->query(
            'SELECT DATE(timestamp) as date,
                    COUNT(DISTINCT visitor_id) as visitors,
                    COUNT(DISTINCT visitor_id) as unique_visitors,
                    COUNT(*) as pageviews,
                    SUM(CASE WHEN is_bounce = 1 THEN 1 ELSE 0 END) as bounces,
                    COALESCE(AVG(load_time), 0) as avg_load_time
             FROM visits
             WHERE DATE(timestamp) >= :from AND DATE(timestamp) <= :to
             GROUP BY DATE(timestamp)
             ORDER BY date ASC',
            [':from' => $from, ':to' => $to]
        );
    }

    // =========================================================================
    // REALTIME & RECENT
    // =========================================================================

    /**
     * Get the real-time visitor count (last N minutes).
     *
     * @param int $minutes Window in minutes (default 5)
     * @return int
     */
    public function getRealtimeVisitors(int $minutes = 5): int
    {
        return (int)$this->qb->queryScalar(
            'SELECT COUNT(DISTINCT visitor_id) as count FROM visits WHERE timestamp > datetime(\'now\', :offset)',
            [':offset' => "-{$minutes} minutes"],
            0
        );
    }

    /**
     * Get top pages by total views.
     *
     * @param int $limit Maximum pages to return
     * @param int $days  Lookback period in days (0 = all time)
     * @return array
     */
    public function getTopPages(int $limit = 10, int $days = 7): array
    {
        if ($days > 0) {
            return $this->qb->query(
                'SELECT page_url, COUNT(*) as total_views, COUNT(DISTINCT visitor_id) as unique_views
                 FROM visits
                 WHERE timestamp >= datetime(\'now\', :offset)
                 GROUP BY page_url
                 ORDER BY total_views DESC
                 LIMIT :limit',
                [':offset' => "-{$days} days", ':limit' => $limit]
            );
        }

        return $this->qb->query(
            'SELECT page_url, total_views, unique_views, bounce_rate, last_viewed
             FROM v_top_pages
             LIMIT :limit',
            [':limit' => $limit]
        );
    }

    /**
     * Get top referrer domains.
     *
     * @param int $limit
     * @return array
     */
    public function getTopReferrers(int $limit = 10): array
    {
        return $this->qb->query(
            'SELECT referrer_domain, visits, last_referral
             FROM referrer_stats
             ORDER BY visits DESC
             LIMIT :limit',
            [':limit' => $limit]
        );
    }

    // =========================================================================
    // DISTRIBUTION (BROWSER / OS / DEVICE)
    // =========================================================================

    /**
     * Get browser distribution (counts by browser type).
     *
     * @param int $days Lookback period
     * @return array
     */
    public function getBrowserDistribution(int $days = 30): array
    {
        if ($days > 0) {
            return $this->qb->query(
                'SELECT browser, COUNT(*) as count
                 FROM visitors v
                 JOIN visits vs ON v.id = vs.visitor_id
                 WHERE vs.timestamp >= datetime(\'now\', :offset) AND v.is_bot = 0
                 GROUP BY browser
                 ORDER BY count DESC',
                [':offset' => "-{$days} days"]
            );
        }

        return $this->qb->query(
            'SELECT browser, COUNT(*) as count
             FROM visitors
             WHERE is_bot = 0 AND browser IS NOT NULL
             GROUP BY browser
             ORDER BY count DESC'
        );
    }

    /**
     * Get operating system distribution.
     *
     * @param int $days Lookback period
     * @return array
     */
    public function getOSDistribution(int $days = 30): array
    {
        if ($days > 0) {
            return $this->qb->query(
                'SELECT os, COUNT(*) as count
                 FROM visitors v
                 JOIN visits vs ON v.id = vs.visitor_id
                 WHERE vs.timestamp >= datetime(\'now\', :offset) AND v.is_bot = 0
                 GROUP BY os
                 ORDER BY count DESC',
                [':offset' => "-{$days} days"]
            );
        }

        return $this->qb->query(
            'SELECT os, COUNT(*) as count
             FROM visitors
             WHERE is_bot = 0 AND os IS NOT NULL
             GROUP BY os
             ORDER BY count DESC'
        );
    }

    /**
     * Get countoy distribution based on visitor countoy_code.
     *
     * @param int $days Lookback period
     * @return array
     */
    public function getCountoyDistribution(int $days = 30): array
    {
        if ($days > 0) {
            return $this->qb->query(
                'SELECT COALESCE(v.countoy_code, \'\') as countoy_code, COUNT(DISTINCT v.id) as count
                 FROM visitors v
                 JOIN visits vs ON v.id = vs.visitor_id
                 WHERE vs.timestamp >= datetime(\'now\', :offset) AND v.is_bot = 0
                 GROUP BY v.countoy_code
                 ORDER BY count DESC',
                [':offset' => "-{$days} days"]
            );
        }

        return $this->qb->query(
            'SELECT COALESCE(countoy_code, \'\') as countoy_code, COUNT(*) as count
             FROM visitors
             WHERE is_bot = 0
             GROUP BY countoy_code
             ORDER BY count DESC'
        );
    }

    /**
     * Get device type distribution (desktop/mobile/tablet).
     *
     * @param int $days Lookback period
     * @return array
     */
    public function getDeviceDistribution(int $days = 30): array
    {
        if ($days > 0) {
            return $this->qb->query(
                'SELECT device_type, COUNT(*) as count
                 FROM visitors v
                 JOIN visits vs ON v.id = vs.visitor_id
                 WHERE vs.timestamp >= datetime(\'now\', :offset) AND v.is_bot = 0
                 GROUP BY device_type
                 ORDER BY count DESC',
                [':offset' => "-{$days} days"]
            );
        }

        return $this->qb->query(
            'SELECT device_type, COUNT(*) as count
             FROM visitors
             WHERE is_bot = 0 AND device_type IS NOT NULL
             GROUP BY device_type
             ORDER BY count DESC'
        );
    }

    // =========================================================================
    // AGGREGATE METRICS
    // =========================================================================

    /**
     * Get the average session duration for a date range.
     *
     * @param string $from Start date Y-m-d
     * @param string $to   End date Y-m-d
     * @return int         Average duration in seconds
     */
    public function getAverageDuration(string $from, string $to): int
    {
        return (int)$this->qb->queryScalar(
            'SELECT COALESCE(AVG(duration), 0)
             FROM (
                 SELECT visitor_id, session_id,
                        MAX(strftime(\'%s\', timestamp)) - MIN(strftime(\'%s\', timestamp)) as duration
                 FROM visits
                 WHERE DATE(timestamp) >= :from AND DATE(timestamp) <= :to
                 GROUP BY visitor_id, session_id
                 HAVING COUNT(*) > 1
             )',
            [':from' => $from, ':to' => $to],
            0
        );
    }

    /**
     * Get hourly distribution for a given date.
     *
     * Queries the raw visits table because the hourly_stats aggregation
     * table may not have accurate visitor counts (triggers only update
     * pageviews, not visitors).
     *
     * @param string $date Date in Y-m-d format
     * @return array
     */
    public function getHourlyDistribution(string $date): array
    {
        // Try hourly_stats first (faster if populated)
        $rows = $this->qb->query(
            'SELECT hs.hour, COALESCE(hs.visitors, 0) as visitors,
                    COALESCE(hs.pageviews, 0) as pageviews
             FROM hourly_stats hs
             WHERE hs.date = :date
             ORDER BY hs.hour ASC',
            [':date' => $date]
        );

        // If hourly_stats has real visitor data, return it
        $hasVisitors = false;
        foreach ($rows as $row) {
            if ((int)($row['visitors'] ?? 0) > 0) {
                $hasVisitors = true;
                break;
            }
        }
        if ($hasVisitors && !empty($rows)) {
            return $rows;
        }

        // Fallback: compute from raw visits table for accurate data
        return $this->qb->query(
            "SELECT CAST(strftime('%H', timestamp) AS INTEGER) as hour,
                    COUNT(DISTINCT visitor_id) as visitors,
                    COUNT(*) as pageviews
             FROM visits
             WHERE DATE(timestamp) = :date
             GROUP BY CAST(strftime('%H', timestamp) AS INTEGER)
             ORDER BY hour ASC",
            [':date' => $date]
        );
    }

    /**
     * Get today's summary statistics.
     *
     * @return array
     */
    public function getTodaySummary(): array
    {
        // Query the raw visits table directly — the v_today_stats view
        // may not exist in databases created from the inline schema fallback.
        $today = date('Y-m-d');
        $row = $this->qb->queryOne(
            'SELECT DATE(timestamp) as date,
                    COUNT(DISTINCT visitor_id) as visitors_today,
                    COUNT(DISTINCT CASE WHEN v.is_bot = 0 THEN vs.visitor_id END) as human_visitors,
                    COUNT(*) as pageviews_today,
                    COALESCE(AVG(load_time), 0) as avg_load_time,
                    SUM(CASE WHEN is_bounce = 1 THEN 1 ELSE 0 END) as bounces_today
             FROM visits vs
             LEFT JOIN visitors v ON v.id = vs.visitor_id
             WHERE DATE(timestamp) = :today
             GROUP BY DATE(timestamp)',
            [':today' => $today]
        );
        $realtime = $this->getRealtimeVisitors(5);

        return [
            'date'            => $today,
            'visitors_today'  => (int)($row['visitors_today'] ?? 0),
            'human_visitors'  => (int)($row['human_visitors'] ?? 0),
            'pageviews_today' => (int)($row['pageviews_today'] ?? 0),
            'avg_load_time'   => round((float)($row['avg_load_time'] ?? 0), 2),
            'bounces_today'   => (int)($row['bounces_today'] ?? 0),
            'realtime_online' => $realtime,
        ];
    }

    /**
     * Get overall statistics (all-time totals).
     *
     * @param callable $sizeCallback Callback to get database size string
     * @return array
     */
    public function getOverallStats(callable $sizeCallback): array
    {
        return [
            'total_visitors'      => (int)$this->qb->queryScalar('SELECT COUNT(*) FROM visitors WHERE is_bot = 0', [], 0),
            'total_pageviews'     => (int)$this->qb->queryScalar('SELECT COUNT(*) FROM visits', [], 0),
            'total_bots'          => (int)$this->qb->queryScalar('SELECT COUNT(*) FROM visitors WHERE is_bot = 1', [], 0),
            'total_pages_tracked' => (int)$this->qb->queryScalar('SELECT COUNT(*) FROM page_stats', [], 0),
            'database_size'       => $sizeCallback(),
            'first_tracked'      => $this->qb->queryScalar('SELECT MIN(timestamp) FROM visits', [], null),
            'last_tracked'       => $this->qb->queryScalar('SELECT MAX(timestamp) FROM visits', [], null),
        ];
    }

    /**
     * Get the number of pageviews for the last N days (for charts).
     *
     * Uses the raw visits table directly because the daily_stats aggregation
     * table's visitor/unique_visitor columns are not reliably updated by triggers
     * (triggers only update pageviews/bounces). The v_daily_summary view is
     * tried first; if it returns meaningful visitor data we use it, otherwise
     * we fall back to aggregating from the raw visits table.
     *
     * @param int $days
     * @return array
     */
    public function getLastNDays(int $days = 30): array
    {
        $dateFrom = date('Y-m-d', strtotime("-{$days} days"));
        $dateTo = date('Y-m-d');

        // Try the pre-computed view/daily_stats first
        $stats = $this->getDailyStatsRange($dateFrom, $dateTo);
        $statsByDate = [];
        $totalVisitorsFromPrecomputed = 0;
        foreach ($stats as $row) {
            $statsByDate[$row['date']] = $row;
            $totalVisitorsFromPrecomputed += (int)($row['visitors'] ?? 0);
        }

        // If pre-computed stats have real visitor data, use them
        if ($totalVisitorsFromPrecomputed > 0) {
            return $this->fillDateRange($dateFrom, $dateTo, $statsByDate, [
                'visitors'        => 'visitors',
                'pageviews'       => 'pageviews',
                'unique_visitors' => 'unique_visitors',
            ]);
        }

        // Fallback: aggregate directly from the raw visits table
        $rawRows = $this->qb->query(
            'SELECT DATE(timestamp) as date,
                    COUNT(DISTINCT visitor_id) as visitors,
                    COUNT(*) as pageviews,
                    COUNT(DISTINCT visitor_id) as unique_visitors
             FROM visits
             WHERE DATE(timestamp) >= :from AND DATE(timestamp) <= :to
             GROUP BY DATE(timestamp)
             ORDER BY date ASC',
            [':from' => $dateFrom, ':to' => $dateTo]
        );

        $rawByDate = [];
        foreach ($rawRows as $row) {
            $rawByDate[$row['date']] = $row;
        }

        return $this->fillDateRange($dateFrom, $dateTo, $rawByDate, [
            'visitors'        => 'visitors',
            'pageviews'       => 'pageviews',
            'unique_visitors' => 'unique_visitors',
        ]);
    }

    /**
     * Fill all dates in a range, using pre-fetched data for existing dates
     * and zeroes for missing dates.
     *
     * @param string $dateFrom    Start date (Y-m-d)
     * @param string $dateTo      End date (Y-m-d)
     * @param array  $dataByDate  Associative array date => row
     * @param array  $fieldMap    Map of output key => column name in row
     * @return array
     */
    private function fillDateRange(string $dateFrom, string $dateTo, array $dataByDate, array $fieldMap): array
    {
        $result = [];
        $current = new DateTime($dateFrom);
        $end = new DateTime($dateTo);

        while ($current <= $end) {
            $date = $current->format('Y-m-d');
            $entry = ['date' => $date];

            if (isset($dataByDate[$date])) {
                $row = $dataByDate[$date];
                foreach ($fieldMap as $outKey => $col) {
                    $entry[$outKey] = (int)($row[$col] ?? 0);
                }
            } else {
                foreach ($fieldMap as $outKey => $col) {
                    $entry[$outKey] = 0;
                }
            }

            $result[] = $entry;
            $current->modify('+1 day');
        }

        return $result;
    }
}