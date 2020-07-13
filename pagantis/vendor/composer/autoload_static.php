<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitaf1beb4713ca5ead3e81f1922e3702c1
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Pagantis\\OrdersApiClient\\' => 25,
            'Pagantis\\ModuleUtils\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Pagantis\\OrdersApiClient\\' => 
        array (
            0 => __DIR__ . '/..' . '/pagantis/orders-api-client/src',
        ),
        'Pagantis\\ModuleUtils\\' => 
        array (
            0 => __DIR__ . '/..' . '/pagantis/module-utils/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'H' => 
        array (
            'Httpful' => 
            array (
                0 => __DIR__ . '/..' . '/nategood/httpful/src',
            ),
        ),
    );

    public static $classMap = array (
        'Nayjest\\StrCaseConverter\\Str' => __DIR__ . '/..' . '/nayjest/str-case-converter/src/Str.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitaf1beb4713ca5ead3e81f1922e3702c1::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitaf1beb4713ca5ead3e81f1922e3702c1::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitaf1beb4713ca5ead3e81f1922e3702c1::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitaf1beb4713ca5ead3e81f1922e3702c1::$classMap;

        }, null, ClassLoader::class);
    }
}
