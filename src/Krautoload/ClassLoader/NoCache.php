<?php

namespace Krautoload;

class ClassLoader_NoCache {

  protected $finder;

  /**
   * @param ApiClassFinder $finder
   *   The object that does the actual class finding.
   */
  function __construct($finder) {
    $this->finder = $finder;
  }

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

  /**
   * Callback for class loading. This will include ("require") the file found.
   *
   * @param string $class
   *   The class to load.
   */
  function loadClass($class) {
    $api = new InjectedAPI($class);
    // $api has a ->suggestFile($file) method, which returns TRUE if the
    // suggested file exists.
    // The $finder->findFile() method is supposed to suggest a number of files
    // to the $api, until one is successful, and then return TRUE. Or return
    // FALSE, if nothing was found.
    if ($this->finder->apiFindFile($api, $class)) {
      return TRUE;
    }
    return FALSE;
  }
}
