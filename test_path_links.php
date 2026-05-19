<?php
$includes_dir = 'C:\XAMPP\htdocs\digital-kebana\kebana-digital\includes';

function testPath($script) {
    global $includes_dir;
    $script_dir_normalized = str_replace('\\', '/', dirname($script));
    $base_dir_normalized = str_replace('\\', '/', dirname($includes_dir));
    
    $rel_path = str_ireplace($base_dir_normalized, '', $script_dir_normalized);
    $up_count = substr_count($rel_path, '/');
    
    $base_path = $up_count > 0 ? str_repeat('../', $up_count) : './';
    $css_base_path = $base_path . 'src/css/';
    
    echo "Script: $script\n";
    echo "  Rel Path: $rel_path\n";
    echo "  Up Count: $up_count\n";
    echo "  Base Path: $base_path\n";
    echo "  CSS Path: $css_base_path\n\n";
}

testPath('c:/XAMPP/htdocs/digital-kebana' . URL_ROOT . '/src/php/index.php'); // depth 2
testPath('c:/XAMPP/htdocs/digital-kebana' . URL_ROOT . '/modules/finance/transactions/list.php'); // depth 3
testPath('c:/XAMPP/htdocs/digital-kebana' . URL_ROOT . '/index.php'); // depth 0
?>
