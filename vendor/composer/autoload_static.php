<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit88f9e5a3dd9f2ee16e82685d1aae71b7
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Dotenv\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Dotenv\\' => 
        array (
            0 => __DIR__ . '/..' . '/vlucas/phpdotenv/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit88f9e5a3dd9f2ee16e82685d1aae71b7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit88f9e5a3dd9f2ee16e82685d1aae71b7::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
