<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit34bca33b03966db41fbaf3e9cc054d74
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Shadows\\CarStorage\\Core\\' => 24,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Shadows\\CarStorage\\Core\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit34bca33b03966db41fbaf3e9cc054d74::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit34bca33b03966db41fbaf3e9cc054d74::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
