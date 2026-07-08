<?php
/**
 * Counto Analytics – Version Automation Script
 *
 * Bumps the version number across the entire project:
 *   1. Updates version.php (central version file)
 *   2. Updates config.json (system.version)
 *   3. Prepends a placeholder entry to CHANGELOG-de.md and CHANGELOG-en.md
 *
 * Usage:
 *   php update_version.php                    # bumps PATCH (1.4.0 -> 1.4.1)
 *   php update_version.php minor              # bumps MINOR (1.4.0 -> 1.5.0)
 *   php update_version.php major              # bumps MAJOR (1.4.0 -> 2.0.0)
 *   php update_version.php <custom>           # sets to <custom> e.g. "1.4.1-beta"
 *
 * @package    Counto
 * @copyright  2026 Counto Analytics
 * @license    GPL-3.0-or-later
 */

declare(strict_types=1);

define('COUNTO_DIR', __DIR__);

// =========================================================================
// PARSE ARGUMENTS
// =========================================================================

$bumpType = $argv[1] ?? 'patch'; // patch, minor, major, or custom version string
$customVersion = null;

if (!in_array($bumpType, ['patch', 'minor', 'major'], true)) {
    // Treat as custom version string
    $customVersion = $bumpType;
    $bumpType = 'custom';
}

// =========================================================================
// READ CURRENT VERSION FROM version.php
// =========================================================================

$versionFile = COUNTO_DIR . '/version.php';
if (!file_exists($versionFile)) {
    die("ERROR: version.php not found at {$versionFile}. Cannot proceed.\n");
}

$versionContent = file_get_contents($versionFile);
if (!preg_match("/define\('COUNTO_VERSION',\s*'([0-9]+\.[0-9]+\.[0-9]+(?:-[a-zA-Z0-9]+)?)'\);/", $versionContent, $matches)) {
    die("ERROR: Could not parse COUNTO_VERSION from version.php\n");
}

$currentVersion = $matches[1];
echo "Current version: {$currentVersion}\n";

// =========================================================================
// COMPUTE NEW VERSION
// =========================================================================

if ($customVersion !== null) {
    $newVersion = $customVersion;
} else {
    $parts = explode('.', $currentVersion);
    // Strip any pre-release suffix from the last part
    $lastPart = explode('-', $parts[2] ?? '0');
    $patch = (int)($lastPart[0] ?? 0);
    $minor = (int)($parts[1] ?? 0);
    $major = (int)($parts[0] ?? 1);

    switch ($bumpType) {
        case 'major':
            $major++;
            $minor = 0;
            $patch = 0;
            break;
        case 'minor':
            $minor++;
            $patch = 0;
            break;
        case 'patch':
        default:
            $patch++;
            break;
    }

    $newVersion = "{$major}.{$minor}.{$patch}";
}

echo "New version:     {$newVersion}\n";

$today = date('Y-m-d');
echo "Date:            {$today}\n\n";

// =========================================================================
// 1. UPDATE version.php
// =========================================================================

$updatedVersionContent = preg_replace(
    "/define\('COUNTO_VERSION',\s*'[^']*'\);/",
    "define('COUNTO_VERSION', '{$newVersion}');",
    $versionContent
);
$updatedVersionContent = preg_replace(
    "/define\('COUNTO_VERSION_DATE',\s*'[^']*'\);/",
    "define('COUNTO_VERSION_DATE', '{$today}');",
    $updatedVersionContent
);

if (file_put_contents($versionFile, $updatedVersionContent) === false) {
    echo "ERROR: Could not write version.php\n";
    exit(1);
}
echo "[OK] Updated version.php\n";

// =========================================================================
// 2. UPDATE config.json (system.version)
// =========================================================================

$configFile = COUNTO_DIR . '/data/config.json';
if (file_exists($configFile)) {
    $configJson = file_get_contents($configFile);
    $config = json_decode($configJson, true);

    if (is_array($config)) {
        $config['system']['version'] = $newVersion;
        $newConfigJson = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($newConfigJson !== false && file_put_contents($configFile, $newConfigJson, LOCK_EX) !== false) {
            echo "[OK] Updated config.json (system.version -> {$newVersion})\n";
        } else {
            echo "[WARN] Could not write config.json\n";
        }
    } else {
        echo "[WARN] config.json is not a valid JSON object, skipping\n";
    }
} else {
    echo "[INFO] config.json not found at {$configFile}, skipping\n";
}

// =========================================================================
// 3. UPDATE SQLite settings table (system.version) if database exists
// =========================================================================

$dbFile = COUNTO_DIR . '/data/counto.db';
if (file_exists($dbFile)) {
    try {
        // Lightweight: use PDO directly to avoid pulling in the full DatabaseFacade stack
        $pdo = new PDO('sqlite:' . $dbFile, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $stmt = $pdo->prepare(
            "INSERT INTO settings (key, value) VALUES (:key, :value) 
             ON CONFLICT(key) DO UPDATE SET value = :value2"
        );
        $stmt->execute([
            ':key'    => 'system.version',
            ':value'  => $newVersion,
            ':value2' => $newVersion,
        ]);
        echo "[OK] Updated SQLite settings (system.version -> {$newVersion})\n";
    } catch (\Throwable $e) {
        echo "[WARN] Could not update SQLite settings: {$e->getMessage()}\n";
    }
} else {
    echo "[INFO] SQLite database not found, skipping\n";
}

// =========================================================================
// 4. PREPEND PLACEHOLDER ENTRY TO CHANGELOGs
// =========================================================================

$placeholderDe = <<<MD

## [{$newVersion}] – {$today}

### Hinzugefügt

- 

### Geändert

- 

### Sicherheit

- 

### Behoben

- 

---

MD;

$placeholderEn = <<<MD

## [{$newVersion}] – {$today}

### Added

- 

### Changed

- 

### Security

- 

### Fixed

- 

---

MD;

// DE changelog
$changelogDeFile = COUNTO_DIR . '/CHANGELOG-de.md';
if (file_exists($changelogDeFile)) {
    $contentDe = file_get_contents($changelogDeFile);
    // Find the first version heading line (## [x.x.x]) and insert before it
    $pattern = '/\n##\s+\[[0-9]+\.[0-9]+\.[0-9]+(?:-[a-zA-Z0-9]+)?\]/';
    if (preg_match($pattern, $contentDe, $matches, PREG_OFFSET_CAPTURE)) {
        $pos = $matches[0][1];
        $contentDe = substr_replace($contentDe, $placeholderDe, $pos, 0);
        if (file_put_contents($changelogDeFile, $contentDe, LOCK_EX) !== false) {
            echo "[OK] Prepended placeholder to CHANGELOG-de.md\n";
        } else {
            echo "[WARN] Could not write CHANGELOG-de.md\n";
        }
    } else {
        echo "[WARN] Could not find insertion point in CHANGELOG-de.md\n";
    }
} else {
    echo "[INFO] CHANGELOG-de.md not found, skipping\n";
}

// EN changelog
$changelogEnFile = COUNTO_DIR . '/CHANGELOG-en.md';
if (file_exists($changelogEnFile)) {
    $contentEn = file_get_contents($changelogEnFile);
    $pattern = '/\n##\s+\[[0-9]+\.[0-9]+\.[0-9]+(?:-[a-zA-Z0-9]+)?\]/';
    if (preg_match($pattern, $contentEn, $matches, PREG_OFFSET_CAPTURE)) {
        $pos = $matches[0][1];
        $contentEn = substr_replace($contentEn, $placeholderEn, $pos, 0);
        if (file_put_contents($changelogEnFile, $contentEn, LOCK_EX) !== false) {
            echo "[OK] Prepended placeholder to CHANGELOG-en.md\n";
        } else {
            echo "[WARN] Could not write CHANGELOG-en.md\n";
        }
    } else {
        echo "[WARN] Could not find insertion point in CHANGELOG-en.md\n";
    }
} else {
    echo "[INFO] CHANGELOG-en.md not found, skipping\n";
}

echo "\nDone! Version bumped from {$currentVersion} to {$newVersion}.\n";
echo "Don't forget to fill in the CHANGELOG entries and commit your changes.\n";