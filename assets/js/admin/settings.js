/**
 * admin/settings.js - Settings & Tools
 *
 * Settings form with unsaved-changes warning, export functions,
 * backup management, and tool actions (optimize, anonymize).
 *
 * @package Counto
 * @version 1.0.0
 */

/**
 * Initialize settings form with unsaved-changes warning.
 */
export function initSettingsForm() {
    const settingsForm = document.getElementById('settings-form');
    if (!settingsForm) return;

    let isDirty = false;

    const inputs = settingsForm.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('change', () => { isDirty = true; });
        input.addEventListener('input', () => { isDirty = true; });
    });

    window.addEventListener('beforeunload', function (e) {
        if (isDirty) {
            e.preventDefault();
            e.returnValue = 'Ungespeicherte Änderungen!';
            return e.returnValue;
        }
    });

    settingsForm.addEventListener('submit', () => { isDirty = false; });
}

/**
 * Trigger a file download export.
 * @param {string} format - csv, json, or excel
 */
function triggerExport(format) {
    const fromInput = document.getElementById('filter-from');
    const toInput = document.getElementById('filter-to');

    const from = fromInput ? fromInput.value : '';
    const to = toInput ? toInput.value : '';

    let url = 'export.php?format=' + format;
    if (from) url += '&from=' + from;
    if (to) url += '&to=' + to;

    // Open in new tab for download
    window.open(url, '_blank');
}

/**
 * Initialize export buttons.
 */
export function initExportButtons() {
    document.querySelectorAll('[data-export]').forEach(btn => {
        btn.addEventListener('click', function () {
            const format = this.getAttribute('data-export');
            if (format) triggerExport(format);
        });
    });
}

/**
 * Initialize backup management.
 */
export function initBackupTools() {
    // Download backup
    const backupBtn = document.getElementById('btn-create-backup');
    if (backupBtn) {
        backupBtn.addEventListener('click', function () {
            window.open('export.php?action=backup', '_blank');
        });
    }

    // Restore from file
    const restoreInput = document.getElementById('restore-file');
    const restoreBtn = document.getElementById('btn-restore');
    if (restoreInput && restoreBtn) {
        restoreBtn.addEventListener('click', function () {
            if (!restoreInput.files || restoreInput.files.length === 0) {
                alert('Bitte wählen Sie eine Backup-Datei aus.');
                return;
            }
            if (!confirm('⚠️ Datenbank wirklich wiederherstellen? Alle aktuellen Daten werden überschrieben!')) {
                return;
            }
            // Submit the form
            const form = restoreInput.closest('form');
            if (form) form.submit();
        });
    }
}

/**
 * Initialize tool action buttons.
 */
export function initToolActions() {
    // Optimize button
    const optimizeBtn = document.getElementById('btn-optimize');
    if (optimizeBtn) {
        optimizeBtn.addEventListener('click', async function () {
            this.disabled = true;
            this.textContent = '⏳ Optimiere...';
            try {
                const resp = await fetch('api.php?action=tool&tool=optimize', {
                    headers: { 'X-Admin-Action': '1' }
                });
                const data = await resp.json();
                if (data.success) {
                    alert('✅ Datenbank optimiert.');
                } else {
                    alert('❌ Fehler: ' + (data.error || 'Unbekannt'));
                }
            } catch (e) {
                alert('❌ Fehler: ' + e.message);
            }
            this.disabled = false;
            this.textContent = '🔧 Optimieren';
        });
    }

    // Anonymize button
    const anonBtn = document.getElementById('btn-anonymize');
    if (anonBtn) {
        anonBtn.addEventListener('click', async function () {
            if (!confirm('⚠️ Alle IP-Daten unwiderruflich anonymisieren?')) return;
            this.disabled = true;
            this.textContent = '⏳ Anonymisiere...';
            try {
                const resp = await fetch('api.php?action=tool&tool=anonymize', {
                    headers: { 'X-Admin-Action': '1' }
                });
                const data = await resp.json();
                if (data.success) {
                    alert('✅ IP-Daten anonymisiert.');
                } else {
                    alert('❌ Fehler: ' + (data.error || 'Unbekannt'));
                }
            } catch (e) {
                alert('❌ Fehler: ' + e.message);
            }
            this.disabled = false;
            this.textContent = '🛡 IPs anonymisieren';
        });
    }
}

export { triggerExport };

/**
 * Initialize the Integration Check tool.
 */
export function initIntegrationCheck() {
    const btn = document.getElementById('btn-integration-check');
    const urlInput = document.getElementById('integration-url');
    const resultDiv = document.getElementById('integration-result');

    if (!btn || !urlInput || !resultDiv) return;

    btn.addEventListener('click', async function () {
        const checkUrl = urlInput.value.trim();
        if (!checkUrl) {
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<div class="alert alert-error">Please enter a valid URL.</div>';
            return;
        }
        if (!/^https?:\/\//i.test(checkUrl)) {
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<div class="alert alert-error">URL must start with http:// or https://</div>';
            return;
        }

        // Show loading state
        btn.disabled = true;
        btn.textContent = '⏳ Checking...';
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div class="alert alert-info">Fetching page source and scanning for tracking code...</div>';

        try {
            const resp = await fetch('admin.php?ajax=integration_check&check_url=' + encodeURIComponent(checkUrl));
            const data = await resp.json();

            if (data.found) {
                resultDiv.innerHTML = '<div class="alert alert-success">✅ ' + data.message + '<br><small>Pattern: <code>' + (data.search_pattern || '') + '</code></small></div>';
            } else if (data.success) {
                resultDiv.innerHTML = '<div class="alert alert-error">❌ ' + data.message + '<br><small>Expected: <code>' + (data.search_pattern || '') + '</code></small></div>';
            } else {
                resultDiv.innerHTML = '<div class="alert alert-error">❌ ' + data.message + '</div>';
            }
        } catch (e) {
            resultDiv.innerHTML = '<div class="alert alert-error">❌ Network error: ' + e.message + '</div>';
        }

        btn.disabled = false;
        btn.textContent = 'Check';
    });
}
