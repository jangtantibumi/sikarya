<?php

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
$root = dirname(__DIR__);

$root = str_replace('\\', '/', $root);
$_ENV['APP_BASE_PATH'] = $root;
$_SERVER['APP_BASE_PATH'] = $root;
putenv('APP_BASE_PATH=' . $root);

$loader->setPsr4('App\\', [$root . '/app']);
$loader->setPsr4('Database\\Factories\\', [$root . '/database/factories']);
$loader->setPsr4('Database\\Seeders\\', [$root . '/database/seeders']);
$loader->setPsr4('Tests\\', [$root . '/tests']);

$classMap = [];
foreach ([$root . '/app', $root . '/database/seeders', $root . '/tests'] as $sourceDirectory) {
    if (!is_dir($sourceDirectory)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDirectory));
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $source = file_get_contents($file->getPathname());
        if (!preg_match('/namespace\s+([^;]+);/', $source, $namespaceMatch)) {
            continue;
        }

        if (!preg_match('/(?:abstract\s+|final\s+)?(?:class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)/', $source, $classMatch)) {
            continue;
        }

        $classMap[trim($namespaceMatch[1]) . '\\' . $classMatch[1]] = $file->getPathname();
    }
}

$loader->addClassMap($classMap);
