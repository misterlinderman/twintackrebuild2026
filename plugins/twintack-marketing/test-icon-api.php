<?php
/**
 * Test Noun Project API Icon Download
 * Direct test to see what the API returns
 */

// Load WordPress
$wp_load_paths = array(
    __DIR__ . '/../../../wp-load.php',
    __DIR__ . '/../../../../wp-load.php',
    __DIR__ . '/../../../../../wp-load.php'
);

foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        break;
    }
}

if (!defined('ABSPATH')) {
    die('Could not load WordPress');
}

// Check admin
if (!current_user_can('manage_options')) {
    die('Unauthorized - must be logged in as admin');
}

// Test single icon
$icon_id = 8116739; // Carousel icon

echo '<h1>Testing Noun Project API for Icon ID: ' . $icon_id . '</h1>';
echo '<style>body { font-family: monospace; padding: 20px; background: #f0f0f1; } pre { background: white; padding: 20px; border: 1px solid #ccc; overflow: auto; }</style>';

if (!class_exists('TwinTack_NounProject_API')) {
    echo '<p style="color: red;">Error: TwinTack_NounProject_API class not found</p>';
    exit;
}

$api = TwinTack_NounProject_API::get_instance();

if (!$api->is_configured()) {
    echo '<p style="color: red;">Error: API not configured</p>';
    exit;
}

echo '<h2>Step 1: Get Icon Data</h2>';
$icon_data = $api->get_icon($icon_id);

if (is_wp_error($icon_data)) {
    echo '<p style="color: red;">Error: ' . $icon_data->get_error_message() . '</p>';
    exit;
}

echo '<pre>';
print_r($icon_data);
echo '</pre>';

echo '<h2>Step 2: Download Icon</h2>';
$svg = $api->download_icon_svg($icon_id);

if (is_wp_error($svg)) {
    echo '<p style="color: red;">Error: ' . $svg->get_error_message() . '</p>';
    exit;
}

echo '<p style="color: green;">Success! Downloaded ' . strlen($svg) . ' bytes</p>';
echo '<h3>Preview:</h3>';
echo '<div style="background: white; padding: 20px; border: 1px solid #ccc;">';
echo $svg;
echo '</div>';

echo '<h3>Raw SVG/XML:</h3>';
echo '<pre>' . htmlspecialchars($svg) . '</pre>';

echo '<h2>Step 3: Save to File</h2>';
$icons_dir = plugin_dir_path(__FILE__) . 'assets/images/icons/';
if (!file_exists($icons_dir)) {
    wp_mkdir_p($icons_dir);
}

$file_path = $icons_dir . 'test-carousel.svg';
$result = file_put_contents($file_path, $svg);

if ($result !== false) {
    echo '<p style="color: green;">✓ Saved to: ' . $file_path . '</p>';
} else {
    echo '<p style="color: red;">✗ Failed to save to: ' . $file_path . '</p>';
}

