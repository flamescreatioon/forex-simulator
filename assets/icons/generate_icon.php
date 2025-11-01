<?php
// Simple icon generator using GD library
header('Content-Type: image/png');
header('Cache-Control: public, max-age=31536000');

$size = isset($_GET['size']) ? intval($_GET['size']) : 192;
$size = min(max($size, 16), 1024); // Clamp between 16 and 1024

// Create image
$img = imagecreatetruecolor($size, $size);
imagesavealpha($img, true);

// Colors
$bg = imagecolorallocate($img, 52, 152, 219); // #3498db
$white = imagecolorallocate($img, 255, 255, 255);
$darkBlue = imagecolorallocate($img, 41, 128, 185); // #2980b9

// Draw rounded rectangle background
$radius = $size / 6;
imagefilledrectangle($img, 0, 0, $size, $size, $bg);

// Draw "FX" text
$fontSize = $size / 3;
$font = 5; // Built-in font
$text = "FX";

// Calculate text position (center)
$textWidth = imagefontwidth($font) * strlen($text);
$textHeight = imagefontheight($font);
$x = ($size - $textWidth * ($fontSize / 10)) / 2;
$y = ($size - $fontSize) / 2.5;

// Draw large FX text
imagestring($img, $font, $x, $y, $text, $white);

// Draw a simple chart line at bottom
$lineY = $size * 0.7;
$points = [
    $size * 0.2, $lineY,
    $size * 0.35, $lineY - $size * 0.1,
    $size * 0.5, $lineY - $size * 0.05,
    $size * 0.65, $lineY - $size * 0.15,
    $size * 0.8, $lineY - $size * 0.08
];

for ($i = 0; $i < count($points) - 2; $i += 2) {
    imageline($img, 
        $points[$i], $points[$i+1],
        $points[$i+2], $points[$i+3],
        $white);
    imageline($img, 
        $points[$i]+1, $points[$i+1],
        $points[$i+2]+1, $points[$i+3],
        $white); // Thicker line
}

// Output
imagepng($img);
imagedestroy($img);
