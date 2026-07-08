<?php
/**
 * Standalone test: invoke performSetup() directly from setup.php.
 * Bypasses the security lock so we can verify config.json and counto.db
 * are created correctly with the proper site name, URL, and password hash.
 */

define('COUNTO_DIR', __DIR__);
// Remove any stale artifacts to simulate clean state
@unlink(COUNTO_DIR . '/data/config.json');
@unlink(COUNTO_DIR . '/data/.setup_done');
@unlink(COUNTO_DIR . '/data/counto.db');
@unlink(COUNTO_DIR . '/data/stats.json');
// Don't remove setup.php though

require_once __DIR__ . '/version.php';
require_once __DIR__ . '/setup.php';

$settings = [
    'site_name'         => 'Test Site',
    'site_url'          => 'https://test.example.com',
    'password'          => 'secret123',
    'timezone'          => 'Europe/Berlin',
    'anonymize_ip'      => true,
    'ignore_bots'       => true,
    'enable_public'     => true,
    'generate_api_key'  => true,
    'generate_demo_data' => false,
    'enable_multi_user' => false,
];

$result = performSetup($settings);

echo "=== Setup Result ===\n";
echo "Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
echo "Error: " . ($result['error'] ?: 'none') . "\n\n";

// Verify config.json
$configPath = COUNTO_DIR . '/data/config.json';
echo "=== config.json ===\n";
echo "Exists: " . (file_exists($configPath) ? 'YES' : 'NO') . "\n";
if (file_exists($configPath)) {
    $config = json_decode(file_get_contents($configPath), true);
    echo "site.name: " . ($config['site']['name'] ?? 'MISSING') . "\n";
    echo "site.url: " . ($config['site']['url'] ?? 'MISSING') . "\n";
    echo "security.admin_password (hash present): " . (!empty($config['security']['admin_password']) ? 'YES' : 'NO') . "\n";
    echo "BCRYPT prefix check: " . (str_starts_with($config['security']['admin_password'] ?? '', '$2') ? 'YES (bcrypt)' : 'NO') . "\n";
    echo "Password verify ('secret123'): " . (password_verify('secret123', $config['security']['admin_password']) ? 'MATCH' : 'FAIL') . "\n";
    echo "Password verify ('admin'): " . (password_verify('admin', $config['security']['admin_password']) ? 'MATCH (bad!)' : 'no match (good)') . "\n";
}

// Verify counto.db
$dbPath = COUNTO_DIR . '/data/counto.db';
echo "\n=== counto.db ===\n";
echo "Exists: " . (file_exists($dbPath) ? 'YES' : 'NO') . "\n";
if (file_exists($dbPath)) {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // List all tables
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "\n\n";
    
    // Check settings table
    $settings = $db->query("SELECT key, value FROM settings ORDER BY key")->fetchAll(PDO::FETCH_KEY_PAIR);
    echo "=== Settings in DB ===\n";
    $checkKeys = ['site.name', 'site.url', 'security.admin_password', 'tracking.timezone', 'security.api_key'];
    foreach ($checkKeys as $k) {
        $val = $settings[$k] ?? 'MISSING';
        if ($k === 'security.admin_password') {
            $val = !empty($val) ? substr($val, 0, 7) . '...' : 'MISSING';
        }
        echo "  $k: $val\n";
    }
    
    // Verify password_verify against DB hash
    $dbHash = $settings['security.admin_password'] ?? '';
    echo "\nPassword verify from DB ('secret123'): " . (password_verify('secret123', $dbHash) ? 'MATCH' : 'FAIL') . "\n";
    echo "Password verify from DB ('admin'): " . (password_verify('admin', $dbHash) ? 'MATCH (bad!)' : 'no match (good)') . "\n";
}

echo "\n=== Done ===\n";