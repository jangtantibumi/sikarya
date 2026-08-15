<?php

use Composer\Autoload\ClassLoader;

/**
 * Keep the application autoloader pointed at this workspace when vendor is
 * shared through a local junction. Normal production installations exit
 * immediately because Composer already points at the correct application.
 */
return static function (ClassLoader $loader, string $root): void {
    $root = str_replace('\\', '/', $root);
    $appPath = $root.'/app';
    $configuredAppPath = $loader->getPrefixesPsr4()['App\\'][0] ?? null;

    if ($configuredAppPath && realpath($configuredAppPath) === realpath($appPath)) {
        return;
    }

    $_ENV['APP_BASE_PATH'] = $root;
    $_SERVER['APP_BASE_PATH'] = $root;
    putenv('APP_BASE_PATH='.$root);

    $directories = [
        'App\\' => $appPath,
        'Database\\Factories\\' => $root.'/database/factories',
        'Database\\Seeders\\' => $root.'/database/seeders',
    ];
    foreach ($directories as $prefix => $directory) {
        $loader->setPsr4($prefix, [$directory]);
    }

    $classMap = [];
    foreach ($directories as $directory) {
        if (! is_dir($directory)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());
            if (
                ! preg_match('/namespace\s+([^;]+);/', $source, $namespace)
                || ! preg_match(
                    '/(?:abstract\s+|final\s+)?(?:class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)/',
                    $source,
                    $class,
                )
            ) {
                continue;
            }

            $classMap[trim($namespace[1]).'\\'.$class[1]] = $file->getPathname();
        }
    }

    $loader->addClassMap($classMap);
};
