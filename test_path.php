<?php
$includes_dir = __DIR__;
$script = 'c:/XAMPP/htdocs/digital-kebana/kebana-digital/src/php/index.php'; // Simulation
$script_dir = dirname($script);
$up_count = substr_count(str_replace(dirname($includes_dir), '', $script_dir), DIRECTORY_SEPARATOR);
$css_base_path = str_repeat('../', max(1, $up_count)) . 'src/css/';
echo "includes_dir: $includes_dir\n";
echo "script_dir: $script_dir\n";
echo "up_count: $up_count\n";
echo "css_base_path: $css_base_path\n";
?>
