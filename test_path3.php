<?php
$includes_dir = 'C:\XAMPP\htdocs\digital-kebana\kebana-digital\includes';
$script = 'c:/XAMPP/htdocs/digital-kebana/kebana-digital/src/php/index.php'; // Simulation

$script_dir = str_replace('\\', '/', dirname($script));
$base_dir = str_replace('\\', '/', dirname($includes_dir));

// Use case-insensitive replace for Windows drive letters
$rel_path = str_ireplace($base_dir, '', $script_dir);
$up_count = substr_count($rel_path, '/');

$css_base_path = str_repeat('../', max(1, $up_count)) . 'src/css/';
echo "base_dir: $base_dir\n";
echo "script_dir: $script_dir\n";
echo "rel_path: $rel_path\n";
echo "up_count: $up_count\n";
echo "css_base_path: $css_base_path\n";
?>