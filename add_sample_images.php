<?php
// Sample image URLs (you can replace these with your actual image URLs)
$sample_images = [
    'oil-change.jpg' => 'https://example.com/oil-change.jpg',
    'tire-rotation.jpg' => 'https://example.com/tire-rotation.jpg',
    'brake-service.jpg' => 'https://example.com/brake-service.jpg',
    'engine-tuneup.jpg' => 'https://example.com/engine-tuneup.jpg',
    'battery-check.jpg' => 'https://example.com/battery-check.jpg'
];

$image_dir = __DIR__ . '/assets/images/services/';

// Create placeholder images with service names
foreach ($sample_images as $filename => $url) {
    $image = imagecreatetruecolor(800, 600);
    $bg_color = imagecolorallocate($image, 240, 240, 240);
    $text_color = imagecolorallocate($image, 50, 50, 50);
    
    // Fill background
    imagefilledrectangle($image, 0, 0, 800, 600, $bg_color);
    
    // Add service name as text
    $service_name = ucwords(str_replace(['-', '.jpg'], [' ', ''], $filename));
    $font_size = 5;
    $text_box = imagettfbbox($font_size, 0, 'arial.ttf', $service_name);
    $text_width = abs($text_box[4] - $text_box[0]);
    $text_height = abs($text_box[5] - $text_box[1]);
    
    // Center the text
    $x = (800 - $text_width) / 2;
    $y = (600 - $text_height) / 2;
    
    imagestring($image, $font_size, $x, $y, $service_name, $text_color);
    
    // Save image
    imagejpeg($image, $image_dir . $filename);
    imagedestroy($image);
    
    echo "Created placeholder image for: " . $filename . "<br>";
}

// Create hero image
$hero_image = imagecreatetruecolor(1920, 1080);
$bg_color = imagecolorallocate($hero_image, 30, 30, 30);
$text_color = imagecolorallocate($hero_image, 255, 255, 255);

imagefilledrectangle($hero_image, 0, 0, 1920, 1080, $bg_color);
imagestring($hero_image, 5, 860, 520, "Professional Auto Care Services", $text_color);

imagejpeg($hero_image, __DIR__ . '/assets/images/garage-hero.jpg');
imagedestroy($hero_image);

echo "Created hero image: garage-hero.jpg<br>";
echo "All sample images have been created successfully!";
?> 