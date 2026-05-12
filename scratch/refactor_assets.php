<?php
/**
 * Script to refactor asset paths in modules
 */

$root = dirname(__DIR__);
$modulesDir = $root . '/modules';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modulesDir));
$count = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getRealPath();
        $content = file_get_contents($path);
        
        // Replace $css_path = '../../src/css/...'; with $css_path = $base_path . 'src/css/...';
        $newContent = preg_replace('/\$css_path\s*=\s*[\'"](?:\.\.\/)+src\/css\/(.*?)[\'"]/', "\$css_path = \$base_path . 'src/css/$1'", $content);
        
        if ($newContent !== $content) {
            file_put_contents($path, $newContent);
            echo "Updated assets: $path\n";
            $count++;
        }
    }
}

echo "Total asset paths updated: $count\n";
