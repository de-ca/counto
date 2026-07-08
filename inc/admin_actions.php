<?php
/**
 * Counto Analytics – Admin Action Handlers
 *
 * Processes all POST actions in the admin panel:
 *   Save settings, change password, cleanup data,
 *   generate API/export tokens, create backup.
 *
 * Included from admin.php after authentication setup.
 *
 * @package    Counto
 * @copyright  2026 Counto Analytics
 * @license    GPL-3.0-or-later
 * @version 1.4.1
 *
 * @global array        &$rawConfig   Counto settings (nested array from SQLite, passed by reference)
 * @global \Counto\Core\Database\DatabaseFacade $db           Database facade instance
 * @global Tracker      $tracker      Tracker instance
 * @global Stats        $stats        Stats instance
 * @global string       $message      Message text (mutable via reference)
 * @global string       $messageType  Message type: 'info', 'success', or 'error' (mutable via reference)
 */

declare(strict_types=1);

// =========================================================================
// HANDLE ADMIN ACTIONS (when logged in)
// =========================================================================
$message = $message ?? '';
$messageType = $messageType ?? 'info';

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- CSRF Protection for all POST actions ---
    $csrfValid = \Counto\Utils\Security::verifyCsrf($_POST['_csrf'] ?? '');
    if (!$csrfValid) {
        $message = __('admin.csrf_error');
        $messageType = 'error';
    } else {
        // --- Save Settings (to SQLite) ---
        if (isset($_POST['save_settings'])) {
            // Validate DB connection before persisting
            if (!$db->isConnected()) {
                $db->connect();
                if (!$db->isConnected()) {
                    $message = __('admin.settings_error_db');
                    $messageType = 'error';
                }
            }

            if (empty($message)) {
                $rawConfig['site']['name'] = trim($_POST['site_name'] ?? $rawConfig['site']['name']);
                $rawConfig['site']['url'] = trim($_POST['site_url'] ?? $rawConfig['site']['url']);
                $rawConfig['tracking']['timezone'] = $_POST['timezone'] ?? $rawConfig['tracking']['timezone'];
                $rawConfig['tracking']['session_timeout'] = (int)($_POST['session_timeout'] ?? 1800);
                $rawConfig['tracking']['ignore_bots'] = !empty($_POST['ignore_bots']);
                $rawConfig['privacy']['days_to_keep'] = (int)($_POST['days_to_keep'] ?? 90);
                $rawConfig['privacy']['disable_tracking'] = !empty($_POST['disable_tracking']);
                $rawConfig['security']['enable_public_stats'] = !empty($_POST['enable_public_stats']);

                // Persist all settings to SQLite via flat key-value pairs
                $saveResults = [];
                $saveResults['site.name'] = $db->setSetting('site.name', $rawConfig['site']['name']);
                $saveResults['site.url'] = $db->setSetting('site.url', $rawConfig['site']['url']);
                $saveResults['tracking.timezone'] = $db->setSetting('tracking.timezone', $rawConfig['tracking']['timezone']);
                $saveResults['tracking.session_timeout'] = $db->setSetting('tracking.session_timeout', (string)$rawConfig['tracking']['session_timeout']);
                $saveResults['tracking.ignore_bots'] = $db->setSetting('tracking.ignore_bots', $rawConfig['tracking']['ignore_bots'] ? '1' : '0');
                $saveResults['privacy.days_to_keep'] = $db->setSetting('privacy.days_to_keep', (string)$rawConfig['privacy']['days_to_keep']);
                $saveResults['privacy.disable_tracking'] = $db->setSetting('privacy.disable_tracking', $rawConfig['privacy']['disable_tracking'] ? '1' : '0');
                $saveResults['security.enable_public_stats'] = $db->setSetting('security.enable_public_stats', $rawConfig['security']['enable_public_stats'] ? '1' : '0');

                $saveErrors = array_filter($saveResults, fn($v) => $v === false);
                if (!empty($saveErrors)) {
                    $allFailed = count($saveErrors) === count($saveResults);
                    $message = $allFailed ? __('admin.settings_error_save_full') : __('admin.settings_error_save');
                    $messageType = 'error';
                } else {
                    $stats->invalidateCache();
                    header("Location: admin.php?saved=1");
                    exit;
                }
                $stats->invalidateCache();
            }
        }

        // --- Change Password (SQLite) ---
        if (isset($_POST['change_password'])) {
            $currentPw = $_POST['current_password'] ?? '';
            $newPw = $_POST['new_password'] ?? '';
            $confirmPw = $_POST['confirm_password'] ?? '';

            if (!password_verify($currentPw, $rawConfig['security']['admin_password'] ?? '')) {
                $message = __('admin.password_wrong');
                $messageType = 'error';
            } elseif (strlen($newPw) < 6) {
                $message = __('admin.password_too_short');
                $messageType = 'error';
            } elseif ($newPw !== $confirmPw) {
                $message = __('admin.password_mismatch');
                $messageType = 'error';
            } else {
                $hash = password_hash($newPw, PASSWORD_DEFAULT);
                $rawConfig['security']['admin_password'] = $hash;
                $db->setSetting('security.admin_password', $hash);
                $message = __('admin.password_changed');
                $messageType = 'success';
            }
        }

        // --- Cleanup Data ---
        if (isset($_POST['cleanup_data'])) {
            $days = (int)($_POST['cleanup_days'] ?? 90);
            $deleted = $tracker->cleanup($days);
            $stats->invalidateCache();
            $message = "{$deleted} " . __('admin.tools_cleanup_done') . " {$days} " . __('admin.tools_cleanup_done_suffix');
            $messageType = 'success';
        }

        // --- Generate API Token (SQLite) ---
        if (isset($_POST['generate_token'])) {
            $token = 'wc_' . bin2hex(random_bytes(24));
            $rawConfig['security']['api_key'] = $token;
            $db->setSetting('security.api_key', $token);
            $message = __('admin.api_generated');
            $messageType = 'success';
        }

        // --- Generate Export Token (SQLite) ---
        if (isset($_POST['generate_export_token'])) {
            $token = bin2hex(random_bytes(32));
            $rawConfig['security']['export_token'] = $token;
            $db->setSetting('security.export_token', $token);
            $message = __('admin.export_token_generated');
            $messageType = 'success';
        }

        // --- Create Backup (SQLite) ---
        if (isset($_POST['create_backup'])) {
            $backupResult = $db->backup();
            if ($backupResult) {
                $message = __('admin.tools_backup_success') . ' ' . basename($backupResult);
                $messageType = 'success';
            } else {
                $message = __('admin.tools_backup_error');
                $messageType = 'error';
            }
        }
    } // end CSRF check
}