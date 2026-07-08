<?php
/**
 * PSR-4–style autoloader for the new modular Counto namespace.
 *
 * Maps the namespace prefix `Counto\` to the `inc/` directory.
 * The legacy (non-namespaced) classes in inc/ are loaded separately
 * via require_once – this autoloader only handles the new modular code.
 *
 * Usage:
 *   require_once __DIR__ . '/inc/autoload.php';
 *
 * @package    Counto
 * @copyright  2026 Counto Analytics
 * @license    GPL-3.0-or-later
 * @version 1.4.0
 */

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    // Only handle our namespace
    $prefix = 'Counto\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    // Strip the namespace prefix and convert to file path
    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});