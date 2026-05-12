<?php
/**
 * Script to refactor includes in modules to use APP_ROOT
 */

$root = dirname(__DIR__);
$modulesDir = $root . '/modules';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modulesDir));
$count = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getRealPath();
        $content = file_get_contents($path);
        
        // Replace ../../includes/ or ../../../includes/ with APP_ROOT . '/includes/
        $newContent = preg_replace('/require_once\s+[\'"](?:\.\.\/)+includes\/(.*?)[\'"]/', "require_once APP_ROOT . '/includes/$1'", $content);
        
        if ($newContent !== $content) {
            file_put_contents($path, $newContent);
            echo "Updated: $path\n";
            $count++;
        }
    }
}

echo "Total files updated: $count\n";
