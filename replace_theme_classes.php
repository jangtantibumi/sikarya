<?php

$dir = new RecursiveDirectoryIterator(__DIR__ . '/resources/views/inventory');
$ite = new RecursiveIteratorIterator($dir);

$regexReplacements = [
    '/(?<!dark:)bg-erp-dark/' => 'bg-gray-50 dark:bg-erp-dark',
    '/(?<!dark:)bg-erp-card/' => 'bg-white dark:bg-erp-card',
    '/(?<!dark:)text-white/' => 'text-gray-900 dark:text-white',
    '/(?<!dark:)text-gray-100/' => 'text-gray-900 dark:text-gray-100',
    '/(?<!dark:)text-gray-200/' => 'text-gray-800 dark:text-gray-200',
    '/(?<!dark:)text-gray-300/' => 'text-gray-700 dark:text-gray-300',
    '/(?<!dark:)text-gray-400/' => 'text-gray-600 dark:text-gray-400',
    '/(?<!dark:)text-gray-500/' => 'text-gray-500 dark:text-gray-500',
    '/(?<!dark:)border-erp-border/' => 'border-gray-200 dark:border-erp-border',
];

$count = 0;

foreach($ite as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;
        
        foreach($regexReplacements as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        if($content !== $original) {
            file_put_contents($path, $content);
            echo "Updated: $path\n";
            $count++;
        }
    }
}

echo "Total files updated: $count\n";
