<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit0d2ab1280316e8e18ee92f08de988c8d
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit0d2ab1280316e8e18ee92f08de988c8d', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit0d2ab1280316e8e18ee92f08de988c8d', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit0d2ab1280316e8e18ee92f08de988c8d::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
