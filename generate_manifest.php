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
    'name' => 'Forex Trading Platform',
    'short_name' => 'Forex',
    'description' => 'Lightweight forex trading platform with live charts and positions.',
    'start_url' => $basePath . '/index.php',
    'scope' => $basePath . '/',
    'display' => 'standalone',
    'background_color' => '#ffffff',
    'theme_color' => '#3498db',
    'orientation' => 'portrait-primary',
    'icons' => [
        [
            'src' => $basePath . '/assets/icons/icon-192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any maskable'
        ],
        [
            'src' => $basePath . '/assets/icons/icon-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any maskable'
        ]
    ],
    'categories' => ['finance', 'productivity'],
    'screenshots' => []
];

echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
