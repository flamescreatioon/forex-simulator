<?php
// Dynamic manifest generator
header('Content-Type: application/manifest+json');
header('Cache-Control: no-cache, must-revalidate');

// Auto-detect base path
$path = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptName);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

$manifest = [
    'name' => 'Volatility 1040',
    'short_name' => 'Volatility 1040',
    'description' => 'Volatility 1040 — lightweight trading dashboard with live charts and positions.',
    'start_url' => $basePath . '/index.php',
    'scope' => $basePath . '/',
    'display' => 'standalone',
    'background_color' => '#ffffff',
    'theme_color' => '#2563eb',
    'orientation' => 'portrait-primary',
    'icons' => [
        // Use explicit PHP generators to avoid relying on .htaccess rewrites
        [
            'src' => $basePath . '/assets/icons/icon-192.png.php',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any maskable'
        ],
        [
            'src' => $basePath . '/assets/icons/icon-512.png.php',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any maskable'
        ]
    ],
    'categories' => ['finance', 'productivity'],
    'screenshots' => []
];

echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
