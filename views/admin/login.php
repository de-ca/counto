<?php
/**
 * Counto Analytics – Admin Login Page View
 *
 * Renders the password-protected login form for the admin area.
 * Uses inline styles to avoid external CSS dependencies.
 *
 * Included from admin.php when user is not logged in.
 *
 * @package    Counto
 * @copyright  2026 Counto Analytics
 * @license    GPL-3.0-or-later
 * @version 1.4.1
 *
 * @global string $lang           Current language code (en/de)
 * @global string $loginError     Login error message (empty if none)
 * @global string $csrfToken      CSRF protection token
 */

declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="<?= ($lang ?? 'en') ?>" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('admin.login_title') ?></title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-box { background: #fff; border-radius: 16px; padding: 2.5rem; box-shadow: 0 20px 60px rgba(0,0,0,0.2); max-width: 420px; width: 100%; text-align: center; }
        .login-box h1 { font-size: 1.5rem; margin-bottom: 0.5rem; color: #1a1a2e; }
        .login-box .sub { color: #6b7280; margin-bottom: 1.5rem; font-size: 0.9rem; }
        .login-box input[type="password"] { width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 1rem; margin-bottom: 1rem; text-align: center; transition: border-color 0.2s; }
        .login-box input[type="password"]:focus { outline: none; border-color: #667eea; }
        .login-box button { width: 100%; padding: 12px; background: #667eea; color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .login-box button:hover { background: #5a6fd6; }
        .login-box .error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; }
        .login-box .back { display: block; margin-top: 1rem; color: #667eea; font-size: 0.85rem; text-decoration: none; }
        .login-lang { position: absolute; top: 1rem; right: 1.5rem; }
        .login-lang a { color: #fff; text-decoration: none; font-weight: 600; font-size: 0.9rem; background: rgba(255,255,255,0.2); padding: 8px 14px; border-radius: 6px; transition: background 0.2s; }
        .login-lang a:hover { background: rgba(255,255,255,0.35); }
    </style>
</head>
<body>
    <div class="login-lang">
        <a href="?lang=<?= ($lang ?? 'en') === 'de' ? 'en' : 'de' ?>">
            <?= ($lang ?? 'en') === 'de' ? __('admin.lang_switch_en') : __('admin.lang_switch_de') ?>
        </a>
    </div>
    <div class="login-box">
        <h1><?= __('admin.login_heading') ?></h1>
        <p class="sub"><?= __('admin.login_subtitle') ?></p>
        <?php if ($loginError): ?>
            <div class="error"><?= e($loginError) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
            <input type="password" name="password" placeholder="<?= __('admin.login_password_placeholder') ?>" required autofocus>
            <button type="submit" name="login"><?= __('admin.login_button') ?></button>
        </form>
        <a class="back" href="index.php"><?= __('admin.login_back') ?></a>
    </div>
</body>
</html>