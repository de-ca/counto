<?php
/**
 * Counto Analytics – Version Header Synchronization
 *
 * Reads the canonical version from version.php and updates the
 * @version tag in all .php DocBlocks to match.
 *
 * Usage: php scripts/sync_headers.php
 *
 * @package    Counto
 * @copyright  2026 Counto Analytics
 * @license    GPL-3.0-or-later
 * @version 1.4.0
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------
// 1. Read the canonical version from version.php
// ---------------------------------------------------------------------------
$versionFile = __DIR__ . '/../version.php';
if (!file_exists($versionFile)) {
    fwrite(STDERR, "ERROR: version.php not found at {$versionFile}\n");
    exit(1);
}

// Parse version.php without executing it (safer)
$versionFileContent = file_get_contents($versionFile);
if (!preg_match("/define\s*\(\s*'COUNTO_VERSION'\s*,\s*'([^']+)'\s*\)/", $versionFileContent, $matches)) {
    fwrite(STDERR, "ERROR: Could not extract COUNTO_VERSION from version.php\n");
    exit(1);
}
$canonicalVersion = $matches[1];
echo "Canonical version from version.php: {$canonicalVersion}\n\n";

// ---------------------------------------------------------------------------
// 2. Collect all .php files recursively
// ---------------------------------------------------------------------------
$projectDir = realpath(__DIR__ . '/../');
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$phpFiles = [];
foreach ($iterator as $file) {
    /** @var SplFileInfo $file */
    if ($file->getExtension() === 'php') {
        $phpFiles[] = $file->getPathname();
    }
}

// Sort for consistent output
sort($phpFiles);

echo "Found " . count($phpFiles) . " PHP files.\n\n";

// ---------------------------------------------------------------------------
// 3. Update @version in each file's DocBlock
// ---------------------------------------------------------------------------
$updatedCount = 0;
$skippedCount = 0;
$pattern = '/@version\s+\d+\.\d+\.\d+/';

foreach ($phpFiles as $filePath) {
    $content = file_get_contents($filePath);
    if ($content === false) {
        fwrite(STDERR, "WARNING: Could not read {$filePath}\n");
        $skippedCount++;
        continue;
    }

    // Only match @version within the first 40 lines (typical DocBlock location)
    // This avoids matching @version in unrelated context (e.g. inline comments)
    $lines = explode("\n", $content);
    $found = false;
    $currentVersion = null;

    // Look for @version in the first 50 lines (header DocBlock)
    for ($i = 0; $i < min(50, count($lines)); $i++) {
        if (preg_match($pattern, $lines[$i], $lineMatches)) {
            $found = true;
            preg_match('/\d+\.\d+\.\d+/', $lineMatches[0], $verMatches);
            $currentVersion = $verMatches[0] ?? 'unknown';
            break;
        }
    }

    if (!$found) {
        // No @version tag in this file – skip
        $skippedCount++;
        continue;
    }

    if ($currentVersion === $canonicalVersion) {
        // Already up-to-date
        $skippedCount++;
        continue;
    }

    // Update the content using the full file replacement (not just first 50 lines)
    $newContent = preg_replace($pattern, '@version ' . $canonicalVersion, $content, 1, $replaceCount);

    if ($newContent === null || $replaceCount === 0) {
        fwrite(STDERR, "WARNING: preg_replace failed for {$filePath}\n");
        $skippedCount++;
        continue;
    }

    $relativePath = str_replace($projectDir . '/', '', $filePath);
    if (file_put_contents($filePath, $newContent) === false) {
        fwrite(STDERR, "ERROR: Could not write {$filePath}\n");
        $skippedCount++;
        continue;
    }

    echo "Updated: {$relativePath}  ({$currentVersion} → {$canonicalVersion})\n";
    $updatedCount++;
}

// ---------------------------------------------------------------------------
// 4. Summary
// ---------------------------------------------------------------------------
echo "\n--- Summary ---\n";
echo "Canonical version: {$canonicalVersion}\n";
echo "Files updated:     {$updatedCount}\n";
echo "Files skipped:     {$skippedCount}\n";
echo "Total PHP files:   " . count($phpFiles) . "\n";
echo "Done.\n";