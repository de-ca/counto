<?php
/**
 * counto – Internationalization (i18n) Module
 *
 * Detects the user's preferred language via a strict priority hierarchy:
 *  1. ?lang= query parameter (explicit manual override) – stored in session if available
 *  2. HTTP_ACCEPT_LANGUAGE browser header (first two chars)
 *  3. Cloudflare HTTP_CF_IPcountoY header (Geo-IP fallback)
 *  4. Default: en (English)
 *
 * All translations are embedded inline (no external lang/ directory).
 *
 * @package    Counto
 * @copyright  2026 Counto Analytics
 * @license    GPL-3.0-or-later
 * @version 1.4.0
 */

declare(strict_types=1);

// Supported language codes
define('SUPPORTED_LANGS', ['de', 'en']);

// =========================================================================
// LANGUAGE DETECTION – Strict Priority Hierarchy
// =========================================================================

$lang = 'en'; // Priority 4: System default

// --- Priority 1: Manual override via ?lang= query parameter ---
if (!empty($_GET['lang']) && in_array($_GET['lang'], SUPPORTED_LANGS, true)) {
    $lang = $_GET['lang'];
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['lang'] = $lang;
}
// --- Priority 2: Standard browser Accept-Language header ---
elseif (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if ($browserLang === 'de') {
        $lang = 'de';
    } elseif ($browserLang === 'en') {
        $lang = 'en';
    }
}
// --- Priority 3: Cloudflare Geo-IP header (optional fallback) ---
elseif (!empty($_SERVER['HTTP_CF_IPcountoY'])) {
    $cc = strtoupper($_SERVER['HTTP_CF_IPcountoY']);
    if (in_array($cc, ['DE', 'AT', 'CH'], true)) {
        $lang = 'de';
    } else {
        $lang = 'en';
    }
}

// Final safety net
if (!in_array($lang, SUPPORTED_LANGS, true)) {
    $lang = 'en';
}

// =========================================================================
// INLINE TRANSLATIONS (no external lang/ directory)
// =========================================================================

$t = [];

// --- English ---
$translations['en'] = [
    // Charts
    'chart.visitors'  => 'Visitors',
    'chart.pageviews' => 'Pageviews',

    // Dashboard
    'dash.title'              => 'Statistics',
    'dash.admin'              => 'Admin',
    'dash.refresh'            => 'Refresh data',
    'dash.dark'               => 'Dark mode',
    'dash.light'              => 'Light mode',
    'dash.now_online'         => 'Now online',
    'dash.visitors_today'     => 'Visitors today',
    'dash.pageviews_today'    => 'Pageviews today',
    'dash.total_visitors'     => 'Total visitors',
    'dash.bounce_rate'        => 'Bounce rate',
    'dash.avg_time'           => 'Avg. time',
    'dash.auto_refresh'       => 'Auto-refreshes every 60 seconds',
    'dash.updated'            => 'Updated',
    'dash.footer'             => 'Powered by Counto Analytics',
    'dash.no_data'            => 'No data available yet',
    'dash.error_fetch'        => 'Error fetching data',
    'dash.not_available'      => 'Dashboard not available',
    'dash.not_available_desc' => 'The dashboard is currently not available. Please try again later.',
    'dash.install_required'   => 'Installation required',
    'dash.trend_7_days'       => 'Trend last 7 days',
    'dash.hourly_dist'        => 'Hourly distribution',
    'dash.longterm_trend'     => 'Long-term trend',
    'dash.browser_dist'       => 'Browser distribution',
    'dash.countoy_dist'       => 'Country distribution',
    'dash.top_pages'          => 'Top pages',
    'dash.page_overview'      => 'Page overview',

    // Admin – General
    'admin.not_installed'              => 'Not installed',
    'admin.not_installed_desc'         => 'Counto Analytics is not installed yet. Please run the setup wizard first.',
    'admin.csrf_error'                 => 'Security token invalid. Please reload the page.',
    'admin.login_error'                => 'Incorrect password. Please try again.',
    'admin.login_title'                => 'Counto Admin – Login',
    'admin.login_heading'              => 'Admin Login',
    'admin.login_subtitle'             => 'Log in with your admin password.',
    'admin.login_password_placeholder' => 'Admin password',
    'admin.login_button'               => 'Login',
    'admin.login_back'                 => 'Back to dashboard',
    'admin.lang_switch_en'             => 'Switch to English',
    'admin.lang_switch_de'             => 'Zu Deutsch wechseln',
    'admin.lang_switch_title_en'       => 'Switch to English',
    'admin.lang_switch_title_de'       => 'Zu Deutsch wechseln',
    'admin.theme_dark'                 => 'Dark mode',
    'admin.theme_light'                => 'Light mode',
    'admin.menu_toggle'                => 'Toggle menu',
    'admin.dashboard_btn'              => 'Dashboard',
    'admin.logout'                     => 'Logout',

    // Admin – Tabs
    'admin.tab_overview'  => 'Overview',
    'admin.tab_visitors'  => 'Visitors',
    'admin.tab_pages'     => 'Pages',
    'admin.tab_referrers' => 'Referrers',
    'admin.tab_settings'  => 'Settings',
    'admin.tab_export'    => 'Export',
    'admin.tab_tools'     => 'Tools',

    // Admin – Date Filter
    'admin.filter_from'    => 'From',
    'admin.filter_to'      => 'To',
    'admin.filter_apply'   => 'Apply',
    'admin.filter_7days'   => '7 days',
    'admin.filter_30days'  => '30 days',
    'admin.filter_90days'  => '90 days',

    // Admin – Overview
    'admin.overview_title'           => 'Overview',
    'admin.overview_online'          => 'Currently online',
    'admin.overview_visitors_today'  => 'Visitors today',
    'admin.overview_pageviews_today' => 'Pageviews today',
    'admin.overview_bounce_rate'     => 'Bounce rate',
    'admin.overview_total_visitors'  => 'Total visitors',
    'admin.overview_total_pageviews' => 'Total pageviews',
    'admin.peak_hour'                => 'Peak hour',
    'admin.growth_trend'             => 'Growth trend',
    'admin.growth_not_enough'        => 'Not enough data for trend analysis yet.',

    // Admin – Visitors
    'admin.visitors_title' => 'Visitors',
    'admin.browser_dist'   => 'Browser distribution',
    'admin.os_dist'        => 'OS distribution',
    'admin.device_dist'    => 'Device distribution',
    'admin.countoy_dist'   => 'Country distribution',

    // Admin – Pages
    'admin.pages_title'   => 'Pages',
    'admin.pages_no_data' => 'No page data available for the selected period.',

    // Admin – Referrers
    'admin.referrers_title'   => 'Referrers',
    'admin.referrers_no_data' => 'No referrer data available for the selected period.',

    // Admin – Settings
    'admin.settings_title'          => 'Settings',
    'admin.settings_site_name'      => 'Site name',
    'admin.settings_site_url'       => 'Site URL',
    'admin.settings_timezone'       => 'Timezone',
    'admin.settings_session_timeout'=> 'Session timeout (seconds)',
    'admin.settings_days_to_keep'   => 'Days to keep data',
    'admin.settings_ignore_bots'    => 'Ignore bots and crawlers',
    'admin.settings_public_stats'   => 'Enable public statistics',
    'admin.settings_disable_tracking'=> 'Disable tracking',
    'admin.settings_save'           => 'Save settings',
    'admin.settings_saved'          => 'Settings saved successfully.',
    'admin.settings_error_db'       => 'Cannot save settings: Database connection failed.',
    'admin.settings_error_save'     => 'Some settings could not be saved. Please check write permissions.',
    'admin.settings_error_save_full'=> 'No settings could be saved. Please check write permissions.',

    // Admin – Password
    'admin.password_title'     => 'Change password',
    'admin.password_current'   => 'Current password',
    'admin.password_new'       => 'New password',
    'admin.password_confirm'   => 'Confirm new password',
    'admin.password_button'    => 'Change password',
    'admin.password_wrong'     => 'Current password is incorrect.',
    'admin.password_too_short' => 'New password must be at least 6 characters.',
    'admin.password_mismatch'  => 'New passwords do not match.',
    'admin.password_changed'   => 'Password changed successfully.',

    // Admin – Export
    'admin.export_title'    => 'Export',
    'admin.export_desc'     => 'Export your analytics data in various formats.',
    'admin.export_csv'      => 'Export CSV',
    'admin.export_json'     => 'Export JSON',
    'admin.export_excel'    => 'Export Excel',
    'admin.export_range'    => 'Current date range',
    'admin.export_adjust'   => 'Adjust the date filter above to change the export range.',

    // Admin – API
    'admin.api_title'              => 'API Access',
    'admin.api_token'              => 'API Token',
    'admin.api_current'            => 'Current API token',
    'admin.api_no_token'           => 'No token generated yet',
    'admin.api_generate'           => 'Generate new token',
    'admin.api_generated'          => 'API token generated successfully.',
    'admin.export_token_title'     => 'Export Token',
    'admin.export_token_current'   => 'Current export token',
    'admin.export_token_generate'  => 'Generate new export token',
    'admin.export_token_generated' => 'Export token generated successfully.',

    // Admin – Tracking Tab
    'admin.tab_tracking'                => 'Tracking',
    'admin.tracking_title'              => 'Tracking Code Integration',
    'admin.tracking_intro'              => 'Integrate Counto Analytics into your website. Choose the method that best suits your setup.',
    'admin.tracking_option_a'           => 'Standard: Script Tag (Recommended)',
    'admin.tracking_option_a_desc'      => 'Place this script tag just before the closing </body> tag on every page you want to track.',
    'admin.tracking_option_a_tip'       => 'The <code>defer</code> attribute ensures the script loads without blocking page rendering. Counto automatically detects the current page URL.',
    'admin.tracking_option_b'           => 'Manual: <head> Placement',
    'admin.tracking_option_b_desc'      => 'Alternatively, you can place the tracking script in the <head> section. Note the following:',
    'admin.tracking_option_b_li1'       => 'Remove the <code>defer</code> attribute when placing in <head> – otherwise the script may not execute.',
    'admin.tracking_option_b_li2'       => 'The script is asynchronous and does not block page rendering.',
    'admin.tracking_option_b_li3'       => 'For optimal compatibility, the <body>-before-closing placement is preferred.',
    'admin.tracking_option_b_note'      => 'If placed in <head>, ensure your site uses <code>Content-Security-Policy</code> headers that allow external scripts from this domain.',
    'admin.tracking_option_c'           => 'CMS & Hosted Platforms',
    'admin.tracking_option_c_desc'      => 'Integration guides for popular content management systems and hosted platforms:',
    'admin.tracking_cms_wp'             => 'Insert the script tag via the theme\'s <code>footer.php</code> or use a "Header/Footer Scripts" plugin.',
    'admin.tracking_cms_other'          => 'Add the script tag in your theme\'s template file (e.g. <code>index.php</code>) before <code></body></code>.',
    'admin.tracking_cms_hosted'         => 'Use the built-in "Custom Code" or "Tracking Code" section in your site settings to paste the script tag.',
    'admin.tracking_cms_custom_title'   => 'Custom / Static Site',
    'admin.tracking_cms_custom'         => 'Add the script tag directly to your HTML template or use your static site generator\'s layout/partial system.',
    'admin.tracking_endpoint_title'     => 'Tracking Endpoint Reference',
    'admin.tracking_endpoint_url'       => 'Endpoint URL',
    'admin.tracking_endpoint_method'    => 'HTTP Method',
    'admin.tracking_endpoint_params'    => 'Parameters',
    'admin.tracking_endpoint_response'  => 'Response Formats',

    // Admin – Tools
    'admin.tools_cleanup_title'   => 'Data cleanup',
    'admin.tools_cleanup_label'   => 'Delete data older than (days)',
    'admin.tools_cleanup_confirm' => 'Are you sure you want to delete old data? This action cannot be undone.',
    'admin.tools_cleanup_button'  => 'Delete old data',
    'admin.tools_cleanup_done'    => 'entries deleted, older than',
    'admin.tools_cleanup_done_suffix' => 'days.',
    'admin.tools_backup_title'    => 'Backup',
    'admin.tools_backup_create'   => 'Create backup',
    'admin.tools_backup_button'   => 'Create backup now',
    'admin.tools_backup_existing' => 'Existing backups',
    'admin.tools_backup_none'     => 'No backups found.',
    'admin.tools_backup_success'  => 'Backup created:',
    'admin.tools_backup_error'    => 'Backup creation failed. Check write permissions.',
    'admin.tools_sysinfo_title'           => 'System information',
    'admin.tools_sysinfo_php'             => 'PHP version',
    'admin.tools_sysinfo_timezone'        => 'Timezone',
    'admin.tools_sysinfo_first'           => 'First tracked',
    'admin.tools_sysinfo_last'            => 'Last tracked',
    'admin.tools_sysinfo_tracking'        => 'Tracking active',
    'admin.tools_sysinfo_public'          => 'Public stats',
    'admin.tools_sysinfo_db'              => 'Database path',
    'admin.tools_sysinfo_db_size'         => 'Database size',
    'admin.tools_sysinfo_license'         => 'License',
    'admin.tools_sysinfo_license_details' => 'Details',
    'admin.tools_sysinfo_yes'             => 'Yes',
    'admin.tools_sysinfo_no'              => 'No',
    'admin.tools_sysinfo_not_set'         => 'Not set',
];

// --- Deutsch ---
$translations['de'] = [
    'chart.visitors'  => 'Besucher',
    'chart.pageviews' => 'Seitenaufrufe',

    'dash.title'              => 'Statistiken',
    'dash.admin'              => 'Admin',
    'dash.refresh'            => 'Daten aktualisieren',
    'dash.dark'               => 'Dunkelmodus',
    'dash.light'              => 'Hellmodus',
    'dash.now_online'         => 'Jetzt online',
    'dash.visitors_today'     => 'Besucher heute',
    'dash.pageviews_today'    => 'Seitenaufrufe heute',
    'dash.total_visitors'     => 'Besucher gesamt',
    'dash.bounce_rate'        => 'Absprungrate',
    'dash.avg_time'           => 'Durchschn. Zeit',
    'dash.auto_refresh'       => 'Aktualisiert automatisch alle 60 Sekunden',
    'dash.updated'            => 'Aktualisiert',
    'dash.footer'             => 'Powered by Counto Analytics',
    'dash.no_data'            => 'Noch keine Daten verfügbar',
    'dash.error_fetch'        => 'Fehler beim Abrufen der Daten',
    'dash.not_available'      => 'Dashboard nicht verfügbar',
    'dash.not_available_desc' => 'Das Dashboard ist derzeit nicht verfügbar. Bitte versuchen Sie es später erneut.',
    'dash.install_required'   => 'Installation erforderlich',
    'dash.trend_7_days'       => 'Trend letzte 7 Tage',
    'dash.hourly_dist'        => 'Stündliche Verteilung',
    'dash.longterm_trend'     => 'Langzeit-Trend',
    'dash.browser_dist'       => 'Browser-Verteilung',
    'dash.countoy_dist'       => 'Länder-Verteilung',
    'dash.top_pages'          => 'Top-Seiten',
    'dash.page_overview'      => 'Seitenübersicht',

    'admin.not_installed'              => 'Nicht installiert',
    'admin.not_installed_desc'         => 'Counto Analytics wurde noch nicht installiert. Bitte führen Sie zuerst den Setup-Assistenten aus.',
    'admin.csrf_error'                 => 'Sicherheits-Token ungültig. Bitte laden Sie die Seite neu.',
    'admin.login_error'                => 'Falsches Passwort. Bitte versuchen Sie es erneut.',
    'admin.login_title'                => 'Counto Admin – Anmeldung',
    'admin.login_heading'              => 'Admin-Anmeldung',
    'admin.login_subtitle'             => 'Melden Sie sich mit Ihrem Admin-Passwort an.',
    'admin.login_password_placeholder' => 'Admin-Passwort',
    'admin.login_button'               => 'Anmelden',
    'admin.login_back'                 => 'Zurück zum Dashboard',
    'admin.lang_switch_en'             => 'Switch to English',
    'admin.lang_switch_de'             => 'Zu Deutsch wechseln',
    'admin.lang_switch_title_en'       => 'Zu Englisch wechseln',
    'admin.lang_switch_title_de'       => 'Zu Deutsch wechseln',
    'admin.theme_dark'                 => 'Dunkelmodus',
    'admin.theme_light'                => 'Hellmodus',
    'admin.menu_toggle'                => 'Menü umschalten',
    'admin.dashboard_btn'              => 'Dashboard',
    'admin.logout'                     => 'Abmelden',

    'admin.tab_overview'  => 'Übersicht',
    'admin.tab_visitors'  => 'Besucher',
    'admin.tab_pages'     => 'Seiten',
    'admin.tab_referrers' => 'Verweise',
    'admin.tab_settings'  => 'Einstellungen',
    'admin.tab_export'    => 'Export',
    'admin.tab_tools'     => 'Werkzeuge',

    'admin.filter_from'    => 'Von',
    'admin.filter_to'      => 'Bis',
    'admin.filter_apply'   => 'Anwenden',
    'admin.filter_7days'   => '7 Tage',
    'admin.filter_30days'  => '30 Tage',
    'admin.filter_90days'  => '90 Tage',

    'admin.overview_title'           => 'Übersicht',
    'admin.overview_online'          => 'Derzeit online',
    'admin.overview_visitors_today'  => 'Besucher heute',
    'admin.overview_pageviews_today' => 'Seitenaufrufe heute',
    'admin.overview_bounce_rate'     => 'Absprungrate',
    'admin.overview_total_visitors'  => 'Besucher gesamt',
    'admin.overview_total_pageviews' => 'Seitenaufrufe gesamt',
    'admin.peak_hour'                => 'Spitzenstunde',
    'admin.growth_trend'             => 'Wachstumstrend',
    'admin.growth_not_enough'        => 'Noch nicht genügend Daten für eine Trendanalyse.',

    'admin.visitors_title' => 'Besucher',
    'admin.browser_dist'   => 'Browser-Verteilung',
    'admin.os_dist'        => 'Betriebssystem-Verteilung',
    'admin.device_dist'    => 'Geräte-Verteilung',
    'admin.countoy_dist'   => 'Länder-Verteilung',

    'admin.pages_title'   => 'Seiten',
    'admin.pages_no_data' => 'Keine Seitendaten für den gewählten Zeitraum verfügbar.',

    'admin.referrers_title'   => 'Verweise',
    'admin.referrers_no_data' => 'Keine Verweisdaten für den gewählten Zeitraum verfügbar.',

    'admin.settings_title'           => 'Einstellungen',
    'admin.settings_site_name'       => 'Name der Webseite',
    'admin.settings_site_url'        => 'URL der Webseite',
    'admin.settings_timezone'        => 'Zeitzone',
    'admin.settings_session_timeout' => 'Session-Timeout (Sekunden)',
    'admin.settings_days_to_keep'    => 'Daten aufbewahren (Tage)',
    'admin.settings_ignore_bots'     => 'Bots und Crawler ignorieren',
    'admin.settings_public_stats'    => 'Öffentliche Statistiken aktivieren',
    'admin.settings_disable_tracking'=> 'Tracking deaktivieren',
    'admin.settings_save'            => 'Einstellungen speichern',
    'admin.settings_saved'           => 'Einstellungen erfolgreich gespeichert.',
    'admin.settings_error_db'        => 'Einstellungen konnten nicht gespeichert werden: Datenbankverbindung fehlgeschlagen.',
    'admin.settings_error_save'      => 'Einige Einstellungen konnten nicht gespeichert werden. Bitte prüfen Sie die Schreibrechte.',
    'admin.settings_error_save_full' => 'Keine Einstellungen konnten gespeichert werden. Bitte prüfen Sie die Schreibrechte.',

    'admin.password_title'     => 'Passwort ändern',
    'admin.password_current'   => 'Aktuelles Passwort',
    'admin.password_new'       => 'Neues Passwort',
    'admin.password_confirm'   => 'Neues Passwort bestätigen',
    'admin.password_button'    => 'Passwort ändern',
    'admin.password_wrong'     => 'Aktuelles Passwort ist falsch.',
    'admin.password_too_short' => 'Neues Passwort muss mindestens 6 Zeichen lang sein.',
    'admin.password_mismatch'  => 'Neue Passwörter stimmen nicht überein.',
    'admin.password_changed'   => 'Passwort erfolgreich geändert.',

    'admin.export_title'    => 'Export',
    'admin.export_desc'     => 'Exportieren Sie Ihre Analysedaten in verschiedenen Formaten.',
    'admin.export_csv'      => 'CSV exportieren',
    'admin.export_json'     => 'JSON exportieren',
    'admin.export_excel'    => 'Excel exportieren',
    'admin.export_range'    => 'Aktueller Zeitraum',
    'admin.export_adjust'   => 'Passen Sie den Datumsfilter oben an, um den Exportzeitraum zu ändern.',

    'admin.api_title'              => 'API-Zugriff',
    'admin.api_token'              => 'API-Token',
    'admin.api_current'            => 'Aktueller API-Token',
    'admin.api_no_token'           => 'Noch kein Token generiert',
    'admin.api_generate'           => 'Neuen Token generieren',
    'admin.api_generated'          => 'API-Token erfolgreich generiert.',
    'admin.export_token_title'     => 'Export-Token',
    'admin.export_token_current'   => 'Aktueller Export-Token',
    'admin.export_token_generate'  => 'Neuen Export-Token generieren',
    'admin.export_token_generated' => 'Export-Token erfolgreich generiert.',

    // Admin – Tracking Tab (Deutsch)
    'admin.tab_tracking'                => 'Tracking',
    'admin.tracking_title'              => 'Tracking-Code Integration',
    'admin.tracking_intro'              => 'Integrieren Sie Counto Analytics in Ihre Webseite. Wählen Sie die Methode, die am besten zu Ihrem Setup passt.',
    'admin.tracking_option_a'           => 'Standard: Script-Tag (Empfohlen)',
    'admin.tracking_option_a_desc'      => 'Platzieren Sie diesen Script-Tag kurz vor dem schließenden </body>-Tag auf jeder Seite, die Sie tracken möchten.',
    'admin.tracking_option_a_tip'       => 'Das <code>defer</code>-Attribut sorgt dafür, dass das Script geladen wird, ohne das Rendering der Seite zu blockieren. Counto erkennt automatisch die aktuelle Seiten-URL.',
    'admin.tracking_option_b'           => 'Manuell: Platzierung im <head>',
    'admin.tracking_option_b_desc'      => 'Alternativ können Sie das Tracking-Script im <head>-Bereich platzieren. Beachten Sie dabei:',
    'admin.tracking_option_b_li1'       => 'Entfernen Sie das <code>defer</code>-Attribut bei Platzierung im <head> – sonst wird das Script möglicherweise nicht ausgeführt.',
    'admin.tracking_option_b_li2'       => 'Das Script ist asynchron und blockiert das Rendering nicht.',
    'admin.tracking_option_b_li3'       => 'Für optimale Kompatibilität wird die Platzierung vor </body> bevorzugt.',
    'admin.tracking_option_b_note'      => 'Bei Platzierung im <head> stellen Sie sicher, dass Ihre <code>Content-Security-Policy</code>-Header externe Scripts von dieser Domain erlauben.',
    'admin.tracking_option_c'           => 'CMS & gehostete Plattformen',
    'admin.tracking_option_c_desc'      => 'Integrationsanleitungen für gängige Content-Management-Systeme und gehostete Plattformen:',
    'admin.tracking_cms_wp'             => 'Fügen Sie den Script-Tag über die <code>footer.php</code> des Themes ein oder nutzen Sie ein "Header/Footer Scripts"-Plugin.',
    'admin.tracking_cms_other'          => 'Fügen Sie den Script-Tag in der Template-Datei Ihres Themes (z. B. <code>index.php</code>) vor <code></body></code> ein.',
    'admin.tracking_cms_hosted'         => 'Nutzen Sie den integrierten "Custom Code"- oder "Tracking-Code"-Bereich in den Einstellungen Ihrer Seite, um den Script-Tag einzufügen.',
    'admin.tracking_cms_custom_title'   => 'Eigene / Statische Seite',
    'admin.tracking_cms_custom'         => 'Fügen Sie den Script-Tag direkt in Ihr HTML-Template ein oder nutzen Sie das Layout-/Partial-System Ihres Static-Site-Generators.',
    'admin.tracking_endpoint_title'     => 'Tracking-Endpunkt-Referenz',
    'admin.tracking_endpoint_url'       => 'Endpunkt-URL',
    'admin.tracking_endpoint_method'    => 'HTTP-Methode',
    'admin.tracking_endpoint_params'    => 'Parameter',
    'admin.tracking_endpoint_response'  => 'Antwortformate',

    'admin.tools_cleanup_title'   => 'Datenbereinigung',
    'admin.tools_cleanup_label'   => 'Daten löschen, die älter sind als (Tage)',
    'admin.tools_cleanup_confirm' => 'Sind Sie sicher, dass Sie alte Daten löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.',
    'admin.tools_cleanup_button'  => 'Alte Daten löschen',
    'admin.tools_cleanup_done'    => 'Einträge gelöscht, älter als',
    'admin.tools_cleanup_done_suffix' => 'Tage.',
    'admin.tools_backup_title'    => 'Backup',
    'admin.tools_backup_create'   => 'Backup erstellen',
    'admin.tools_backup_button'   => 'Jetzt Backup erstellen',
    'admin.tools_backup_existing' => 'Vorhandene Backups',
    'admin.tools_backup_none'     => 'Keine Backups gefunden.',
    'admin.tools_backup_success'  => 'Backup erstellt:',
    'admin.tools_backup_error'    => 'Backup-Erstellung fehlgeschlagen. Bitte prüfen Sie die Schreibrechte.',
    'admin.tools_sysinfo_title'           => 'Systeminformationen',
    'admin.tools_sysinfo_php'             => 'PHP-Version',
    'admin.tools_sysinfo_timezone'        => 'Zeitzone',
    'admin.tools_sysinfo_first'           => 'Erstes Tracking',
    'admin.tools_sysinfo_last'            => 'Letztes Tracking',
    'admin.tools_sysinfo_tracking'        => 'Tracking aktiv',
    'admin.tools_sysinfo_public'          => 'Öffentliche Statistiken',
    'admin.tools_sysinfo_db'              => 'Datenbank-Pfad',
    'admin.tools_sysinfo_db_size'         => 'Datenbank-Größe',
    'admin.tools_sysinfo_license'         => 'Lizenz',
    'admin.tools_sysinfo_license_details' => 'Details',
    'admin.tools_sysinfo_yes'             => 'Ja',
    'admin.tools_sysinfo_no'              => 'Nein',
    'admin.tools_sysinfo_not_set'         => 'Nicht gesetzt',
];

// =========================================================================
// LOAD TRANSLATIONS
// =========================================================================

if (isset($translations[$lang])) {
    $t = $translations[$lang];
} else {
    $t = $translations['en'];
}

// Check for missing keys compared to English and fill with English fallback
if ($lang !== 'en' && isset($translations['en'])) {
    foreach ($translations['en'] as $key => $value) {
        if (!isset($t[$key])) {
            $t[$key] = $value;
        }
    }
}

/**
 * Helper: return translation for a given key, or the key itself if missing.
 *
 * @param string $key
 * @return string
 */
function __(string $key): string
{
    global $t;
    return $t[$key] ?? $key;
}

/**
 * Helper: echo translation for a given key.
 *
 * @param string $key
 * @return void
 */
function _e(string $key): void
{
    echo __($key);
}