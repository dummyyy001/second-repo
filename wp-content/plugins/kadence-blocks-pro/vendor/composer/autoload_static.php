<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita22f1dc71cbe85b57e42f4fce833d2aa
{
    public static $prefixLengthsPsr4 = array (
        'K' => 
        array (
            'KadenceWP\\KadenceBlocksPro\\' => 27,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'KadenceWP\\KadenceBlocksPro\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes/vendor',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita22f1dc71cbe85b57e42f4fce833d2aa::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita22f1dc71cbe85b57e42f4fce833d2aa::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInita22f1dc71cbe85b57e42f4fce833d2aa::$classMap;

        }, null, ClassLoader::class);
    }
}
