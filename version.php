<?php
/**
 * Counto Analytics – Central Version File
 *
 * This is the single source of truth for the current Counto Analytics version.
 * All other files (config.json, database, headers) derive their version
 * number from this file.
 *
 * Run `php update_version.php` to bump the version automatically.
 *
 * @package    Counto
 * @copyright  2026 Counto Analytics
 * @license    GPL-3.0-or-later
 */

declare(strict_types=1);

define('COUNTO_VERSION', '1.4.1');
define('COUNTO_VERSION_DATE', '2026-07-08');