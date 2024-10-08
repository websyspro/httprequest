<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1442ddb2101c78a67b5bf073d639d101
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Websyspro\\HttpRequest\\' => 22,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Websyspro\\HttpRequest\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1442ddb2101c78a67b5bf073d639d101::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1442ddb2101c78a67b5bf073d639d101::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit1442ddb2101c78a67b5bf073d639d101::$classMap;

        }, null, ClassLoader::class);
    }
}
