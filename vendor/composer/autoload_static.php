<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit87f9fa370e34c4c027dd4365a216ce6b
{
    public static $prefixLengthsPsr4 = array (
        'O' => 
        array (
            'OfficeGest\\' => 11,
        ),
        'C' => 
        array (
            'Curl\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'OfficeGest\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Curl\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-curl-class/php-curl-class/src/Curl',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit87f9fa370e34c4c027dd4365a216ce6b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit87f9fa370e34c4c027dd4365a216ce6b::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
