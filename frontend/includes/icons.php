<?php
/**
 * icons.php — inline SVG icons (Lucide-style). Outline + filled variants.
 * Usage: echo icon('home', 'outline');
 */

function icon_paths(string $name): array
{
    $icons = [
        'home' => [
            'outline' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'filled'  => '<path d="M3 9.2 12 2l9 7.2V20a2 2 0 0 1-2 2h-5v-6h-4v6H5a2 2 0 0 1-2-2z"/>',
        ],
        'calendar' => [
            'outline' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
            'filled'  => '<rect x="3" y="4" width="18" height="18" rx="2"/><rect x="3" y="10" width="18" height="4"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>',
        ],
        'bell' => [
            'outline' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
            'filled'  => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
        ],
        'user' => [
            'outline' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'filled'  => '<path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm-8 10a8 8 0 0 1 16 0z"/>',
        ],
        'dashboard' => [
            'outline' => '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>',
            'filled'  => '<path d="M3 3h7v9H3zM14 3h7v5h-7zM14 12h7v9h-7zM3 16h7v5H3z"/>',
        ],
        'map' => [
            'outline' => '<polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/>',
            'filled'  => '<path d="M8 2 16 6l7-4v16l-7 4-8-4-7 4V6z"/>',
        ],
        'users' => [
            'outline' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'filled'  => '<path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm8 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-8 1c-3.5 0-8 1.6-8 5v3h16v-3c0-3.4-4.5-5-8-5zm8 1c.4 0 .8 0 1.2.1C20.9 13.3 22 15 22 17v3h-3v-3c0-1.7-.7-3-2-4-.3 0-.7-.1-1-.1z"/>',
        ],
        'arrow-left' => [
            'outline' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
            'filled'  => '<path d="M19 11H7.8l4.6-4.6L11 5l-7 7 7 7 1.4-1.4L7.8 13H19z"/>',
        ],
        'plus' => [
            'outline' => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
            'filled'  => '<path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6z"/>',
        ],
        'check' => [
            'outline' => '<polyline points="20 6 9 17 4 12"/>',
            'filled'  => '<path d="m9 16.2-4.2-4.2L3.4 13.4 9 19 21 7l-1.4-1.4z"/>',
        ],
        'inbox' => [
            'outline' => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
            'filled'  => '<path d="M22 12h-6l-2 3h-4l-2-3H2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2zM5.45 5.11 2 12h6l2 3h4l2-3h6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
        ],
        'truck' => [
            'outline' => '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
            'filled'  => '<path d="M1 3h15v13H1zm15 5h4l3 3v5h-7zM5.5 16a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zm13 0a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5z"/>',
        ],
        'logout' => [
            'outline' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
            'filled'  => '<path d="M9 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h4v-2H5V5h4zm7 4 5 5-5 5-1.4-1.4L17.2 13H9v-2h8.2l-2.6-2.6z"/>',
        ],
    ];

    return $icons[$name] ?? ['outline' => '', 'filled' => ''];
}

function icon(string $name, string $variant = 'outline', string $extraClass = ''): string
{
    $paths = icon_paths($name);
    $variant = $variant === 'filled' ? 'filled' : 'outline';
    $cls = 'icon ' . $variant . ($extraClass ? ' ' . $extraClass : '');
    return '<svg class="' . $cls . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . $paths[$variant]
        . '</svg>';
}