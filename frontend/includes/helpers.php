<?php
/**
 * helpers.php — frontend-side helpers.
 * e() is guarded to avoid colliding with the backend's copy.
 */

require_once __DIR__ . '/icons.php';

if (!function_exists('e')) {
    function e($str): string
    {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * The API base URL. Empty string = same origin (dev router / same host).
 * In production set API_URL=https://api.glorikar.com in backend/.env.
 */
function api_url(): string
{
    return \Env::get('API_URL', '');
}

/**
 * Open the standard app shell: <head> + nav chrome.
 * Usage: page_start('Title', $user); ... markup ...; page_end();
 */
function page_start(string $title, array $user): void
{
    $pageTitle = $title;
    require __DIR__ . '/head.php';
    require __DIR__ . '/nav.php';
}

/** Close the app shell. */
function page_end(): void
{
    echo "\n</div><!-- /.app -->\n</body>\n</html>";
}

/** Sticky page header with optional back button. */
function page_header(string $title, ?string $backHref = null): void
{
    echo '<div class="page-header">';
    if ($backHref !== null) {
        echo '<a class="btn btn-ghost btn-sm" href="' . e($backHref) . '" aria-label="Back">' . icon('arrow-left', 'outline') . '</a>';
    }
    echo '<h1 class="page-title display-sm">' . e($title) . '</h1>';
    echo '</div>';
}

/** Standard error-state block (used by JS to inject retryable errors). */
function error_state_html(string $message): string
{
    return '<div class="error-state">'
        . '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
        . '<span class="body-lg">' . e($message) . '</span>'
        . '<button class="btn btn-ghost btn-sm" onclick="location.reload()">Retry</button>'
        . '</div>';
}

/** Standard empty-state block. */
function empty_state_html(string $icon, string $title, string $sub = ''): string
{
    return '<div class="empty-state">'
        . icon($icon, 'outline')
        . '<span class="heading-sm">' . e($title) . '</span>'
        . ($sub !== '' ? '<span class="body-sm">' . e($sub) . '</span>' : '')
        . '</div>';
}