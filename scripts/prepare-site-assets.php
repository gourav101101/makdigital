<?php

declare(strict_types=1);

$base = dirname(__DIR__);
$archive = $base.'/storage/app/legacy/mdarena/';
$manifest = json_decode(file_get_contents($archive.'assets.json'), true, flags: JSON_THROW_ON_ERROR);
$destination = $base.'/public/images';
if (! is_dir($destination)) {
    mkdir($destination, 0755, true);
}
$sources = [
    'stoneplus' => 'spsurfaces.jpg',
    'hansline' => 'hansline.jpg',
    'kpl' => 'keshayona.jpg',
];
foreach ($sources as $name => $filename) {
    $asset = array_values(array_filter($manifest, fn (array $asset): bool => str_ends_with($asset['url'], '/'.$filename) && $asset['status'] === 200))[0];
    $image = imagecreatefromjpeg($archive.$asset['file']);
    foreach ([768, 1280] as $width) {
        $height = (int) round(imagesy($image) * $width / imagesx($image));
        $output = imagecreatetruecolor($width, $height);
        imagecopyresampled($output, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
        imagewebp($output, "$destination/$name-$width.webp", 85);
        imagedestroy($output);
    }
    imagedestroy($image);
}
$logo = array_values(array_filter($manifest, fn (array $asset): bool => str_ends_with($asset['url'], '/Mak-Digital-Post-logo-new.png')))[0];
copy($archive.$logo['file'], $destination.'/mak-logo.png');
$preview = imagecreatetruecolor(1200, 630);
$paper = imagecolorallocate($preview, 248, 249, 246);
$ink = imagecolorallocate($preview, 23, 32, 39);
$blue = imagecolorallocate($preview, 23, 92, 220);
imagefill($preview, 0, 0, $paper);
$logoImage = imagecreatefrompng($destination.'/mak-logo.png');
imagecopyresampled($preview, $logoImage, 65, 45, 0, 0, 231, 99, imagesx($logoImage), imagesy($logoImage));
$font = PHP_OS_FAMILY === 'Windows' ? 'C:/Windows/Fonts/arial.ttf' : '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
if (is_file($font)) {
    imagettftext($preview, 66, 0, 65, 295, $ink, $font, 'You visualize.');
    imagettftext($preview, 66, 0, 65, 395, $blue, $font, 'We digitalize.');
    imagettftext($preview, 19, 0, 68, 520, $ink, $font, 'Strategy / Design / Development / Growth');
}
for ($ring = 0; $ring < 5; $ring++) {
    $ringColor = imagecolorallocate($preview, 23 + $ring * 8, 80 + $ring * 22, 185 + $ring * 12);
    imagesetthickness($preview, 22);
    imageellipse($preview, 975, 240 + $ring * 40, 250 - abs($ring - 2) * 35, 85, $ringColor);
}
imagejpeg($preview, $destination.'/social-preview.jpg', 90);
imagedestroy($logoImage);
imagedestroy($preview);
echo "Prepared original logo and six responsive portfolio images.\n";
