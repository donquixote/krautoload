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
    require_once $basedir . '/FinderPlugin/ShallowPSR0/AllUnderscore.php';

    // Build the class finder and loader, and register it to the spl stack.
    $finder = new Krautoload\ApiClassFinder();
    $loader = new Krautoload\ClassLoader_NoCache($finder);
    $loader->register();

    // Wire up the class finder so it can find Krautoload classes.
    // Krautoload uses PSR-0 with only underscores after the package namespace.
    $plugin = new Krautoload\FinderPlugin_ShallowPSR0_AllUnderscore();
    $finder->registerNamespacePathPlugin('Krautoload/', $basedir . DIRECTORY_SEPARATOR, $plugin);

    // Create the registration hub.
    self::$hub = new Krautoload\RegistrationHub($finder);
    return self::$hub;
  }

  static function registration() {
    if (!isset(self::$hub)) {
      throw new Exception("Krautoload::start() must run before Krautoload::registration()");
    }
    return self::$hub;
  }
}
