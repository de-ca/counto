<?php
/**
 * Admin Tracking Tab View
 *
 * @package    Counto
 * @copyright  2026 Counto Analytics
 * @license    GPL-3.0-or-later
 * @version 1.4.1
 *
 * @global array  $rawConfig          Counto settings (nested array from SQLite)
 * @global string $tracking_script_tag The pre-built <script> tag for track.js
 */

declare(strict_types=1);

// Variables $trackingBaseUrl, $trackScriptUrl, and $tracking_script_tag
// are pre-computed in admin.php before this view is included.
// Fallback: compute them dynamically via auto-detection if not already defined.
if (!isset($baseUrl)) {
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $baseUrl .= $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl .= dirname($_SERVER['SCRIPT_NAME']);
    // Fallback to config site.url if server vars are incomplete
    if (empty($_SERVER['HTTP_HOST']) || empty($_SERVER['SCRIPT_NAME'])) {
        $baseUrl = rtrim($rawConfig['site']['url'] ?? '', '/');
    }
}
if (!isset($trackingBaseUrl)) {
    $trackingBaseUrl = $baseUrl;
}
if (!isset($trackScriptUrl)) {
    $trackScriptUrl = $baseUrl . '/track.php?js=1';
}
if (!isset($tracking_script_tag)) {
    // Universal inline tracking snippet – auto-detects the correct base URL on every installation
    $tracking_script_tag = '<script>(function(e,n){e.src=n+"/track.php?js=1";e.async=!0;document.head.appendChild(e)})(document.createElement("script"),"' . $baseUrl . '");</script>';
}
?>
<div id="tab-tracking" class="tab-panel">
    <section class="admin-section">
        <h2 class="section-title"><?= __('admin.tracking_title') ?></h2>
        <p style="margin-bottom:1.5rem;color:var(--text-secondary);">
            <?= __('admin.tracking_intro') ?>
        </p>

        <!-- Option A: Standard Script Tag -->
        <div class="chart-card" style="margin-bottom:1.5rem;">
            <h3 class="chart-title">🔧 <?= __('admin.tracking_option_a') ?></h3>
            <p style="margin-bottom:0.75rem;color:var(--text-secondary);">
                <?= __('admin.tracking_option_a_desc') ?>
            </p>
            <pre style="background:var(--card-bg);border:1px solid var(--border);padding:1rem;border-radius:8px;overflow-x:auto;position:relative;"><code><?= e($tracking_script_tag) ?></code>
<button class="copy-snippet-btn" style="position:absolute;top:8px;right:8px;background:var(--accent);color:#fff;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:11px;" onclick="navigator.clipboard.writeText(this.parentElement.querySelector('code').textContent).then(()=>{this.textContent='✅';setTimeout(()=>{this.textContent='📋';},1500);})">📋</button></pre>
            <p class="form-help" style="margin-top:0.5rem;">
                💡 <?= __('admin.tracking_option_a_tip') ?>
            </p>
        </div>

        <!-- Option B: Manual Placement -->
        <div class="chart-card" style="margin-bottom:1.5rem;">
            <h3 class="chart-title">📝 <?= __('admin.tracking_option_b') ?></h3>
            <p style="margin-bottom:0.75rem;color:var(--text-secondary);">
                <?= __('admin.tracking_option_b_desc') ?>
            </p>
            <ul style="list-style:disc;padding-left:1.5rem;color:var(--text-secondary);line-height:1.8;">
                <li><?= __('admin.tracking_option_b_li1') ?></li>
                <li><?= __('admin.tracking_option_b_li2') ?></li>
                <li><?= __('admin.tracking_option_b_li3') ?></li>
            </ul>
            <div style="margin-top:1rem;background:var(--info-bg, #f0f4ff);border:1px solid var(--info-border, #c6d5f6);border-radius:8px;padding:0.75rem 1rem;font-size:0.85rem;color:var(--info-text, #2f4a85);">
                ⚠️ <?= __('admin.tracking_option_b_note') ?>
            </div>
        </div>

        <!-- Option C: CMS Integration -->
        <div class="chart-card" style="margin-bottom:1.5rem;">
            <h3 class="chart-title">🗂️ <?= __('admin.tracking_option_c') ?></h3>
            <p style="margin-bottom:0.75rem;color:var(--text-secondary);">
                <?= __('admin.tracking_option_c_desc') ?>
            </p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:8px;padding:1rem;">
                    <strong>WordPress</strong>
                    <p style="font-size:0.85rem;color:var(--text-secondary);margin-top:0.25rem;">
                        <?= __('admin.tracking_cms_wp') ?>
                    </p>
                </div>
                <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:8px;padding:1rem;">
                    <strong>Joomla / Drupal</strong>
                    <p style="font-size:0.85rem;color:var(--text-secondary);margin-top:0.25rem;">
                        <?= __('admin.tracking_cms_other') ?>
                    </p>
                </div>
                <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:8px;padding:1rem;">
                    <strong>Shopify / Wix</strong>
                    <p style="font-size:0.85rem;color:var(--text-secondary);margin-top:0.25rem;">
                        <?= __('admin.tracking_cms_hosted') ?>
                    </p>
                </div>
                <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:8px;padding:1rem;">
                    <strong><?= __('admin.tracking_cms_custom_title') ?></strong>
                    <p style="font-size:0.85rem;color:var(--text-secondary);margin-top:0.25rem;">
                        <?= __('admin.tracking_cms_custom') ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Reference: Tracking URL -->
        <div class="chart-card">
            <h3 class="chart-title">📡 <?= __('admin.tracking_endpoint_title') ?></h3>
            <table class="info-table">
                <tr><td><?= __('admin.tracking_endpoint_url') ?></td><td><code><?= e($trackingBaseUrl) ?>/track.php</code></td></tr>
                <tr><td><?= __('admin.tracking_endpoint_method') ?></td><td>GET</td></tr>
                <tr><td><?= __('admin.tracking_endpoint_params') ?></td><td><code>js</code>, <code>page</code>, <code>ref</code>, <code>callback</code></td></tr>
                <tr><td><?= __('admin.tracking_endpoint_response') ?></td><td>1×1 GIF, JSON, JavaScript</td></tr>
            </table>
        </div>
    </section>
</div>