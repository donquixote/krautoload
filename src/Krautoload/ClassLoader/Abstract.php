<?php

namespace Krautoload;

abstract class ClassLoader_Abstract implements ClassLoader_Interface {

  /**
   * Registers this instance as an autoloader.
   *
   * @param boolean $prepend
   *   If TRUE, the loader will be prepended. Otherwise, it will be appended.
   */
  function register($prepend = FALSE) {
    // http://www.php.net/manual/de/function.spl-autoload-register.php#107362
    // "when specifying the third parameter (prepend), the function will fail badly in PHP 5.2"
    if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
      spl_autoload_register(array($this, 'loadClass'), TRUE, $prepend);
    }
    elseif ($prepend) {
      $loaders = spl_autoload_functions();
      spl_autoload_register(array($this, 'loadClass'));
      foreach ($loaders as $loader) {
        spl_autoload_unregister($loader);
        spl_autoload_register($loader);
      }
    }
    else {
      spl_autoload_register(array($this, 'loadClass'));
    }
  }

  /**
   * Unregister from the spl autoload stack.
   */
  function unregister() {
    spl_autoload_unregister(array($this, 'loadClass'));
  }

  function findFile($class) {
    $api = new InjectedAPI_ClassFinder_FirstExistingFile($class);
    $this->apiFindFile($api, $class);
    return $api->getFile();
  }

  /**
   * Load a class, and return the file that was successful.
   *
   * @param string $class
   *   The class to load.
   *
   * @return string
   *   The file that defined the class.
   */
  function loadClassGetFile($class) {
    $api = new InjectedAPI_ClassFinder_LoadClassGetFile($class);
    $this->apiFindFile($api, $class);
    return $api->getFile();
  }
}
