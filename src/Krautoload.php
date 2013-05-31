<?php

class Krautoload {

  static protected $hub;

  static function start() {
    if (isset(self::$hub)) {
      throw new Exception("Krautoload::start() can be called only once.");
    }
    $basedir = dirname(__FILE__) . '/Krautoload';
    // Include the bare minimum we need before Krautoload can load its own.
    require_once $basedir . '/ApiClassFinder.php';
    require_once $basedir . '/InjectedAPI.php';
    require_once $basedir . '/ClassLoader/NoCache.php';
    require_once $basedir . '/FinderPlugin/Interface.php';
    require_once $basedir . '/FinderPlugin/ShallowPSR0.php';
    $finder = new Krautoload\ApiClassFinder();
    $plugin = new Krautoload\FinderPlugin_ShallowPSR0();
    $finder->registerNamespacePathPlugin('Krautoload/', $basedir . DIRECTORY_SEPARATOR, $plugin);
    $loader = new Krautoload\ClassLoader_NoCache($finder);
    $loader->register();
    self::$hub = new Krautoload\RegistrationHub($finder);
  }

  static function registration() {
    if (!isset(self::$hub)) {
      throw new Exception("Krautoload::start() must run before Krautoload::registration()");
    }
    return self::$hub;
  }
}
